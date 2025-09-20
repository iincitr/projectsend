<?php
use ProjectSend\Classes\Session as Session;
use ProjectSend\Classes\Download;
use ProjectSend\Classes\ActionsLog;

/** Process an action */
$allowed_levels = array(9, 8, 7, 0);
require_once 'bootstrap.php';

// Allow get_public_file_info without login requirement
if (!isset($_GET['do']) || $_GET['do'] !== 'get_public_file_info') {
    log_in_required($allowed_levels);
}

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

        // Add categories with names
        $categories_names = [];
        if (!empty($file->categories)) {
            $categories_query = "SELECT id, name FROM " . TABLE_CATEGORIES . " WHERE id IN (" . implode(',', $file->categories) . ")";
            $categories_stmt = $dbh->query($categories_query);
            while ($cat = $categories_stmt->fetch(PDO::FETCH_ASSOC)) {
                $categories_names[] = [
                    'id' => $cat['id'],
                    'name' => $cat['name']
                ];
            }
        }
        $file_data['categories'] = $categories_names;

        // Add privacy information
        $file_data['public'] = $file->public;
        $file_data['public_token'] = $file->public_token;
        $file_data['public_url'] = $file->public_url;

        // Add expiry information
        $file_data['expires'] = $file->expires;
        if ($file->expires && $file->expiry_date) {
            $file_data['expiry_date_formatted'] = date(get_option('timeformat'), strtotime($file->expiry_date));
            $file_data['days_until_expiry'] = round((strtotime($file->expiry_date) - time()) / 86400);
        }

        // Add image metadata for images
        if ($file->isImage()) {
            // Get dimensions
            $dimensions = $file->getDimensions();
            if ($dimensions) {
                $file_data['image_metadata'] = [
                    'width' => $dimensions['width'],
                    'height' => $dimensions['height'],
                ];
            }

            // Get EXIF data (if available)
            if (function_exists('exif_read_data')) {
                try {
                    $exif = @exif_read_data($file->full_path, 'IFD0', true);
                    if ($exif && isset($exif['IFD0'])) {
                        $exif_data = [];
                        $exif_fields = ['Make', 'Model', 'DateTime', 'ExposureTime', 'FNumber', 'ISOSpeedRatings'];
                        foreach ($exif_fields as $field) {
                            if (!empty($exif['IFD0'][$field])) {
                                $exif_data[$field] = $exif['IFD0'][$field];
                            }
                        }
                        if (!empty($exif_data)) {
                            $file_data['image_metadata']['exif'] = $exif_data;
                        }
                    }
                } catch (Exception $e) {
                    // Silently fail if EXIF reading fails
                }
            }
        }

        // Add uploader information
        $file_data['uploaded_by'] = $file->uploaded_by;

        // Add assignment information
        $file_data['assignments'] = [
            'clients' => $file->assignments_clients,
            'groups' => $file->assignments_groups
        ];

        echo json_encode(['success' => true, 'file' => $file_data]);
    break;

    case 'get_public_file_info':
        header('Content-Type: application/json');
        
        if (!isset($_GET['file_id']) || empty($_GET['file_id'])) {
            echo json_encode(['success' => false, 'error' => 'File ID is required']);
            break;
        }
        
        $file_id = (int)$_GET['file_id'];
        
        try {
            // Get file information
            $file = new \ProjectSend\Classes\Files($file_id);
            
            if (!$file->id) {
                echo json_encode(['success' => false, 'error' => 'File not found', 'debug' => 'File object has no ID']);
                break;
            }
            
            // Check if file is public
            if (!$file->isPublic()) {
                echo json_encode(['success' => false, 'error' => 'File is not public', 'debug' => 'File exists but isPublic() returned false']);
                break;
            }
            
            // Get file data using the getPublicData method
            $file_data = $file->getPublicData();
            
            if (empty($file_data)) {
                echo json_encode(['success' => false, 'error' => 'Failed to get file data', 'debug' => 'getPublicData() returned empty']);
                break;
            }
            
            echo json_encode(['success' => true, 'file' => $file_data]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Exception occurred: ' . $e->getMessage(), 'debug' => 'Exception in get_public_file_info']);
        }
    break;
}

exit;
