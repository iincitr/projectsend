<?php
/**
 * General options form configuration
 * Refactored to use array-based configuration - matches original exactly
 */

// Define the form sections and fields
$form_sections = [
    [
        'title' => __('General', 'cftp_admin'),
        'description' => __('Basic information to be shown around the site. The time format and zones values affect how the clients see the dates on their files lists.', 'cftp_admin'),
        'fields' => [
            [
                'type' => 'text',
                'name' => 'this_install_title',
                'label' => __('Site name', 'cftp_admin'),
                'required' => true
            ],
            [
                'type' => 'custom',
                'name' => 'timezone',
                'render_callback' => function($field) {
                    echo '<div class="form-group row">';
                    echo '<label for="timezone" class="col-sm-4 control-label">' . __('Timezone', 'cftp_admin') . '</label>';
                    echo '<div class="col-sm-8">';
                    include_once 'timezones.php';
                    echo '</div>';
                    echo '</div>';
                }
            ],
            [
                'type' => 'text',
                'name' => 'timeformat',
                'label' => __('Time format', 'cftp_admin'),
                'required' => true,
                'note' => sprintf(__('For example, %s will display the current date and time like this: %s', 'cftp_admin'), 'd/m/Y h:i:s', date('d/m/Y h:i:s')) . '<br>' .
                         sprintf(__("For the full list of available values, visit %s the official PHP Manual %s", 'cftp_admin'), '<a href="https://php.net/manual/en/function.date.php" target="_blank">', '</a>') . '<br>' .
                         __("This date will be considered for files expiration.", 'cftp_admin') . '<br>' .
                         __("You can adjust your timezone if your local date/time does not match your server's settings.", 'cftp_admin')
            ],
            [
                'type' => 'checkbox',
                'name' => 'footer_custom_enable',
                'label' => __("Use custom footer text", 'cftp_admin')
            ],
            [
                'type' => 'text',
                'name' => 'footer_custom_content',
                'label' => __('Footer content', 'cftp_admin')
            ],
            [
                'type' => 'select',
                'name' => 'pagination_results_per_page',
                'label' => __('Pagination results per page', 'cftp_admin'),
                'options' => array_combine([10, 20, 50, 100], [10, 20, 50, 100]),
                'required' => true,
                'note' => __('Applies to pagination in all administration areas', 'cftp_admin')
            ]
        ]
    ],
    [
        'title' => __('Editor', 'cftp_admin'),
        'fields' => [
            [
                'type' => 'checkbox',
                'name' => 'files_descriptions_use_ckeditor',
                'label' => __("Use the visual editor on files descriptions", 'cftp_admin')
            ]
        ]
    ],
    [
        'title' => __('Uploads', 'cftp_admin'),
        'fields' => [
            [
                'type' => 'checkbox',
                'name' => 'uploads_organize_folders_by_date',
                'label' => __("Organize uploads in folders based on year and month", 'cftp_admin'),
                'note' => __("For new uploads only. Will not affect existing files.", 'cftp_admin')
            ],
            [
                'type' => 'select',
                'name' => 'upload_chunk_size',
                'label' => __('Chunk size', 'cftp_admin'),
                'options' => array_combine([1, 5, 10, 20, 50, 100], array_map(function($size) { return $size . ' mb.'; }, [1, 5, 10, 20, 50, 100])),
                'required' => true,
                'note' => __("Uploaded files are split into chunks which are then compiled on your server. Be sure to check by uploading one small and large files after changing this setting to make sure your internet connection and server can handle them.", 'cftp_admin')
            ]
        ]
    ],
    [
        'title' => __('Uploads defaults', 'cftp_admin'),
        'fields' => [
            [
                'type' => 'checkbox',
                'name' => 'files_default_expire',
                'label' => __("Files expire by default", 'cftp_admin'),
                'note' => __('Users can always set an expiration date for files. This option just makes the checkbox marked by default in the editor.', 'cftp_admin') . ' ' .
                         __('For clients not allowed to set it, this setting will be directly applied to the file.', 'cftp_admin')
            ],
            [
                'type' => 'checkbox',
                'name' => 'files_default_public',
                'label' => __("Files are public by default", 'cftp_admin'),
                'note' => __('Users can always set a download to be public. This option just makes the checkbox marked by default in the editor.', 'cftp_admin') . ' ' .
                         __('For clients not allowed to set it, this setting will be directly applied to the file.', 'cftp_admin')
            ],
            [
                'type' => 'text',
                'name' => 'files_default_expire_days_after',
                'label' => __('After these many days:', 'cftp_admin')
            ]
        ]
    ],
    [
        'title' => __('Language', 'cftp_admin'),
        'fields' => [
            [
                'type' => 'checkbox',
                'name' => 'use_browser_lang',
                'label' => __("Detect user browser language", 'cftp_admin'),
                'note' => __("If available, will override the default one from the system configuration file. Affects all users and clients.", 'cftp_admin')
            ]
        ]
    ],
    [
        'title' => __('Downloads', 'cftp_admin'),
        'fields' => [
            [
                'type' => 'custom',
                'name' => 'download_method',
                'render_callback' => function($field) {
                    ?>
                    <div class="form-group row">
                        <label for="download_method" class="col-sm-4 control-label"><?php _e('Download method', 'cftp_admin'); ?></label>
                        <div class="col-sm-8">
                            <select class="form-select" name="download_method" id="download_method" required>
                                <option value="php" <?php echo (get_option('download_method') == 'php') ? 'selected="selected"' : ''; ?>>php</option>
                                <option value="apache_xsendfile" <?php echo (get_option('download_method') == 'apache_xsendfile') ? 'selected="selected"' : ''; ?>>XSendFile (apache)</option>
                                <option value="nginx_xaccel" <?php echo (get_option('download_method') == 'nginx_xaccel') ? 'selected="selected"' : ''; ?>>X-Accel (nginx)</option>
                            </select>
                            <div class="method_note none" data-method="php">
                                <p class="field_note form-text"><?php _e("Serving files with php is the default method and does not require any changes to your webserver. However, very large files could download with errors depending on your php configuration.", 'cftp_admin'); ?></p>
                            </div>
                            <div class="method_note none" data-method="apache_xsendfile">
                                <p class="field_note form-text"><?php _e("XSendfile improves downloads by allowing the web server to send the file directly bypassing php and it's limitations. This in an advanced feature that requires you to install and enable a module on your server.", 'cftp_admin'); ?></p>
                                <p class="field_note form-text"><?php _e("Be aware that if the module is not set up correctly, downloads will trigger but the files will have a length of 0 bytes.", 'cftp_admin'); ?></p>
                            </div>
                            <div class="method_note none" data-method="nginx_xaccel">
                                <p class="field_note form-text"><?php _e("X-Accel is a method available in nginx that allows the system to serve files directly, bypassing php and it's limitations. To configure it, you need to edit your server block and add the following code:", 'cftp_admin'); ?></p>
                                <pre>location <?php echo XACCEL_FILES_URL; ?> {
    internal;
    alias <?php echo UPLOADED_FILES_ROOT; ?>/;
}</pre>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            ],
            [
                'type' => 'checkbox',
                'name' => 'download_logging_ignore_file_author',
                'label' => __("Do not log downloads by the file's uploader", 'cftp_admin'),
                'note' => __("When a user or client downloads their own files, do not log the download or add to the downloads count.", 'cftp_admin')
            ]
        ]
    ],
    [
        'title' => __('System location', 'cftp_admin'),
        'description' => __('These options are to be changed only if you are moving the system to another place. Changes here can cause ProjectSend to stop working.', 'cftp_admin'),
        'fields' => [
            [
                'type' => 'text',
                'name' => 'base_uri',
                'label' => __('System URI', 'cftp_admin'),
                'value' => BASE_URI,
                'required' => true
            ]
        ]
    ],
    [
        'title' => __('Custom download URI', 'cftp_admin'),
        'fields' => [
            [
                'type' => 'text',
                'name' => 'custom_download_uri',
                'label' => __('Custom download URI base', 'cftp_admin'),
                'note' => sprintf(__("The default URL base is %s. If you set up a custom domain that acts as shortener set the URL here.", 'cftp_admin'), BASE_URI.'custom-download.php?link=') . '<br>' .
                         sprintf(__('When setting up your vhost, make sure to redirect to %s', 'cftp_admin'), BASE_URI.'custom-download.php?link=$file_alias')
            ]
        ],
        'divider' => false // No divider at the end
    ]
];

// Render the form sections
render_options_form_sections($form_sections);