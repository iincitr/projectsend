<?php
/**
 * Manage permissions for a specific role
 * Only accessible to Super Administrators (level 9)
 */
require_once 'bootstrap.php';
check_access_enhanced(null, ['edit_settings']);

$active_nav = 'users';

// Get role from URL
if (empty($_GET['role'])) {
    $flash->error(__('No role specified.', 'cftp_admin'));
    ps_redirect('roles.php');
}

$role_id = (int)$_GET['role'];
$role = new \ProjectSend\Classes\Roles($role_id);

if (!$role->exists()) {
    $flash->error(__('Role not found.', 'cftp_admin'));
    ps_redirect('roles.php');
}

$page_title = sprintf(__('Manage Permissions: %s', 'cftp_admin'), $role->name);
$page_id = 'role_permissions';

// Process form submission
if ($_POST) {
    // CRITICAL: System Admin (level 9) permissions cannot be edited
    if ($role->name == 'System Administrator') {
        $flash->error(__('System Administrator permissions cannot be modified. System Admin always has ALL permissions.', 'cftp_admin'));
        ps_redirect('role-permissions.php?role=' . $role->id);
    }

    $new_permissions = $_POST['permissions'] ?? [];

    // Ensure it's an array
    if (!is_array($new_permissions)) {
        $new_permissions = [];
    }

    $result = $role->setPermissions($new_permissions);

    if ($result) {
        $flash->success(__('Permissions updated successfully.', 'cftp_admin'));
        ps_redirect('role-permissions.php?role=' . $role->id);
    } else {
        $flash->error(__('Could not update permissions. Please try again.', 'cftp_admin'));
    }
}

// Get all permissions grouped by category
$permissions_grouped = get_permissions_grouped_by_category();
$permission_categories = get_permission_categories();

// Get current role permissions
$current_permissions = $role->permissions;

include_once ADMIN_VIEWS_DIR . DS . 'header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-lg-6">
            </div>
            <div class="col-xs-12 col-sm-12 col-lg-6 text-end">
                <a href="roles-edit.php?role=<?php echo $role->id; ?>" class="btn btn-secondary">
                    <i class="fa fa-edit"></i> <?php _e('Edit Role', 'cftp_admin'); ?>
                </a>
                <a href="roles.php" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> <?php _e('Back to Roles', 'cftp_admin'); ?>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="ps-card">
            <div class="ps-card-body">
                <div class="row align-items-center mb-3">
                    <div class="col">
                        <h5 class="mb-0">
                            <?php _e('Permissions', 'cftp_admin'); ?>
                            <?php if ($role->is_system_role): ?>
                                <span class="badge bg-primary ms-2"><?php _e('System Role', 'cftp_admin'); ?></span>
                            <?php endif; ?>
                        </h5>
                    </div>
                    <?php if ($role->name != 'System Administrator'): ?>
                    <div class="col-auto">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-success" id="select-all">
                                <?php _e('Select All', 'cftp_admin'); ?>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="select-none">
                                <?php _e('Select None', 'cftp_admin'); ?>
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <form action="" method="post" id="permissions_form">
                    <?php addCsrf(); ?>

                    <div class="row">
                        <div class="col-12">
                            <?php if ($role->name == 'System Administrator'): ?>
                                <div class="alert alert-info">
                                    <i class="fa fa-lock"></i>
                                    <strong><?php _e('System Administrator Role', 'cftp_admin'); ?></strong><br>
                                    <?php _e('System Administrator permissions cannot be modified. This role automatically has ALL permissions for security and system integrity.', 'cftp_admin'); ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">
                                    <?php _e('Select the permissions this role should have. Users with this role will be able to perform the selected actions.', 'cftp_admin'); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php foreach ($permissions_grouped as $category => $permissions): ?>
                        <div class="permission-category mb-4">
                            <div class="row">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-2">
                                        <i class="fa fa-<?php
                                        switch($category) {
                                            case 'files': echo 'file'; break;
                                            case 'users': echo 'users'; break;
                                            case 'groups': echo 'th-large'; break;
                                            case 'system': echo 'cogs'; break;
                                            case 'categories': echo 'tags'; break;
                                            case 'assets': echo 'code'; break;
                                            default: echo 'circle';
                                        }
                                        ?>"></i>
                                        <?php echo $permission_categories[$category]; ?>
                                        <?php if ($role->name != 'System Administrator'): ?>
                                        <button type="button" class="btn btn-sm btn-outline-secondary ms-2 category-toggle"
                                                data-category="<?php echo $category; ?>">
                                            <?php _e('Toggle All', 'cftp_admin'); ?>
                                        </button>
                                        <?php endif; ?>
                                    </h6>
                                </div>
                            </div>

                            <div class="row">
                                <?php foreach ($permissions as $permission_key => $permission_data): ?>
                                    <div class="col-md-6 col-lg-4 mb-2">
                                        <div class="form-check">
                                            <input type="checkbox"
                                                   name="permissions[]"
                                                   value="<?php echo $permission_key; ?>"
                                                   id="perm_<?php echo $permission_key; ?>"
                                                   class="form-check-input permission-checkbox"
                                                   data-category="<?php echo $category; ?>"
                                                   <?php echo in_array($permission_key, $current_permissions) ? 'checked' : ''; ?>
                                                   <?php echo ($role->name == 'System Administrator') ? 'disabled' : ''; ?> />
                                            <label for="perm_<?php echo $permission_key; ?>" class="form-check-label">
                                                <strong><?php echo $permission_data['label']; ?></strong>
                                                <?php if (!empty($permission_data['description'])): ?>
                                                    <br><small class="text-muted"><?php echo $permission_data['description']; ?></small>
                                                <?php endif; ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="row mt-4">
                        <div class="col-12">
                            <?php if ($role->name != 'System Administrator'): ?>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fa fa-check"></i> <?php _e('Update Permissions', 'cftp_admin'); ?>
                                </button>
                            <?php endif; ?>
                            <a href="roles.php" class="btn btn-secondary">
                                <?php echo ($role->name == 'System Administrator') ? __('Back to Roles', 'cftp_admin') : __('Cancel', 'cftp_admin'); ?>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



<?php
include_once ADMIN_VIEWS_DIR . DS . 'footer.php';
?>