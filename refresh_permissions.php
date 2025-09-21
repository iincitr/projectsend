<?php
require_once 'bootstrap.php';

echo "<h2>Refreshing Core Permissions</h2>";

// Ensure all core permissions exist
\ProjectSend\Classes\Permissions::ensureCorePermissionsExist();
echo "<p>✓ Core permissions ensured in database</p>";

// Get System Administrator role
$admin_role = \ProjectSend\Classes\Roles::getRoleByName('System Administrator');
if ($admin_role) {
    echo "<p>✓ Found System Administrator role (ID: {$admin_role['id']})</p>";

    // Force refresh permissions for System Admin
    $role = new \ProjectSend\Classes\Roles($admin_role['id']);
    $role->setPermissions(array_keys(\ProjectSend\Classes\Permissions::getAllPermissionsFromDatabase()));
    echo "<p>✓ Assigned ALL permissions to System Administrator</p>";

    // Test the new permissions
    echo "<h3>Testing key permissions:</h3>";
    $user = new \ProjectSend\Classes\Users(CURRENT_USER_ID);
    $permissions = new \ProjectSend\Classes\Permissions(CURRENT_USER_ID);

    echo "<p>manage_users: " . (current_user_can('manage_users') ? 'YES' : 'NO') . "</p>";
    echo "<p>manage_clients: " . (current_user_can('manage_clients') ? 'YES' : 'NO') . "</p>";
    echo "<p>manage_groups: " . (current_user_can('manage_groups') ? 'YES' : 'NO') . "</p>";
    echo "<p>edit_settings: " . (current_user_can('edit_settings') ? 'YES' : 'NO') . "</p>";
} else {
    echo "<p>❌ System Administrator role not found!</p>";
}

echo "<p><a href='dashboard.php'>Return to Dashboard</a></p>";
?>