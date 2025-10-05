(function () {
    'use strict';

    admin.pages.fileEditor = function () {
        var form = document.getElementById('files');
        var formChanged = false;
        var formSubmitting = false;
        var initialFormData = form ? new FormData(form) : null;

        $(document).ready(function(){
            // Datepicker
            if ( $.isFunction($.fn.datepicker) ) {
                $('.date-container .date-field').datepicker({
                    format : 'dd-mm-yyyy',
                    autoclose : true,
                    todayHighlight : true
                });
            }

            // Validation
            var validator = $("#files").validate({
                errorPlacement: function(error, element) {
                    error.appendTo(element.parent('div'));
                }
            });

            var file = $('input[name^="file"]');

            file.filter('input[name$="[name]"]').each(function() {
                $(this).rules("add", {
                    required: true,
                    messages: {
                        required: json_strings.validation.no_name
                    }
                });
            });

            // Copy settings to other files
            function copySettingsToCheckboxes(el, to, question)
            {
                if ( confirm( question ) ) {
                    $(to).each(function(i, obj) {
                        var from_element = document.getElementById($(el).data('copy-from'));
                        $(this).prop('checked', from_element.checked);
                    });
                }
            }

            $('.copy-expiration-settings').on('click', function() {
                copySettingsToCheckboxes($(this), '.checkbox_setting_expires', json_strings.translations.upload_form.copy_expiration);
                // Copy date
                var element = $('#'+$(this).data('copy-date-from'));
                var date = element.val();
                $('.date-field').each(function(i, obj) {
                    console.log(date);
                    $('.date-field').datepicker('update', date);
                });
            });

            $('.copy-public-settings').on('click', function() {
                copySettingsToCheckboxes($(this), '.checkbox_setting_public', json_strings.translations.upload_form.copy_public);
            });

            $('.copy-hidden-settings').on('click', function() {
                copySettingsToCheckboxes($(this), '.checkbox_setting_hidden', json_strings.translations.upload_form.copy_hidden);
            });

            // Download limit settings toggle
            $('.checkbox_download_limit_enabled').on('change', function() {
                var settings = $(this).closest('.file_data').find('.download_limit_settings');
                if ($(this).is(':checked')) {
                    settings.slideDown();
                } else {
                    settings.slideUp();
                }
            });

            // Copy download limit settings
            $('.copy-download-limit-settings').on('click', function() {
                if (confirm(json_strings.translations.upload_form.copy_download_limits || 'Apply these download limit settings to all files?')) {
                    var from_element = document.getElementById($(this).data('copy-from'));
                    var from_wrapper = $(from_element).closest('.file_data');

                    // Copy enabled checkbox state
                    $('.checkbox_download_limit_enabled').each(function(i, obj) {
                        $(this).prop('checked', from_element.checked);
                        // Show/hide settings based on checkbox
                        var settings = $(this).closest('.file_data').find('.download_limit_settings');
                        if (from_element.checked) {
                            settings.show();
                        } else {
                            settings.hide();
                        }
                    });

                    // Copy limit type
                    var from_type = from_wrapper.find('input[type="radio"]:checked').val();
                    $('input[name$="[download_limit_type]"]').each(function() {
                        if ($(this).val() === from_type) {
                            $(this).prop('checked', true);
                        }
                    });

                    // Copy limit count
                    var from_count = from_wrapper.find('input[type="number"]').val();
                    $('input[name$="[download_limit_count]"]').val(from_count);
                }
            });

            // Collapse - expand single item
            $('.toggle_file_editor').on('click', function(e) {
                let wrapper = $(this).parents('.file_editor_wrapper');
                wrapper.toggleClass('collapsed');
            });

            // Collapse all
            document.getElementById('files_collapse_all').addEventListener('click', function(e) {
                let wrappers = document.querySelectorAll('.file_editor_wrapper');
                wrappers.forEach(wrapper => {
                    wrapper.classList.add('collapsed');
                });
                    
            })

            // Expand all
            document.getElementById('files_expand_all').addEventListener('click', function(e) {
                let wrappers = document.querySelectorAll('.file_editor_wrapper');
                wrappers.forEach(wrapper => {
                    wrapper.classList.remove('collapsed');
                });
                    
            })
        });

        // Track form changes for unsaved changes warning
        if (form) {
            // Track changes to all form inputs
            form.addEventListener('change', function() {
                formChanged = true;
            });

            // Also track text input changes
            var textInputs = form.querySelectorAll('input[type="text"], input[type="email"], input[type="password"], input[type="number"], input[type="checkbox"], textarea, select');
            textInputs.forEach(function(input) {
                input.addEventListener('input', function() {
                    // Check if form has actually changed from initial state
                    var currentFormData = new FormData(form);
                    formChanged = !areFormDataEqual(initialFormData, currentFormData);
                });
            });

            // Set flag when form is being submitted
            form.addEventListener('submit', function() {
                formSubmitting = true;
            });

            // Helper function to compare FormData objects
            function areFormDataEqual(formData1, formData2) {
                if (!formData1 || !formData2) return false;

                var entries1 = Array.from(formData1.entries());
                var entries2 = Array.from(formData2.entries());

                if (entries1.length !== entries2.length) {
                    return false;
                }

                for (var i = 0; i < entries1.length; i++) {
                    if (entries1[i][0] !== entries2[i][0] || entries1[i][1] !== entries2[i][1]) {
                        return false;
                    }
                }

                return true;
            }

            // Warn user before leaving if there are unsaved changes
            window.addEventListener('beforeunload', function(e) {
                if (formChanged && !formSubmitting) {
                    var confirmationMessage = 'You have unsaved changes. Are you sure you want to leave this page?';
                    e.returnValue = confirmationMessage;
                    return confirmationMessage;
                }
            });

            // Handle navigation away from the page (for links within the application)
            document.addEventListener('click', function(e) {
                // Check if it's a link that would navigate away
                var target = e.target.closest('a');
                if (target && target.href && !target.href.startsWith('#') && !target.classList.contains('delete-confirm')) {
                    if (formChanged && !formSubmitting) {
                        e.preventDefault();
                        var targetUrl = target.href;

                        Swal.fire({
                            title: 'Unsaved Changes',
                            text: 'You have unsaved changes. Are you sure you want to leave this page?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Yes, leave page',
                            cancelButtonText: 'Stay on page',
                            showClass: {
                                popup: 'animate__animated animate__fadeIn'
                            },
                            hideClass: {
                                popup: 'animate__animated animate__fadeOut'
                            }
                        }).then(function(result) {
                            if (result.isConfirmed) {
                                formSubmitting = true; // Prevent the beforeunload warning
                                window.location.href = targetUrl;
                            }
                        });
                    }
                }
            });
        }
    };
})();