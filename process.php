<?php
use ProjectSend\Classes\Session as Session;
use ProjectSend\Classes\Download;
use ProjectSend\Classes\ActionsLog;

/** Process an action */
$allowed_levels = array(9, 8, 7, 0);
require_once 'bootstrap.php';
log_in_required($allowed_levels);

global $auth;
global $logger;

extend_session();

if (!isset($_GET['do'])) {
    exit_with_error_code(403);
}

switch ($_GET['do']) {
    case 'social_login':
        if (Session::has('SOCIAL_LOGIN_NETWORK')) {
            Session::remove('SOCIAL_LOGIN_NETWORK');
        }
        Session::add('SOCIAL_LOGIN_NETWORK', $_GET['provider']);

        $login = $auth->socialLogin($_GET['provider']);
        break;
    case 'login_ldap':
        // Validate required fields
        if (empty($_POST['ldap_email']) || empty($_POST['ldap_password'])) {
            echo json_encode([
                'status' => 'error',
                'message' => __("Email and password are required.", 'cftp_admin')
            ]);
            exit;
        }

        // Check if LDAP is enabled
        if (get_option('ldap_signin_enabled') != 'true') {
            echo json_encode([
                'status' => 'error',
                'message' => __("LDAP authentication is not enabled.", 'cftp_admin')
            ]);
            exit;
        }

        // Set language if provided
        if (!empty($_POST['language'])) {
            $auth->setLanguage($_POST['language']);
        }

        // Perform LDAP authentication
        $login = $auth->loginLdap($_POST['ldap_email'], $_POST['ldap_password'], $_POST['language'] ?? null);
        echo $login;
        break;
    case 'test_ldap_connection':
        // Require admin level for testing LDAP connection
        redirect_if_role_not_allowed([9]);
        
        $test_result = $auth->testLdapConnection();
        echo json_encode($test_result);
        break;
    case 'logout':
        force_logout();
        break;
    case 'change_language':
        $auth->setLanguage(html_output($_GET['language']));
        $location = 'index.php';
        if (!empty($_GET['return_to']) && strpos($_GET['return_to'], BASE_URI) === 0) {
            $location = str_replace(BASE_URI, '', $_GET['return_to']);
        }
        ps_redirect(BASE_URI . $location);
        break;
    case 'get_preview':
        $return = [];
        if (!empty($_GET['file_id'])) {
            if (!user_can_download_file(CURRENT_USER_ID, $_GET['file_id'])) {
                exit_with_error_code(403);
            }
            $file = new \ProjectSend\Classes\Files($_GET['file_id']);
            if ($file->existsOnDisk() && $file->embeddable) {
                $return = json_decode($file->getEmbedData());
            }
        }

        echo json_encode($return);
        exit;
        break;
    case 'download':
        $download = new Download;
        $download->download($_GET['id']);
        break;
    case 'dismiss_upgraded_notice':
        redirect_if_not_logged_in();
        redirect_if_role_not_allowed([9,8,7]);
        save_option('show_upgrade_success_message', 'false');
        ps_redirect(BASE_URI.'dashboard.php');
    case 'return_files_ids':
        redirect_if_not_logged_in();
        redirect_if_role_not_allowed($allowed_levels);
        $download = new Download;
        $download->returnFilesIds($_GET['files']);
        break;
    case 'download_zip':
        redirect_if_not_logged_in();
        redirect_if_role_not_allowed($allowed_levels);
        $download = new Download;
        $download->downloadZip($_GET['files']);
    break;

    case 'get_file_info':
        redirect_if_not_logged_in();
        redirect_if_role_not_allowed($allowed_levels);
        
        header('Content-Type: application/json');
        
        if (!isset($_GET['file_id']) || empty($_GET['file_id'])) {
            echo json_encode(['success' => false, 'error' => 'File ID is required']);
            break;
        }
        
        $file_id = (int)$_GET['file_id'];
        
        // Check if user can download this file
        if (!user_can_download_file(CURRENT_USER_ID, $file_id)) {
            echo json_encode(['success' => false, 'error' => 'Access denied']);
            break;
        }
        
        // Get file information
        $file = new \ProjectSend\Classes\Files($file_id);
        
        if (!$file->id) {
            echo json_encode(['success' => false, 'error' => 'File not found']);
            break;
        }
        
        // Get file data using the getPublicData method
        $file_data = $file->getPublicData();
        
        echo json_encode(['success' => true, 'file' => $file_data]);
    break;
}

exit;
