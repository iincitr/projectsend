<?php
/**
 * Database upgrade: Add view_downloads_details permission
 * This permission controls access to detailed download information
 */

function upgrade_2025100102()
{
    global $dbh;

    try {
        // Permission definition
        $permission_name = 'view_downloads_details';

        // Check if permission definition exists
        $check_perm_query = "SELECT COUNT(*) FROM " . TABLE_PERMISSIONS . " WHERE permission_key = :permission";
        $check_perm_stmt = $dbh->prepare($check_perm_query);
        $check_perm_stmt->bindParam(':permission', $permission_name, PDO::PARAM_STR);
        $check_perm_stmt->execute();
        $perm_exists = $check_perm_stmt->fetchColumn();

        if (!$perm_exists) {
            // Create the permission definition
            $insert_perm_query = "INSERT INTO " . TABLE_PERMISSIONS . "
                                  (permission_key, name, description, category, is_system_permission, active)
                                  VALUES (:permission_key, :name, :description, :category, 0, 1)";
            $insert_perm_stmt = $dbh->prepare($insert_perm_query);
            $insert_perm_stmt->execute([
                'permission_key' => $permission_name,
                'name' => 'View Download Details',
                'description' => 'Access detailed download information and statistics',
                'category' => 'files'
            ]);
        }

        // Get client role ID
        $client_role_id = \ProjectSend\Classes\Roles::getClientRoleId();

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
                // Disable for clients, enable for all other roles
                $granted = ($role_id == $client_role_id) ? 0 : 1;

                $insert_query = "INSERT INTO " . TABLE_ROLE_PERMISSIONS . " (role_id, permission, granted) VALUES (:role_id, :permission, :granted)";
                $insert_stmt = $dbh->prepare($insert_query);
                $insert_stmt->bindParam(':role_id', $role_id, PDO::PARAM_INT);
                $insert_stmt->bindParam(':permission', $permission_name, PDO::PARAM_STR);
                $insert_stmt->bindParam(':granted', $granted, PDO::PARAM_INT);
                $insert_stmt->execute();
            }
        }

        error_log('Database upgrade 2025100102: Successfully added view_downloads_details permission');

    } catch (Exception $e) {
        error_log('Database upgrade 2025100102 failed: ' . $e->getMessage());
        throw $e;
    }
}
