<?php
require_once 'bootstrap.php';

echo "<h2>Current User Debug Information</h2>";
echo "<p>Current User ID: " . (defined('CURRENT_USER_ID') ? CURRENT_USER_ID : 'undefined') . "</p>";
echo "<p>User is logged in: " . (user_is_logged_in() ? 'Yes' : 'No') . "</p>";

if (user_is_logged_in()) {
    $user = new \ProjectSend\Classes\Users(CURRENT_USER_ID);
    echo "<p>User Role ID: " . $user->role_id . "</p>";
    echo "<p>User Role Name: " . $user->getRoleName() . "</p>";

    echo "<h3>Testing key permissions:</h3>";
    echo "<p>edit_settings: " . (current_user_can('edit_settings') ? 'YES' : 'NO') . "</p>";
    echo "<p>manage_users: " . (current_user_can('manage_users') ? 'YES' : 'NO') . "</p>";
    echo "<p>manage_clients: " . (current_user_can('manage_clients') ? 'YES' : 'NO') . "</p>";
    echo "<p>manage_groups: " . (current_user_can('manage_groups') ? 'YES' : 'NO') . "</p>";

    echo "<h3>All available permissions (first 20):</h3>";
    $available_permissions = get_available_permissions();
    $count = 0;
    foreach ($available_permissions as $perm => $data) {
        if ($count++ > 20) break;
        $can = current_user_can($perm);
        echo "<p>$perm: " . ($can ? 'YES' : 'NO') . "</p>";
    }
}
?>