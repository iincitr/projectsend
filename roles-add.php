<?php
/**
 * Add a new user role
 * Only accessible to Super Administrators (level 9)
 */
require_once 'bootstrap.php';
check_access_enhanced(null, ['edit_settings']);

// Check if custom roles are enabled
if (!custom_roles_enabled()) {
    $flash->error(__('Custom roles are disabled.', 'cftp_admin'));
    ps_redirect('roles.php');
}

$active_nav = 'users';
$page_title = __('Add New Role', 'cftp_admin');

// Process form submission
if ($_POST) {
    $validation_errors = [];

    // Validate required fields
    if (empty($_POST['name'])) {
        $validation_errors[] = __('Role name is required.', 'cftp_admin');
    }

    // Role level validation removed - roles use auto-generated IDs

    if (empty($validation_errors)) {
        $role_data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'] ?? '',
            'is_system_role' => 0,
            'active' => isset($_POST['active']) ? 1 : 0
        ];

        $role = new \ProjectSend\Classes\Roles();
        $result = $role->create($role_data);

        if ($result) {
            // Set permissions if provided
            if (!empty($_POST['permissions']) && is_array($_POST['permissions'])) {
                $role->setPermissions($_POST['permissions']);
            }

            $flash->success(__('Role created successfully.', 'cftp_admin'));
            ps_redirect('roles.php');
        } else {
            $flash->error(__('Could not create role. Please try again.', 'cftp_admin'));
        }
    } else {
        foreach ($validation_errors as $error) {
            $flash->error($error);
        }
    }
}

// Role levels are no longer used - roles are created with auto-generated IDs

// Get all permissions grouped by category
$permissions_grouped = get_permissions_grouped_by_category();
$permission_categories = get_permission_categories();

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
                <a href="roles.php" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> <?php _e('Back to Roles', 'cftp_admin'); ?>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><?php _e('Role Information', 'cftp_admin'); ?></h5>
            </div>
            <div class="card-body">
                <form action="" method="post" class="form-horizontal" id="role_form">
                    <?php addCsrf(); ?>

                    <div class="form-group row">
                        <label for="name" class="col-sm-3 control-label"><?php _e('Role Name', 'cftp_admin'); ?></label>
                        <div class="col-sm-9">
                            <input type="text" name="name" id="name" class="form-control required"
                                   value="<?php echo isset($_POST['name']) ? html_output($_POST['name']) : ''; ?>"
                                   maxlength="255" required />
                            <small class="form-text text-muted"><?php _e('A descriptive name for this role (e.g., "Project Manager", "Content Editor")', 'cftp_admin'); ?></small>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="description" class="col-sm-3 control-label"><?php _e('Description', 'cftp_admin'); ?></label>
                        <div class="col-sm-9">
                            <textarea name="description" id="description" class="form-control" rows="3"><?php echo isset($_POST['description']) ? html_output($_POST['description']) : ''; ?></textarea>
                            <small class="form-text text-muted"><?php _e('Optional description explaining what this role is for', 'cftp_admin'); ?></small>
                        </div>
                    </div>

                    <!-- Role level field removed - roles now use auto-generated IDs -->

                    <div class="form-group row">
                        <div class="col-sm-9 offset-sm-3">
                            <div class="form-check">
                                <input type="checkbox" name="active" id="active" class="form-check-input" value="1"
                                       <?php echo (!isset($_POST['active']) || $_POST['active']) ? 'checked' : ''; ?> />
                                <label for="active" class="form-check-label"><?php _e('Active', 'cftp_admin'); ?></label>
                                <small class="form-text text-muted"><?php _e('Inactive roles cannot be assigned to users', 'cftp_admin'); ?></small>
                            </div>
                        </div>
                    </div>

                    <!-- Permissions Selection -->
                    <div class="form-group row">
                        <div class="col-sm-12">
                            <hr>
                            <h6><?php _e('Permissions', 'cftp_admin'); ?></h6>
                            <p class="text-muted"><?php _e('Select the permissions this role should have. You can also configure permissions later.', 'cftp_admin'); ?></p>

                            <div class="mb-3">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-success" id="select-all-permissions">
                                        <?php _e('Select All', 'cftp_admin'); ?>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" id="select-none-permissions">
                                        <?php _e('Select None', 'cftp_admin'); ?>
                                    </button>
                                </div>
                            </div>

                            <?php foreach ($permissions_grouped as $category => $permissions): ?>
                                <div class="permission-category-create mb-4">
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
                                        <button type="button" class="btn btn-sm btn-outline-secondary ms-2 category-toggle-create"
                                                data-category="<?php echo $category; ?>">
                                            <?php _e('Toggle All', 'cftp_admin'); ?>
                                        </button>
                                    </h6>

                                    <div class="row">
                                        <?php foreach ($permissions as $permission_key => $permission_data): ?>
                                            <div class="col-md-6 col-lg-4 mb-2">
                                                <div class="form-check">
                                                    <input type="checkbox"
                                                           name="permissions[]"
                                                           value="<?php echo $permission_key; ?>"
                                                           id="perm_create_<?php echo $permission_key; ?>"
                                                           class="form-check-input permission-checkbox-create"
                                                           data-category="<?php echo $category; ?>"
                                                           <?php echo (isset($_POST['permissions']) && in_array($permission_key, $_POST['permissions'])) ? 'checked' : ''; ?> />
                                                    <label for="perm_create_<?php echo $permission_key; ?>" class="form-check-label">
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
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-check"></i> <?php _e('Create Role', 'cftp_admin'); ?>
                            </button>
                            <a href="roles.php" class="btn btn-secondary">
                                <?php _e('Cancel', 'cftp_admin'); ?>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><?php _e('Permissions Preview', 'cftp_admin'); ?></h5>
            </div>
            <div class="card-body">
                <p class="text-muted"><?php _e('Create the role first and then customize permissions as needed.', 'cftp_admin'); ?></p>

                <div id="permissions-preview" style="display: none;">
                    <h6><?php _e('Default Permissions for Level:', 'cftp_admin'); ?> <span id="preview-level"></span></h6>
                    <div id="permissions-list"></div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title"><?php _e('Role Guidelines', 'cftp_admin'); ?></h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li><strong><?php _e('Naming:', 'cftp_admin'); ?></strong> <?php _e('Use descriptive names that indicate the role\'s purpose', 'cftp_admin'); ?></li>
                    <li><strong><?php _e('Priority:', 'cftp_admin'); ?></strong> <?php _e('Higher numbers = more privileges', 'cftp_admin'); ?></li>
                    <li><strong><?php _e('Permissions:', 'cftp_admin'); ?></strong> <?php _e('Can be customized after role creation', 'cftp_admin'); ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.permission-category-create {
    border-left: 3px solid #e9ecef;
    padding-left: 1rem;
}

.permission-checkbox-create:checked + .form-check-label {
    color: #0d6efd;
}

.category-toggle-create {
    font-size: 0.75rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleLevel = document.getElementById('role_level');
    const permissionsPreview = document.getElementById('permissions-preview');
    const previewLevel = document.getElementById('preview-level');
    const permissionsList = document.getElementById('permissions-list');

    // Default permissions for each level (simplified)
    const defaultPermissions = {
        9: ['Full system access', 'Manage users', 'Manage settings', 'Manage roles'],
        8: ['Manage clients', 'Manage files', 'View statistics'],
        7: ['Upload files', 'Manage own files'],
        6: ['Upload files', 'View own files'],
        5: ['Upload files', 'View own files'],
        4: ['Upload files', 'View own files'],
        3: ['Upload files', 'View own files'],
        2: ['Upload files', 'View own files'],
        1: ['Upload files', 'View own files']
    };

    roleLevel.addEventListener('change', function() {
        const level = parseInt(this.value);

        if (level && defaultPermissions[level]) {
            previewLevel.textContent = level;

            const perms = defaultPermissions[level] || ['Basic file access'];
            permissionsList.innerHTML = perms.map(perm =>
                '<span class="badge bg-secondary me-1 mb-1">' + perm + '</span>'
            ).join('');

            permissionsPreview.style.display = 'block';
        } else {
            permissionsPreview.style.display = 'none';
        }
    });

    // Permission management for role creation
    const permissionCheckboxes = document.querySelectorAll('.permission-checkbox-create');
    const selectAllBtn = document.getElementById('select-all-permissions');
    const selectNoneBtn = document.getElementById('select-none-permissions');
    const categoryToggles = document.querySelectorAll('.category-toggle-create');

    // Select all permissions
    selectAllBtn.addEventListener('click', function() {
        permissionCheckboxes.forEach(cb => cb.checked = true);
    });

    // Select no permissions
    selectNoneBtn.addEventListener('click', function() {
        permissionCheckboxes.forEach(cb => cb.checked = false);
    });

    // Category toggles
    categoryToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const category = this.dataset.category;
            const categoryCheckboxes = document.querySelectorAll(`.permission-checkbox-create[data-category="${category}"]`);
            const allChecked = Array.from(categoryCheckboxes).every(cb => cb.checked);

            categoryCheckboxes.forEach(cb => cb.checked = !allChecked);
        });
    });
});
</script>

<?php
include_once ADMIN_VIEWS_DIR . DS . 'footer.php';
?>