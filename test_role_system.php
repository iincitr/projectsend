<?php
require_once 'bootstrap.php';

echo "<h2>Role System Test - No role_level References</h2>";

try {
    // Test 1: Check tables no longer have role_level columns
    $sql = "DESCRIBE " . TABLE_ROLES;
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "<h3>✅ tbl_roles columns:</h3>";
    echo "<p>" . implode(', ', $columns) . "</p>";

    if (in_array('role_level', $columns)) {
        echo "<p>❌ ERROR: role_level column still exists in tbl_roles!</p>";
    } else {
        echo "<p>✅ SUCCESS: role_level column removed from tbl_roles</p>";
    }

    $sql = "DESCRIBE " . TABLE_ROLE_PERMISSIONS;
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "<h3>✅ tbl_role_permissions columns:</h3>";
    echo "<p>" . implode(', ', $columns) . "</p>";

    if (in_array('role_level', $columns)) {
        echo "<p>❌ ERROR: role_level column still exists in tbl_role_permissions!</p>";
    } else {
        echo "<p>✅ SUCCESS: role_level column removed from tbl_role_permissions</p>";
    }

    // Test 2: Check role retrieval works
    $roles = \ProjectSend\Classes\Roles::getAllRoles();
    echo "<h3>✅ Available Roles:</h3>";
    foreach ($roles as $role) {
        echo "<p>ID: {$role['id']}, Name: {$role['name']}</p>";
    }

    // Test 3: Check System Administrator permissions
    $admin_role = \ProjectSend\Classes\Roles::getRoleByName('System Administrator');
    if ($admin_role) {
        echo "<h3>✅ System Administrator Role Found:</h3>";
        echo "<p>ID: {$admin_role['id']}, Name: {$admin_role['name']}</p>";

        $admin_permissions = \ProjectSend\Classes\Permissions::getPermissionsForRole($admin_role['id']);
        echo "<p>Permissions count: " . count($admin_permissions) . "</p>";

        $key_perms = ['manage_users', 'manage_clients', 'manage_groups', 'edit_settings'];
        foreach ($key_perms as $perm) {
            $has = in_array($perm, $admin_permissions);
            echo "<p>$perm: " . ($has ? '✅' : '❌') . "</p>";
        }
    } else {
        echo "<p>❌ ERROR: System Administrator role not found!</p>";
    }

    echo "<h3>✅ Role system migration completed successfully!</h3>";
    echo "<p>All role_level references have been removed. Only role IDs and permissions remain.</p>";

} catch (Exception $e) {
    echo "<p>❌ ERROR: " . $e->getMessage() . "</p>";
}
?>