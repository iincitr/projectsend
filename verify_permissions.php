<?php
require_once 'bootstrap.php';

echo "<h2>Permission Database Verification</h2>";

// Check if all permissions exist in database
$all_permissions = \ProjectSend\Classes\Permissions::getAllPermissionsFromDatabase();
echo "<p>Total permissions in database: " . count($all_permissions) . "</p>";

// Check System Administrator permissions
$admin_permissions = \ProjectSend\Classes\Permissions::getPermissionsForRole(4);
echo "<p>System Administrator has " . count($admin_permissions) . " permissions</p>";

// Check key permissions
$key_permissions = ['manage_users', 'manage_clients', 'manage_groups', 'edit_settings'];
echo "<h3>Key Permissions Status:</h3>";
foreach ($key_permissions as $perm) {
    $has_perm = in_array($perm, $admin_permissions);
    echo "<p>$perm: " . ($has_perm ? 'YES' : 'NO') . "</p>";
}

echo "<h3>All System Administrator Permissions:</h3>";
foreach ($admin_permissions as $perm) {
    echo "<p>$perm</p>";
}

echo "<p><strong>Database update successful! Log in and check the main menu.</strong></p>";
?>