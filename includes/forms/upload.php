<?php
// Contains the form that is used to upload files
?>
<form action="files-edit.php" name="upload_form" id="upload_form" method="post" enctype="multipart/form-data">
    <?php addCsrf(); ?>
    <input type="hidden" name="uploaded_files" id="uploaded_files" value="" />
    <input type="hidden" name="editor_type" value="new_files" />
    <input type="hidden" name="selected_storage" id="selected_storage" value="" />
    <input type="hidden" name="encrypt_file" id="encrypt_file" value="0" />

    <?php
    // Storage selection for users with permission
    $can_select_storage = current_user_can('upload_storage_select');
    $default_storage = get_option('default_upload_storage', 'local');

    // Encryption settings are defined in upload.php for use in JavaScript
    ?>

    <?php if ($can_select_storage): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="ps-card">
                    <div class="ps-card-body">
                        <h4><?php _e('Storage Destination', 'cftp_admin'); ?></h4>
                        <div class="row">
                            <div class="col-md-6">
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
                                <div class="form-text"><?php _e('Files will be stored in the selected destination. This can affect download speed and availability.', 'cftp_admin'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($show_encryption_option || $encryption_required): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="ps-card">
                    <div class="ps-card-body">
                        <h4><?php _e('File Encryption', 'cftp_admin'); ?></h4>
                        <div class="row">
                            <div class="col-md-12">
                                <?php if ($encryption_required): ?>
                                    <div class="alert alert-info">
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
                                        <?php _e('When enabled, files will be encrypted at rest using AES-256-GCM encryption and automatically decrypted when downloaded.', 'cftp_admin'); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div id="uploader">
        <div class="message message_error">
            <p><?php _e("Your browser doesn't support HTML5, Flash or Silverlight. Please update your browser or install Adobe Flash or Silverlight to continue.",'cftp_admin'); ?></p>
        </div>
    </div>
    <div class="after_form_buttons">
        <button type="submit" name="Submit" class="btn btn-wide btn-primary" id="btn-submit"><?php _e('Upload files','cftp_admin'); ?></button>
    </div>
    <div class="message message_info message_uploading">
        <p><?php _e("Your files are being uploaded! Progress indicators may take a while to update, but work is still being done behind the scenes.",'cftp_admin'); ?></p>
    </div>
</form>
