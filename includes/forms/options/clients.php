<?php
/**
 * Clients options form configuration
 * Refactored to use array-based configuration - matches original exactly
 */

// Define the form sections and fields
$form_sections = [
    [
        'title' => __('New registrations', 'cftp_admin'),
        'description' => __('Used only on self-registrations. These options will not apply to clients registered by system administrators.', 'cftp_admin'),
        'fields' => [
            [
                'type' => 'checkbox',
                'name' => 'clients_can_register',
                'label' => __('Clients can register themselves', 'cftp_admin')
            ],
            [
                'type' => 'checkbox',
                'name' => 'clients_auto_approve',
                'label' => __('Auto approve new accounts', 'cftp_admin')
            ],
            [
                'type' => 'custom',
                'name' => 'clients_auto_group',
                'render_callback' => function($field) {
                    ?>
                    <div class="form-group row">
                        <label for="clients_auto_group" class="col-sm-4 control-label"><?php _e('Add clients to this group:','cftp_admin'); ?></label>
                        <div class="col-sm-8">
                            <select class="form-select" name="clients_auto_group" id="clients_auto_group" required>
                                <option value="0"><?php _e('None (does not enable this feature)','cftp_admin'); ?></option>
                                <?php
                                    /** Fill the groups array that will be used on the form */
                                    $groups = get_groups([]);

                                    foreach ( $groups as $group ) {
                                ?>
                                        <option value="<?php echo filter_var($group["id"], FILTER_VALIDATE_INT); ?>"
                                            <?php
                                                if (get_option('clients_auto_group') == $group["id"]) {
                                                    echo 'selected="selected"';
                                                }
                                            ?>
                                            ><?php echo html_output($group["name"]); ?>
                                        </option>
                                <?php
                                    }
                                ?>
                            </select>
                            <p class="field_note form-text"><?php _e('New clients will automatically be assigned to the group you have selected.','cftp_admin'); ?></p>
                        </div>
                    </div>
                    <?php
                }
            ],
            [
                'type' => 'select',
                'name' => 'clients_can_select_group',
                'label' => __('Groups for which clients can request membership to:', 'cftp_admin'),
                'options' => [
                    'none' => __("None", 'cftp_admin'),
                    'public' => __("Public groups", 'cftp_admin'),
                    'all' => __("All groups", 'cftp_admin')
                ],
                'required' => true,
                'note' => __('When a client registers a new account, an option will be presented to request becoming a member of a particular group.', 'cftp_admin')
            ]
        ]
    ],

    [
        'title' => __('Files', 'cftp_admin'),
        'fields' => [
            [
                'type' => 'checkbox',
                'name' => 'clients_can_upload',
                'label' => __('Clients can upload files', 'cftp_admin')
            ],
            [
                'type' => 'checkbox',
                'name' => 'clients_can_delete_own_files',
                'label' => __('Clients can delete their own uploaded files', 'cftp_admin')
            ],
            [
                'type' => 'checkbox',
                'name' => 'clients_can_set_expiration_date',
                'label' => __('Clients can set expiration Date', 'cftp_admin')
            ],
            [
                'type' => 'checkbox',
                'name' => 'clients_can_set_categories',
                'label' => __('Clients can asssign categories to files', 'cftp_admin'),
                'note' => __("IMPORTANT: This will make all categories visible to all users.", 'cftp_admin')
            ],
            [
                'type' => 'select',
                'name' => 'clients_can_set_public',
                'label' => __('Clients can set own files as public:', 'cftp_admin'),
                'options' => [
                    'none' => __("None", 'cftp_admin'),
                    'allowed' => __("Allowed ones", 'cftp_admin'),
                    'all' => __("All clients", 'cftp_admin')
                ],
                'required' => true,
                'note' => __("If selecting 'allowed ones': please edit each allowed client and set the corresponding permissions.", 'cftp_admin')
            ],
            [
                'type' => 'checkbox',
                'name' => 'clients_new_default_can_set_public',
                'label' => __('New self registered clients can upload public files by default.', 'cftp_admin'),
                'note' => __("Specific option when selecting 'allowed ones' in the previous option.", 'cftp_admin')
            ],
            [
                'type' => 'checkbox',
                'name' => 'clients_can_upload_to_public_folders',
                'label' => __('Allow clients to assign files to public folders', 'cftp_admin'),
                'note' => __("Any file assigned to a public folder automatically becomes public too.", 'cftp_admin') . '<br>' . __('Important: inherits permission from the setting "Clients can set own files as public".', 'cftp_admin')
            ],
            [
                'type' => 'select',
                'name' => 'expired_files_hide',
                'label' => __('When a file expires:', 'cftp_admin'),
                'options' => [
                    '1' => __("Don't show it on the files list", 'cftp_admin'),
                    '0' => __("Show it anyway, but prevent download.", 'cftp_admin')
                ],
                'required' => true,
                'default' => '1',
                'note' => __('This only affects clients. On the admin side, you can still get the files.', 'cftp_admin')
            ],
            [
                'type' => 'checkbox',
                'name' => 'clients_files_list_include_public',
                'label' => __("Show public files and folders on client's files lists", 'cftp_admin'),
                'note' => __("When a client logs in, all public files will also be shown using the selected template, next to the files assigned to their account.", 'cftp_admin')
            ]
        ],
        'divider' => false // No divider at the end
    ]
];

// Render the form sections
render_options_form_sections($form_sections);
