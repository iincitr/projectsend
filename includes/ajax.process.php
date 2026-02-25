<?php
// Process ajax calls
require_once '../bootstrap.php';

global $auth;
global $logger;

extend_session();

if (!user_is_logged_in()) {
    die_with_error_code(403);
}

if (!isset($_GET['do'])) {
    exit_with_error_code(403);
}

header('Content-Type: application/json');

switch ($_GET['do']) {
    case 'folder_create':
        $folder = new \ProjectSend\Classes\Folder();
        $folder->set([
            'name' => $_POST['folder_name'],
            'parent' => (!empty($_POST['folder_parent'])) ? (int)$_POST['folder_parent'] : null,
        ]);

        if ($folder->create()) {
            echo json_encode([
                'status' => 'success',
                'data' => $folder->getData(),
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
            ]);
        }

        exit;
        break;

    case 'folder_move':
        $folder = new \ProjectSend\Classes\Folder($_POST['folder_id']);
        $move = $folder->setNewParent(CURRENT_USER_ID, $_POST['new_parent_id']); 

        if ($move) {
            echo json_encode([
                'status' => 'success',
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
            ]);
            die_with_error_code(500);
        }

        exit;
    break;

    case 'file_move':
        $file = new \ProjectSend\Classes\Files($_POST['file_id']);
        $move = $file->moveToFolder($_POST['new_parent_id']); 

        if ($move) {
            echo json_encode([
                'status' => 'success',
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
            ]);
            die_with_error_code(500);
        }

        exit;
    break;

    case 'folder_rename':
        $folder = new \ProjectSend\Classes\Folder($_POST['folder_id']);
        $rename = $folder->rename($_POST['name']); 

        if ($rename) {
            echo json_encode([
                'status' => 'success',
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
            ]);
            die_with_error_code(500);
        }

        exit;
    break;

    case 'folder_delete':
        $folder = new \ProjectSend\Classes\Folder($_POST['folder_id']);
        $delete = $folder->delete(); 

        if (!$delete) {
            echo json_encode([
                'status' => 'error',
            ]);
            die_with_error_code(500);
        }

        if (in_array($_POST['folder_id'], $delete['folders'])) {
            echo json_encode([
                'status' => 'success',
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
            ]);
            die_with_error_code(500);
        }

        exit;
    break;

    case 'thumbnails_regenerate_get_files':
        if (!current_user_can('edit_settings')) {
            echo json_encode(['status' => 'error', 'message' => 'Permission denied']);
            exit;
        }

        // Validate required parameters
        if (!isset($_POST['formats']) || !is_array($_POST['formats'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid formats parameter'
            ]);
            exit;
        }

        // Get parameters with defaults
        $formats = $_POST['formats'];
        $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : null;
        $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : null;
        $batch_size = isset($_POST['batch_size']) ? (int)$_POST['batch_size'] : 10;
        $offset = isset($_POST['offset']) ? (int)$_POST['offset'] : 0;

        // Get files for processing
        $files_data = get_files_for_thumbnail_regeneration($formats, $start_date, $end_date, $batch_size, $offset);

        echo json_encode([
            'status' => 'success',
            'data' => $files_data
        ]);

        exit;
    break;

    case 'thumbnails_regenerate_process':
        if (!current_user_can('edit_settings')) {
            echo json_encode(['status' => 'error', 'message' => 'Permission denied']);
            exit;
        }

        // Validate required parameters
        if (!isset($_POST['file_id']) || !isset($_POST['width']) || !isset($_POST['height'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Missing required parameters'
            ]);
            exit;
        }

        $file_id = (int)$_POST['file_id'];
        $width = (int)$_POST['width'];
        $height = (int)$_POST['height'];

        // Validate dimensions
        if ($width < 50 || $width > 1000 || $height < 50 || $height > 1000) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid thumbnail dimensions'
            ]);
            exit;
        }

        // Process thumbnail regeneration with error handling
        try {
            $result = regenerate_single_thumbnail($file_id, $width, $height);

            if ($result['success']) {
                echo json_encode([
                    'status' => 'success',
                    'data' => [
                        'file_id' => $result['file_id'],
                        'filename' => $result['filename']
                    ]
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => $result['error'],
                    'data' => [
                        'file_id' => isset($result['file_id']) ? $result['file_id'] : null,
                        'filename' => isset($result['filename']) ? $result['filename'] : null
                    ]
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Fatal error: ' . $e->getMessage(),
                'data' => [
                    'file_id' => $file_id,
                    'filename' => null
                ]
            ]);
        } catch (Error $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'PHP Error: ' . $e->getMessage(),
                'data' => [
                    'file_id' => $file_id,
                    'filename' => null
                ]
            ]);
        }

        exit;
    break;

    default:
        die_with_error_code(500);
    break;
}
