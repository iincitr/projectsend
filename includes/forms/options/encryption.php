<?php
/**
 * File Encryption options form configuration
 */

// Define the form sections and fields
$form_sections = [
    [
        'title' => __('Encryption Settings', 'cftp_admin'),
        'description' => __('Enable server-side encryption for uploaded files. Files are encrypted at rest using AES-256-GCM and automatically decrypted when downloaded.', 'cftp_admin'),
        'fields' => [
            [
                'type' => 'checkbox',
                'name' => 'files_encryption_enabled',
                'label' => __('Enable file encryption feature', 'cftp_admin'),
                'note' => __('When enabled, the encryption feature becomes available. Files can be encrypted based on the setting below.', 'cftp_admin')
            ],
            [
                'type' => 'checkbox',
                'name' => 'files_encryption_required',
                'label' => __('Make encryption mandatory', 'cftp_admin'),
                'note' => __('When enabled, ALL uploaded files will be automatically encrypted. When disabled, users can choose whether to encrypt each file during upload. Note: The encryption feature must be enabled above for this option to work.', 'cftp_admin')
            ],
            [
                'type' => 'number',
                'name' => 'files_encryption_max_file_size',
                'label' => __('Maximum file size for encryption (MB)', 'cftp_admin'),
                'min' => 0,
                'note' => __('Maximum file size that can be encrypted. Set to 0 for no limit. Note: Very large files may take longer to encrypt/decrypt.', 'cftp_admin'),
                'required' => true
            ]
        ]
    ],
    [
        'title' => __('Encryption Tools', 'cftp_admin'),
        'description' => __('Manage and encrypt existing files.', 'cftp_admin'),
        'fields' => [
            [
                'type' => 'custom',
                'name' => 'encryption_tools',
                'render_callback' => function($field) {
                    ?>
                    <div class="form-group row">
                        <div class="col-sm-8 offset-sm-4">
                            <a href="<?php echo BASE_URI; ?>encrypt-files.php" class="btn btn-primary">
                                <i class="fa fa-lock"></i> <?php _e('Encrypt Unencrypted Files', 'cftp_admin'); ?>
                            </a>
                            <p class="field_note form-text mt-2">
                                <?php _e('Batch encrypt files that were uploaded before encryption was enabled.', 'cftp_admin'); ?>
                            </p>
                        </div>
                    </div>
                    <?php
                }
            ]
        ],
        'divider' => false
    ]
];

// Render the form sections
render_options_form_sections($form_sections);
