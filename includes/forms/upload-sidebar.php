<?php
// Upload sidebar - contains storage and encryption options
// Storage selection for users with permission
$can_select_storage = current_user_can('upload_storage_select');
$default_storage = get_option('default_upload_storage', 'local');

// Encryption settings are defined in upload.php for use in JavaScript
?>

<?php if ($can_select_storage): ?>
    <div class="ps-card mb-3">
        <div class="ps-card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h4 class="mb-0"><?php _e('Storage Destination', 'cftp_admin'); ?></h4>
                <a href="<?php echo BASE_URI; ?>integrations.php" target="_blank" class="btn btn-sm btn-outline-secondary">
                    <i class="fa fa-cog"></i> <?php _e('Manage', 'cftp_admin'); ?>
                </a>
            </div>
            <label for="storage_selector" class="form-label"><?php _e('Choose where to store the uploaded files', 'cftp_admin'); ?></label>
            <select name="storage_selector" id="storage_selector" class="form-select">
                <?php
                // Local storage option
                $selected = ($default_storage === 'local') ? 'selected' : '';
                echo '<option value="local" ' . $selected . '>' . __('Local storage', 'cftp_admin') . '</option>';

                // External storage integrations
                $integrations_handler = new \ProjectSend\Classes\Integrations();
                $active_integrations = $integrations_handler->getAll(true); // Only active

                foreach ($active_integrations as $integration) {
                    $selected = ($default_storage == $integration['id']) ? 'selected' : '';
                    $type_config = \ProjectSend\Classes\Integrations::getTypeConfig($integration['type']);
                    $type_name = $type_config ? $type_config['name'] : ucfirst($integration['type']);
                    echo '<option value="' . $integration['id'] . '" ' . $selected . '>' .
                         html_output($integration['name']) . ' (' . $type_name . ')</option>';
                }
                ?>
            </select>
            <div class="form-text"><?php _e('Files will be stored in the selected destination.', 'cftp_admin'); ?></div>
        </div>
    </div>
<?php endif; ?>

<?php if ($show_encryption_option || $encryption_required): ?>
    <div class="ps-card">
        <div class="ps-card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h4 class="mb-0"><?php _e('File Encryption', 'cftp_admin'); ?></h4>
                <a href="<?php echo BASE_URI; ?>options.php?section=encryption" target="_blank" class="btn btn-sm btn-outline-secondary">
                    <i class="fa fa-cog"></i> <?php _e('Manage', 'cftp_admin'); ?>
                </a>
            </div>
            <?php if ($encryption_required): ?>
                <div class="alert alert-info mb-0">
                    <i class="fa fa-lock"></i> <?php _e('File encryption is required for all uploads. Your files will be automatically encrypted at rest on the server.', 'cftp_admin'); ?>
                </div>
            <?php else: ?>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="encrypt_file_checkbox" id="encrypt_file_checkbox" value="1" <?php echo ($encryption_enabled) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="encrypt_file_checkbox">
                        <i class="fa fa-lock"></i> <?php _e('Encrypt files on server', 'cftp_admin'); ?>
                    </label>
                </div>
                <div class="form-text">
                    <?php _e('Files will be encrypted at rest using AES-256-GCM encryption.', 'cftp_admin'); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
