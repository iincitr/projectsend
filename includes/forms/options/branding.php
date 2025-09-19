<?php
/**
 * Branding options form configuration
 * Refactored to use array-based configuration - matches original exactly
 */

// Define the form sections and fields
$form_sections = [
    [
        'title' => __('Current logo', 'cftp_admin'),
        'description' => __('Use this page to upload your company logo, or update the currently assigned one. This image will be shown to your clients when they access their file list.', 'cftp_admin'),
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
                'name' => 'current_logo_display',
                'render_callback' => function($field) {
                    global $logo_file_info;
                    ?>
                    <div id="current_logo">
                        <div id="current_logo_img">
                            <?php
                                if ($logo_file_info['exists'] === true) {
                                    /** Make the image */
                                    $logo = make_thumbnail($logo_file_info['dir'], LOGO_MAX_WIDTH, LOGO_MAX_HEIGHT);

                                    /** If the generator failed, use the original image */
                                    $img_src = ( !empty( $logo ) ) ? $logo['thumbnail']['url'] : $logo_file_info['url'];
                                }
                                else {
                                    $img_src = ASSETS_IMG_URL . '/projectsend-logo.png';
                                }
                            ?>
                            <img src="<?php echo $img_src; ?>">
                        </div>
                        <p class="mt-3 text-info">
                            <?php _e('This preview uses a maximum width of 300px.','cftp_admin'); ?>
                        </p>
                        <?php if (!empty(get_option('logo_filename'))) { ?>
                            <div class="form-grup">
                                <a class="btn btn-pslight confirm_generic" href="<?php echo BASE_URI . 'options.php?section=branding&clear=logo'; ?>"><?php _e('Delete current logo'); ?></a>
                            </div>
                        <?php } ?>
                    </div>
                    <?php
                }
            ],
            [
                'type' => 'custom',
                'name' => 'logo_upload',
                'render_callback' => function($field) {
                    ?>
                    <div id="form_upload_logo">
                        <div class="form-group row">
                            <label class="col-sm-4 control-label"><?php _e('Select image to upload','cftp_admin'); ?></label>
                            <div class="col-sm-8">
                                <input type="file" name="select_logo" class="empty" accept=".jpg, .jpeg, .jpe, .gif, .png, .svg" />
                            </div>
                        </div>
                    </div>
                    <?php
                }
            ]
        ]
    ],
    [
        'title' => __('Favicon', 'cftp_admin'),
        'description' => __('Upload a favicon image (16x16 or 32x32 pixels recommended). Supported formats: .ico, .png, .gif, .jpg', 'cftp_admin'),
        'fields' => [
            [
                'type' => 'custom',
                'name' => 'current_favicon_display',
                'render_callback' => function($field) {
                    $favicon_filename = get_option('favicon_filename');
                    ?>
                    <div id="current_favicon">
                        <?php if (!empty($favicon_filename)) {
                            $favicon_path = ADMIN_UPLOADS_DIR . DS . $favicon_filename;
                            $favicon_url = ADMIN_UPLOADS_URI . $favicon_filename;
                            if (file_exists($favicon_path)) {
                        ?>
                            <div class="form-group row">
                                <label class="col-sm-4 control-label"><?php _e('Current favicon','cftp_admin'); ?></label>
                                <div class="col-sm-8">
                                    <div id="current_favicon_img">
                                        <img src="<?php echo $favicon_url; ?>" style="max-width: 32px; max-height: 32px;">
                                    </div>
                                    <div class="mt-2">
                                        <a class="btn btn-pslight confirm_generic" href="<?php echo BASE_URI . 'options.php?section=branding&clear=favicon'; ?>"><?php _e('Delete current favicon'); ?></a>
                                    </div>
                                </div>
                            </div>
                        <?php } } else { ?>
                            <div class="form-group row">
                                <div class="col-sm-8 offset-sm-4">
                                    <p class="text-muted"><?php _e('No favicon currently set','cftp_admin'); ?></p>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <?php
                }
            ],
            [
                'type' => 'custom',
                'name' => 'favicon_upload',
                'render_callback' => function($field) {
                    ?>
                    <div id="form_upload_favicon">
                        <div class="form-group row">
                            <label class="col-sm-4 control-label"><?php _e('Select favicon to upload','cftp_admin'); ?></label>
                            <div class="col-sm-8">
                                <input type="file" name="select_favicon" class="empty" accept=".ico, .png, .gif, .jpg, .jpeg" />
                                <p class="field_note form-text"><?php _e('For best results, use a square image (16x16, 32x32, or 64x64 pixels).','cftp_admin'); ?></p>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            ]
        ],
        'divider' => false // No divider at the end
    ]
];

// Render the form sections
render_options_form_sections($form_sections);
