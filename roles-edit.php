<?php
/**
 * Edit an existing user role
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

$page_title = sprintf(__('Edit Role: %s', 'cftp_admin'), $role->name);

// Process form submission
if ($_POST) {
    $validation_errors = [];

    // Validate required fields
    if (empty($_POST['name'])) {
        $validation_errors[] = __('Role name is required.', 'cftp_admin');
    }

    // Role level changes removed - roles use auto-generated IDs

    if (empty($validation_errors)) {
        $role_data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'] ?? '',
            'active' => isset($_POST['active']) ? 1 : 0
        ];

        // Role level changes removed - roles use auto-generated IDs

        $result = $role->update($role_data);

        if ($result) {
            $flash->success(__('Role updated successfully.', 'cftp_admin'));

            // Redirect to updated role using role ID
            ps_redirect('roles-edit.php?role=' . $role->id);
        } else {
            $flash->error(__('Could not update role. Please try again.', 'cftp_admin'));
        }
    } else {
        foreach ($validation_errors as $error) {
            $flash->error($error);
        }
    }
}

// Role levels are no longer used - roles use auto-generated IDs

// Get user count for this role
$user_count = $role->getUserCount();

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
                <a href="role-permissions.php?role=<?php echo $role->id; ?>" class="btn btn-primary">
                    <i class="fa fa-key"></i> <?php _e('Manage Permissions', 'cftp_admin'); ?>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <?php _e('Role Information', 'cftp_admin'); ?>
                    <?php if ($role->is_system_role): ?>
                        <span class="badge bg-primary ms-2"><?php _e('System Role', 'cftp_admin'); ?></span>
                    <?php endif; ?>
                </h5>
            </div>
            <div class="card-body">
                <?php if ($role->is_system_role): ?>
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i>
                        <?php _e('This is a system role. Some properties cannot be changed.', 'cftp_admin'); ?>
                    </div>
                <?php endif; ?>

                <form action="" method="post" class="form-horizontal" id="role_form">
                    <?php addCsrf(); ?>

                    <div class="form-group row">
                        <label for="name" class="col-sm-3 control-label"><?php _e('Role Name', 'cftp_admin'); ?></label>
                        <div class="col-sm-9">
                            <input type="text" name="name" id="name" class="form-control required"
                                   value="<?php echo html_output($role->name); ?>"
                                   maxlength="255" required />
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="description" class="col-sm-3 control-label"><?php _e('Description', 'cftp_admin'); ?></label>
                        <div class="col-sm-9">
                            <textarea name="description" id="description" class="form-control" rows="3"><?php echo html_output($role->description); ?></textarea>
                        </div>
                    </div>

                    <!-- Role level fields removed - roles now use auto-generated IDs -->

                    <div class="form-group row">
                        <div class="col-sm-9 offset-sm-3">
                            <div class="form-check">
                                <input type="checkbox" name="active" id="active" class="form-check-input" value="1"
                                       <?php echo $role->active ? 'checked' : ''; ?> />
                                <label for="active" class="form-check-label"><?php _e('Active', 'cftp_admin'); ?></label>
                                <small class="form-text text-muted"><?php _e('Inactive roles cannot be assigned to users', 'cftp_admin'); ?></small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-check"></i> <?php _e('Update Role', 'cftp_admin'); ?>
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
                <h5 class="card-title"><?php _e('Role Statistics', 'cftp_admin'); ?></h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-primary"><?php echo $user_count; ?></h4>
                        <p class="text-muted mb-0"><?php _e('Users', 'cftp_admin'); ?></p>
                    </div>
                    <div class="col-6">
                        <h4 class="text-info"><?php echo count($role->permissions); ?></h4>
                        <p class="text-muted mb-0"><?php _e('Permissions', 'cftp_admin'); ?></p>
                    </div>
                </div>

                <?php if ($user_count > 0): ?>
                    <hr>
                    <a href="users.php?role=<?php echo $role->id; ?>" class="btn btn-sm btn-outline-primary">
                        <i class="fa fa-users"></i> <?php _e('View Users with this Role', 'cftp_admin'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title"><?php _e('Quick Actions', 'cftp_admin'); ?></h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="role-permissions.php?role=<?php echo $role->id; ?>" class="btn btn-outline-primary">
                        <i class="fa fa-key"></i> <?php _e('Manage Permissions', 'cftp_admin'); ?>
                    </a>

                    <?php if (!$role->is_system_role && $user_count == 0): ?>
                        <button type="button" class="btn btn-outline-danger" id="delete-role">
                            <i class="fa fa-trash"></i> <?php _e('Delete Role', 'cftp_admin'); ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($role->is_system_role): ?>
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title"><?php _e('System Role Info', 'cftp_admin'); ?></h5>
                </div>
                <div class="card-body">
                    <p class="text-muted"><?php _e('System roles are built into ProjectSend and provide core functionality. They cannot be deleted and have limited editability.', 'cftp_admin'); ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteButton = document.getElementById('delete-role');

    if (deleteButton) {
        deleteButton.addEventListener('click', function() {
            if (confirm('<?php _e('Are you sure you want to delete this role?', 'cftp_admin'); ?>\n\n<?php _e('This action cannot be undone.', 'cftp_admin'); ?>')) {
                // Create form and submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'process.php';

                form.innerHTML = `
                    <input type="hidden" name="do" value="delete_role">
                    <input type="hidden" name="role_level" value="<?php echo $role->id; ?>">
                    <?php echo addCsrf('return'); ?>
                `;

                document.body.appendChild(form);
                form.submit();
            }
        });
    }
});
</script>

<?php
include_once ADMIN_VIEWS_DIR . DS . 'footer.php';
?>