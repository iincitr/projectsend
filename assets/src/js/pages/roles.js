(function () {
    'use strict';

    admin.pages.roles = function () {

        document.addEventListener('DOMContentLoaded', function() {
            // Handle role deletion
            document.querySelectorAll('.delete-role').forEach(function(button) {
                button.addEventListener('click', function() {
                    const roleId = this.dataset.role;
                    const roleName = this.dataset.name;

                    if (confirm('Are you sure you want to delete the role "' + roleName + '"?\n\nThis action cannot be undone.')) {
                        // Create form and submit
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = 'process.php';

                        // Get CSRF token from any existing form on the page
                        const existingCsrfInput = document.querySelector('input[name="csrf_token"]');
                        const csrfValue = existingCsrfInput ? existingCsrfInput.value : '';

                        form.innerHTML = `
                            <input type="hidden" name="do" value="delete_role">
                            <input type="hidden" name="role_id" value="${roleId}">
                            <input type="hidden" name="csrf_token" value="${csrfValue}">
                        `;

                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });
    };
})();