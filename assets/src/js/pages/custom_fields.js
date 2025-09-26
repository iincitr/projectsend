(function () {
    'use strict';

    admin.pages.customFields = function () {
        document.addEventListener('DOMContentLoaded', function() {
            // Handle delete confirmations
            const deleteButtons = document.querySelectorAll('.delete-confirm');

            deleteButtons.forEach(function(button) {
                button.addEventListener('click', function(e) {
                    e.preventDefault();

                    const confirmMessage = projectSendVars.strings.confirmDelete || 'Are you sure you want to delete this custom field? All associated data will be lost.';

                    if (confirm(confirmMessage)) {
                        window.location.href = this.href;
                    }
                });
            });

            // Initialize data tables if the table exists
            const customFieldsTable = document.getElementById('custom_fields_tbl');
            if (customFieldsTable) {
                // If the table library is available, initialize it
                if (typeof $.fn.footable !== 'undefined') {
                    $(customFieldsTable).footable({
                        sorting: {
                            enabled: true
                        },
                        paging: {
                            enabled: true,
                            size: 10
                        }
                    });
                }
            }
        });
    };
})();