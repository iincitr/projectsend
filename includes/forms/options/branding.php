<?php
/**
 * Branding options form configuration
 * Enhanced layout with improved visual presentation
 */

// Define the form sections and fields
$form_sections = [
    [
        'title' => __('Brand Identity', 'cftp_admin'),
        'description' => __('Customize your ProjectSend installation with your company branding. Upload your logo and favicon to create a professional, branded experience for your clients.', 'cftp_admin'),
        'fields' => [
            [
                'type' => 'custom',
                'name' => 'max_file_size_hidden',
                'render_callback' => function($field) {
                    echo '<input type="hidden" name="MAX_FILE_SIZE" value="1000000000">';
                }
            ],
            [
                'type' => 'custom',
                'name' => 'branding_cards',
                'render_callback' => function($field) {
                    global $logo_file_info;
                    $favicon_filename = get_option('favicon_filename');
                    ?>
                    <div class="row branding-cards">
                        <!-- Logo Card -->
                        <div class="col-lg-6 mb-4">
                            <div class="card h-100 branding-card">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <?php _e('Company Logo', 'cftp_admin'); ?>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="branding-preview mb-3">
                                        <?php
                                            // Check if there's actually a custom logo file (not just the default)
                                            $custom_logo_filename = get_option('logo_filename');
                                            $has_custom_logo = !empty($custom_logo_filename) && isset($logo_file_info) && $logo_file_info['exists'] === true;

                                            if ($has_custom_logo) {
                                                $logo = make_thumbnail($logo_file_info['dir'], LOGO_MAX_WIDTH, LOGO_MAX_HEIGHT);
                                                $img_src = ( !empty( $logo ) ) ? $logo['thumbnail']['url'] : $logo_file_info['url'];
                                                $is_custom_logo = true;
                                            } else {
                                                // Use default logo
                                                if (defined('ASSETS_IMG_URL') && defined('DEFAULT_LOGO_FILENAME')) {
                                                    $img_src = ASSETS_IMG_URL . '/' . DEFAULT_LOGO_FILENAME;
                                                } elseif (defined('ASSETS_URL')) {
                                                    $img_src = ASSETS_URL . '/img/projectsend-logo.svg';
                                                } else {
                                                    $img_src = 'assets/img/projectsend-logo.svg';
                                                }
                                                $is_custom_logo = false;
                                            }
                                        ?>
                                        <div class="preview-container logo-preview">
                                            <img src="<?php echo $img_src; ?>" alt="<?php _e('Current logo', 'cftp_admin'); ?>" class="preview-image" id="logo-preview-img">
                                        </div>
                                        <p class="preview-note text-muted small mt-2">
                                            <?php if ($is_custom_logo) { ?>
                                                <?php _e('Current custom logo', 'cftp_admin'); ?>
                                            <?php } else { ?>
                                                <?php _e('Default ProjectSend logo', 'cftp_admin'); ?>
                                            <?php } ?>
                                        </p>
                                        <div id="logo-upload-warning" class="alert alert-warning mt-2" style="display: none;">
                                            <i class="fa fa-exclamation-triangle me-1"></i>
                                            <?php _e('Remember to save your changes to upload the new logo.', 'cftp_admin'); ?>
                                        </div>
                                    </div>

                                    <div class="file-upload-container">
                                        <label for="select_logo" class="file-upload-label">
                                            <div class="file-upload-area">
                                                <i class="fa fa-cloud-upload text-primary mb-2"></i>
                                                <p class="mb-1"><?php _e('Click to upload new logo', 'cftp_admin'); ?></p>
                                                <small class="text-muted"><?php _e('JPG, PNG, GIF, SVG<br>(max 10MB)', 'cftp_admin'); ?></small>
                                            </div>
                                            <input type="file" name="select_logo" id="select_logo" class="file-upload-input" accept=".jpg,.jpeg,.jpe,.gif,.png,.svg" />
                                        </label>
                                    </div>

                                    <?php if (!empty(get_option('logo_filename'))) { ?>
                                        <div class="mt-3">
                                            <a class="btn btn-outline-danger btn-sm confirm_generic" href="<?php echo BASE_URI . 'options.php?section=branding&clear=logo'; ?>">
                                                <i class="fa fa-trash me-1"></i>
                                                <?php _e('Remove Logo', 'cftp_admin'); ?>
                                            </a>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>

                        <!-- Favicon Card -->
                        <div class="col-lg-6 mb-4">
                            <div class="card h-100 branding-card">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <?php _e('Website Favicon', 'cftp_admin'); ?>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="branding-preview mb-3">
                                        <?php if (!empty($favicon_filename)) {
                                            $favicon_path = ADMIN_UPLOADS_DIR . DS . $favicon_filename;
                                            $favicon_url = ADMIN_UPLOADS_URI . $favicon_filename;
                                            if (file_exists($favicon_path)) {
                                        ?>
                                            <div class="preview-container favicon-preview">
                                                <img src="<?php echo $favicon_url; ?>" alt="<?php _e('Current favicon', 'cftp_admin'); ?>" class="preview-image favicon-size" id="favicon-preview-img">
                                            </div>
                                            <p class="preview-note text-muted small mt-2">
                                                <?php _e('Current custom favicon', 'cftp_admin'); ?>
                                            </p>
                                        <?php } } else { ?>
                                            <div class="preview-container favicon-preview">
                                                <img src="<?php echo ASSETS_URL . '/img/favicon/favicon-32x32.png'; ?>" alt="<?php _e('Default favicon', 'cftp_admin'); ?>" class="preview-image favicon-size" id="favicon-preview-img">
                                            </div>
                                            <p class="preview-note text-muted small mt-2">
                                                <?php _e('Default ProjectSend favicon', 'cftp_admin'); ?>
                                            </p>
                                        <?php } ?>
                                        <div id="favicon-upload-warning" class="alert alert-warning mt-2" style="display: none;">
                                            <i class="fa fa-exclamation-triangle me-1"></i>
                                            <?php _e('Remember to save your changes to upload the new favicon.', 'cftp_admin'); ?>
                                        </div>
                                    </div>

                                    <div class="file-upload-container">
                                        <label for="select_favicon" class="file-upload-label">
                                            <div class="file-upload-area">
                                                <i class="fa fa-cloud-upload text-primary mb-2"></i>
                                                <p class="mb-1"><?php _e('Click to upload favicon', 'cftp_admin'); ?></p>
                                                <small class="text-muted"><?php _e('ICO, PNG, GIF, JPG, SVG<br>(1:1 format recommended)', 'cftp_admin'); ?></small>
                                            </div>
                                            <input type="file" name="select_favicon" id="select_favicon" class="file-upload-input" accept=".ico,.png,.gif,.jpg,.jpeg,.svg" />
                                        </label>
                                    </div>

                                    <?php if (!empty($favicon_filename)) { ?>
                                        <div class="mt-3">
                                            <a class="btn btn-outline-danger btn-sm confirm_generic" href="<?php echo BASE_URI . 'options.php?section=branding&clear=favicon'; ?>">
                                                <i class="fa fa-trash me-1"></i>
                                                <?php _e('Remove Favicon', 'cftp_admin'); ?>
                                            </a>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
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
