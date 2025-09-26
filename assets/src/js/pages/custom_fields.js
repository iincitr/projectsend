(function () {
    'use strict';

    admin.pages.custom_fields = function () {
        function initCustomFields() {
            // Handle delete confirmations
            var deleteButtons = document.querySelectorAll('.delete-confirm');

            deleteButtons.forEach(function(button) {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    var deleteUrl = this.getAttribute('href');

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This will permanently delete this custom field and all its data. This action cannot be undone!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel',
                        showClass: {
                            popup: 'animate__animated animate__fadeIn'
                        },
                        hideClass: {
                            popup: 'animate__animated animate__fadeOut'
                        }
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            window.location.href = deleteUrl;
                        }
                    });
                });
            });
        }

        // Check if DOM is already loaded or wait for it
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCustomFields);
        } else {
            initCustomFields();
        }
    };
})();