<?php
/**
 * Database upgrade: Add upload_storage_select permission and default_upload_storage option
 * This upgrade adds the new permission for storage selection during uploads
 */

function upgrade_2025092603()
{
    global $dbh;

    try {
        // Add the new permission to all existing roles (enabled by default)
        $permission_name = 'upload_storage_select';

        // Get all roles
        $roles_query = "SELECT id FROM " . TABLE_ROLES;
        $roles_stmt = $dbh->prepare($roles_query);
        $roles_stmt->execute();
        $roles = $roles_stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($roles as $role_id) {
            // Check if permission already exists for this role
            $check_query = "SELECT COUNT(*) FROM " . TABLE_ROLE_PERMISSIONS . " WHERE role_id = :role_id AND permission = :permission";
            $check_stmt = $dbh->prepare($check_query);
            $check_stmt->bindParam(':role_id', $role_id, PDO::PARAM_INT);
            $check_stmt->bindParam(':permission', $permission_name, PDO::PARAM_STR);
            $check_stmt->execute();
            $exists = $check_stmt->fetchColumn();

            if (!$exists) {
                // Add permission for this role (enabled by default)
                $insert_query = "INSERT INTO " . TABLE_ROLE_PERMISSIONS . " (role_id, permission, granted) VALUES (:role_id, :permission, 1)";
                $insert_stmt = $dbh->prepare($insert_query);
                $insert_stmt->bindParam(':role_id', $role_id, PDO::PARAM_INT);
                $insert_stmt->bindParam(':permission', $permission_name, PDO::PARAM_STR);
                $insert_stmt->execute();
            }
        }

        // Add default upload storage option if it doesn't exist
        add_option_if_not_exists('default_upload_storage', 'local');

        error_log('Database upgrade 2025092603: Successfully added upload_storage_select permission and default_upload_storage option');

    } catch (Exception $e) {
        error_log('Database upgrade 2025092603 failed: ' . $e->getMessage());
        throw $e;
    }
}