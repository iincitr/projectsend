<?php
/**
 * Show the list of user roles and their permissions
 * Only accessible to Super Administrators (level 9)
 */
require_once 'bootstrap.php';
check_access_enhanced(null, ['edit_settings']);

$active_nav = 'users';
$page_title = __('User Roles Management', 'cftp_admin');

// Get all roles
$roles = get_all_roles();

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
                <?php if (custom_roles_enabled()): ?>
                    <a href="roles-add.php" class="btn btn-primary">
                        <i class="fa fa-plus"></i> <?php _e('Add new role', 'cftp_admin'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <?php if (!custom_roles_enabled()): ?>
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i>
                <?php _e('Custom roles are currently disabled. Only the default system roles are available.', 'cftp_admin'); ?>
                <a href="options.php?section=advanced" class="btn btn-sm btn-light ms-2">
                    <?php _e('Enable Custom Roles', 'cftp_admin'); ?>
                </a>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><?php _e('User Roles', 'cftp_admin'); ?></h5>
            </div>
            <div class="card-body">
                <?php if (empty($roles)): ?>
                    <p class="text-muted"><?php _e('No roles found.', 'cftp_admin'); ?></p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th><?php _e('Role Name', 'cftp_admin'); ?></th>
                                    <th><?php _e('Description', 'cftp_admin'); ?></th>
                                    <th><?php _e('Users', 'cftp_admin'); ?></th>
                                    <th><?php _e('Permissions', 'cftp_admin'); ?></th>
                                    <th><?php _e('Type', 'cftp_admin'); ?></th>
                                    <th><?php _e('Status', 'cftp_admin'); ?></th>
                                    <th><?php _e('Actions', 'cftp_admin'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($roles as $role):
                                    $role_obj = new \ProjectSend\Classes\Roles($role['id']);
                                    $user_count = $role_obj->getUserCount();
                                    $permissions = get_role_permissions($role['id']);
                                ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo html_output($role['name']); ?></strong>
                                            <span class="badge bg-<?php echo $role['name'] == 'System Administrator' ? 'danger' : ($role['name'] == 'Account Manager' ? 'warning' : ($role['name'] == 'Uploader' ? 'info' : 'secondary')); ?> ms-2">
                                                <?php
                                                if ($role['name'] == 'System Administrator') echo 'Super Admin';
                                                elseif ($role['name'] == 'Account Manager') echo 'Admin';
                                                elseif ($role['name'] == 'Uploader') echo 'User';
                                                elseif ($role['name'] == 'Client') echo 'Client';
                                                else echo 'Custom';
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?php echo html_output($role['description']); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark"><?php echo $user_count; ?></span>
                                            <?php if ($user_count > 0): ?>
                                                <a href="users.php?role=<?php echo $role['id']; ?>" class="btn btn-sm btn-outline-primary ms-1">
                                                    <i class="fa fa-users"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo count($permissions); ?></span>
                                            <a href="role-permissions.php?role=<?php echo $role['id']; ?>" class="btn btn-sm btn-outline-secondary ms-1">
                                                <i class="fa fa-key"></i>
                                            </a>
                                        </td>
                                        <td>
                                            <?php if ($role['is_system_role']): ?>
                                                <span class="badge bg-primary"><?php _e('System', 'cftp_admin'); ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-success"><?php _e('Custom', 'cftp_admin'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($role['active']): ?>
                                                <span class="badge bg-success"><?php _e('Active', 'cftp_admin'); ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?php _e('Inactive', 'cftp_admin'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="role-permissions.php?role=<?php echo $role['id']; ?>" class="btn btn-sm btn-outline-secondary" title="<?php _e('Manage Permissions', 'cftp_admin'); ?>">
                                                    <i class="fa fa-key"></i>
                                                </a>

                                                <?php if (!$role['is_system_role'] && custom_roles_enabled()): ?>
                                                    <a href="roles-edit.php?role=<?php echo $role['id']; ?>" class="btn btn-sm btn-outline-primary" title="<?php _e('Edit Role', 'cftp_admin'); ?>">
                                                        <i class="fa fa-edit"></i>
                                                    </a>

                                                    <?php if ($user_count == 0): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-danger delete-role"
                                                                data-role="<?php echo $role['id']; ?>"
                                                                data-name="<?php echo html_output($role['name']); ?>"
                                                                title="<?php _e('Delete Role', 'cftp_admin'); ?>">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <a href="roles-edit.php?role=<?php echo $role['id']; ?>" class="btn btn-sm btn-outline-secondary" title="<?php _e('View Details', 'cftp_admin'); ?>">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><?php _e('Role Hierarchy', 'cftp_admin'); ?></h5>
            </div>
            <div class="card-body">
                <p class="text-muted"><?php _e('Roles are organized in hierarchy, with higher privileges roles having more access:', 'cftp_admin'); ?></p>

                <div class="row">
                    <?php
                    $role_hierarchy = \ProjectSend\Classes\Roles::getRoleHierarchy();
                    foreach ($role_hierarchy as $role):
                        $role_type = '';
                        if ($role['name'] == 'System Administrator') $role_type = 'Super Admin';
                        elseif ($role['name'] == 'Account Manager') $role_type = 'Admin';
                        elseif ($role['name'] == 'Uploader') $role_type = 'User';
                        elseif ($role['name'] == 'Client') $role_type = 'Client';
                        else $role_type = 'Custom Role';
                    ?>
                        <div class="col-md-3 mb-3">
                            <div class="card border-<?php echo $role['name'] == 'System Administrator' ? 'danger' : ($role['name'] == 'Account Manager' ? 'warning' : ($role['name'] == 'Uploader' ? 'info' : 'secondary')); ?>">
                                <div class="card-header text-center">
                                    <strong><?php echo $role_type; ?></strong>
                                </div>
                                <div class="card-body text-center">
                                    <h6><?php echo html_output($role['name']); ?></h6>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle role deletion
    document.querySelectorAll('.delete-role').forEach(function(button) {
        button.addEventListener('click', function() {
            const roleId = this.dataset.role;
            const roleName = this.dataset.name;

            if (confirm('<?php _e('Are you sure you want to delete the role', 'cftp_admin'); ?> "' + roleName + '"?\n\n<?php _e('This action cannot be undone.', 'cftp_admin'); ?>')) {
                // Create form and submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'process.php';

                form.innerHTML = `
                    <input type="hidden" name="do" value="delete_role">
                    <input type="hidden" name="role_id" value="${roleId}">
                    <?php echo addCsrf('return'); ?>
                `;

                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script>

<?php
include_once ADMIN_VIEWS_DIR . DS . 'footer.php';
?>