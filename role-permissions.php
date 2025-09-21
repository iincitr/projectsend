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
                <h1 class="page-title">
                    <?php echo $page_title; ?>
                </h1>
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
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="card-title mb-0">
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
            </div>
            <div class="card-body">
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

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><?php _e('Permission Summary', 'cftp_admin'); ?></h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <h4 class="text-primary" id="selected-count"><?php echo count($current_permissions); ?></h4>
                        <p class="text-muted"><?php _e('Selected', 'cftp_admin'); ?></p>
                    </div>
                    <div class="col-md-3">
                        <h4 class="text-secondary" id="total-count"><?php echo count(get_available_permissions()); ?></h4>
                        <p class="text-muted"><?php _e('Total Available', 'cftp_admin'); ?></p>
                    </div>
                    <div class="col-md-3">
                        <h4 class="text-info"><?php echo $role->getUserCount(); ?></h4>
                        <p class="text-muted"><?php _e('Users Affected', 'cftp_admin'); ?></p>
                    </div>
                    <div class="col-md-3">
                        <h4 class="text-warning" id="changes-count">0</h4>
                        <p class="text-muted"><?php _e('Unsaved Changes', 'cftp_admin'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
    const selectAllBtn = document.getElementById('select-all');
    const selectNoneBtn = document.getElementById('select-none');
    const categoryToggles = document.querySelectorAll('.category-toggle');
    const selectedCount = document.getElementById('selected-count');
    const changesCount = document.getElementById('changes-count');

    // Store initial state
    const initialState = Array.from(permissionCheckboxes).map(cb => cb.checked);
    let changeCount = 0;

    function updateCounts() {
        const checked = document.querySelectorAll('.permission-checkbox:checked').length;
        selectedCount.textContent = checked;

        // Calculate changes
        changeCount = 0;
        permissionCheckboxes.forEach((cb, index) => {
            if (cb.checked !== initialState[index]) {
                changeCount++;
            }
        });
        changesCount.textContent = changeCount;
        changesCount.className = changeCount > 0 ? 'text-warning' : 'text-muted';
    }

    // Select all permissions
    selectAllBtn.addEventListener('click', function() {
        permissionCheckboxes.forEach(cb => cb.checked = true);
        updateCounts();
    });

    // Select no permissions
    selectNoneBtn.addEventListener('click', function() {
        permissionCheckboxes.forEach(cb => cb.checked = false);
        updateCounts();
    });

    // Category toggles
    categoryToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const category = this.dataset.category;
            const categoryCheckboxes = document.querySelectorAll(`.permission-checkbox[data-category="${category}"]`);
            const allChecked = Array.from(categoryCheckboxes).every(cb => cb.checked);

            categoryCheckboxes.forEach(cb => cb.checked = !allChecked);
            updateCounts();
        });
    });

    // Update counts when checkboxes change
    permissionCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateCounts);
    });

    // Warn about unsaved changes
    window.addEventListener('beforeunload', function(e) {
        if (changeCount > 0) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    // Don't warn when submitting form
    document.getElementById('permissions_form').addEventListener('submit', function() {
        window.removeEventListener('beforeunload', arguments.callee);
    });

    // Initial count update
    updateCounts();
});
</script>

<style>
.permission-category {
    border-left: 3px solid #e9ecef;
    padding-left: 1rem;
}

.form-check-label {
    cursor: pointer;
}

.form-check-input:checked + .form-check-label {
    color: #0d6efd;
}

.category-toggle {
    font-size: 0.75rem;
}
</style>

<?php
include_once ADMIN_VIEWS_DIR . DS . 'footer.php';
?>