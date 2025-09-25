<?php
use ProjectSend\Classes\Session as Session;
use ProjectSend\Classes\Download;
use ProjectSend\Classes\ActionsLog;

/** Process an action */
require_once 'bootstrap.php';

// Allow get_public_file_info without login requirement
if (!isset($_GET['do']) || $_GET['do'] !== 'get_public_file_info') {
    redirect_if_not_logged_in();
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
        if (!current_user_can('edit_settings')) {
            exit_with_error_code(403);
        }
        save_option('show_upgrade_success_message', 'false');

        // Redirect back to the original page if provided, otherwise dashboard
        $return_to = isset($_GET['return_to']) ? $_GET['return_to'] : BASE_URI.'dashboard.php';

        // Validate the return URL to prevent open redirects
        $parsed_url = parse_url($return_to);
        if ($parsed_url && (empty($parsed_url['host']) || $parsed_url['host'] === $_SERVER['HTTP_HOST'])) {
            ps_redirect($return_to);
        } else {
            ps_redirect(BASE_URI.'dashboard.php');
        }
        break;
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
        // Skip role-based check, rely on permission-based check below
        // This allows custom roles with appropriate permissions to access this endpoint

        header('Content-Type: application/json');

        if (!isset($_GET['file_id']) || empty($_GET['file_id'])) {
            echo json_encode(['success' => false, 'error' => 'File ID is required']);
            break;
        }

        $file_id = (int)$_GET['file_id'];

        // Check if user can access this file's information
        // For file info, we use edit permissions only (not download permissions)
        // This ensures users can only get detailed info about files they can edit
        $can_access = user_can_edit_file(CURRENT_USER_ID, $file_id);

        if (!$can_access) {
            echo json_encode(['success' => false, 'error' => 'Access denied - you do not have permission to view this file information']);
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

    case 'check_update_requirements':
        // Check permissions
        if (!current_user_can('manage_updates')) {
            echo json_encode(['status' => 'error', 'message' => __('Permission denied', 'cftp_admin')]);
            exit;
        }

        $updater = new \ProjectSend\Classes\AutoUpdate();
        echo json_encode($updater->checkSystemRequirements());
        break;

    case 'perform_system_update':
        // Check permissions
        if (!current_user_can('manage_updates')) {
            echo json_encode(['status' => 'error', 'message' => __('Permission denied', 'cftp_admin')]);
            exit;
        }

        $step = $_POST['step'] ?? 'download';
        $updater = new \ProjectSend\Classes\AutoUpdate();

        switch($step) {
            case 'download':
                $url = $_POST['url'] ?? '';
                if (empty($url)) {
                    echo json_encode(['status' => 'error', 'message' => __('Download URL is required', 'cftp_admin')]);
                    exit;
                }
                $result = $updater->downloadUpdate($url);
                break;

            case 'backup':
                $result = $updater->createBackup();
                break;

            case 'extract':
                $result = $updater->extractUpdate();
                break;

            case 'finalize':
                $result = $updater->finalize();
                break;

            case 'rollback':
                $result = $updater->rollback();
                break;

            default:
                $result = ['status' => 'error', 'message' => __('Invalid update step', 'cftp_admin')];
        }

        echo json_encode($result);
        break;

    case 'get_roles_for_reassignment':
        // Get all active roles except the one being deleted
        if (!current_user_can('edit_settings')) {
            echo json_encode(['error' => 'Access denied']);
            exit;
        }

        $exclude_role = (int)$_GET['exclude_role'];
        $roles = get_all_roles();
        $available_roles = [];

        foreach ($roles as $role) {
            if ($role['id'] != $exclude_role && $role['active']) {
                $available_roles[] = [
                    'id' => $role['id'],
                    'name' => $role['name']
                ];
            }
        }

        echo json_encode(['roles' => $available_roles]);
        break;

    case 'get_role_users':
        // Get users assigned to a specific role
        if (!current_user_can('edit_settings')) {
            echo json_encode(['error' => 'Access denied']);
            exit;
        }

        $role_id = (int)$_GET['role_id'];
        $role = new \ProjectSend\Classes\Roles($role_id);
        $users = $role->getUsers();

        echo json_encode(['users' => $users]);
        break;

    case 'delete_role':
        // Only users with edit_settings permission can delete roles
        if (!current_user_can('edit_settings')) {
            $flash->error(__('You do not have permission to delete roles.', 'cftp_admin'));
            ps_redirect('roles.php');
        }

        if (empty($_POST['role_id'])) {
            $flash->error(__('No role specified.', 'cftp_admin'));
            ps_redirect('roles.php');
        }

        $role_id = (int)$_POST['role_id'];
        $role = new \ProjectSend\Classes\Roles($role_id);

        if (!$role->exists()) {
            $flash->error(__('Role not found.', 'cftp_admin'));
            ps_redirect('roles.php');
        }

        if ($role->is_system_role) {
            $flash->error(__('System roles cannot be deleted.', 'cftp_admin'));
            ps_redirect('roles.php');
        }

        // Handle user reassignment if needed
        if ($role->getUserCount() > 0) {
            if (empty($_POST['reassign_to_role'])) {
                $flash->error(__('Cannot delete role with assigned users. Please reassign users first.', 'cftp_admin'));
                ps_redirect('roles.php');
            }

            $new_role_id = (int)$_POST['reassign_to_role'];
            $new_role = new \ProjectSend\Classes\Roles($new_role_id);

            if (!$new_role->exists()) {
                $flash->error(__('Target role for reassignment not found.', 'cftp_admin'));
                ps_redirect('roles.php');
            }

            // Reassign all users to the new role
            $reassignment_result = $role->reassignUsersToRole($new_role_id);
            if ($reassignment_result['status'] !== 'success') {
                $flash->error(__('Failed to reassign users: ', 'cftp_admin') . $reassignment_result['message']);
                ps_redirect('roles.php');
            }
        }

        $result = $role->delete();
        if ($result['status'] === 'success') {
            $flash->success($result['message']);
        } else {
            $flash->error($result['message']);
        }

        ps_redirect('roles.php');
    break;
}

exit;
