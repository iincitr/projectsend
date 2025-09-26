(function () {
    'use strict';

    admin.pages.custom_fields_form = function () {
        function initCustomFieldsForm() {
            var fieldLabel = document.getElementById('field_label');
            var fieldName = document.getElementById('field_name');
            var fieldType = document.getElementById('field_type');
            var fieldOptionsContainer = document.getElementById('field_options_container');

            // Function to create slug from text
            function createSlug(text) {
                return text.toString()
                    .toLowerCase()
                    .trim()
                    .replace(/[\s\-]+/g, '_')          // Replace spaces and hyphens with underscores
                    .replace(/[^\w_]+/g, '')           // Remove all non-word chars except underscores
                    .replace(/\_\_+/g, '_')            // Replace multiple underscores with single underscore
                    .replace(/^_+/, '')                // Trim underscores from start
                    .replace(/_+$/, '');               // Trim underscores from end
            }

            // Auto-generate field_name from field_label on blur (only for add form)
            if (fieldLabel && fieldName && !fieldName.hasAttribute('readonly')) {
                var autoGenerate = true;

                // Function to generate slug
                function generateSlug() {
                    if (autoGenerate && fieldName.value.trim() === '') {
                        var slug = createSlug(fieldLabel.value);
                        fieldName.value = slug;
                    }
                }

                // Generate on blur
                fieldLabel.addEventListener('blur', generateSlug);

                // Also generate on real-time typing if field_name is empty
                //fieldLabel.addEventListener('input', generateSlug);

                // Stop auto-generating once user manually edits field_name
                // But re-enable if field_name is cleared
                fieldName.addEventListener('input', function() {
                    if (fieldName.value.trim() === '') {
                        autoGenerate = true;
                    } else {
                        autoGenerate = false;
                    }
                });
            }

            // Show/hide field options based on field type
            if (fieldType && fieldOptionsContainer) {
                function toggleFieldOptions() {
                    var fieldOptionsHelp = document.getElementById('field_options_help');

                    if (fieldType.value === 'select' || fieldType.value === 'checkbox') {
                        fieldOptionsContainer.style.display = 'block';
                        // Make field_options required when visible (optional for checkbox)
                        var fieldOptions = document.getElementById('field_options');
                        if (fieldOptions) {
                            if (fieldType.value === 'select') {
                                fieldOptions.setAttribute('required', 'required');
                                fieldOptions.setAttribute('rows', '5');
                            } else {
                                // Checkbox label is optional (defaults to "Yes")
                                fieldOptions.removeAttribute('required');
                                fieldOptions.setAttribute('rows', '2');
                            }
                        }

                        // Update help text based on type
                        if (fieldOptionsHelp) {
                            if (fieldType.value === 'select') {
                                fieldOptionsHelp.innerHTML = 'For select fields: Enter one option per line.';
                            } else {
                                fieldOptionsHelp.innerHTML = 'For checkbox fields: Enter the checkbox label (e.g., "I agree to the terms"). Leave empty for default "Yes".';
                            }
                        }
                    } else {
                        fieldOptionsContainer.style.display = 'none';
                        // Remove required when hidden
                        var fieldOptions = document.getElementById('field_options');
                        if (fieldOptions) {
                            fieldOptions.removeAttribute('required');
                        }
                    }
                }

                // Initial check on page load
                toggleFieldOptions();

                // Toggle on field type change
                fieldType.addEventListener('change', toggleFieldOptions);
            }
        }

        // Check if DOM is already loaded or wait for it
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCustomFieldsForm);
        } else {
            initCustomFieldsForm();
        }
    };
})();