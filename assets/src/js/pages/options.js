(function () {
    'use strict';

    admin.pages.options = function () {
        var tagifyContainer = document.getElementById('allowed_file_types');
        var tagify = new Tagify (tagifyContainer);
        //tagifyContainer.addEventListener('change', tagifyOnChange)

        function tagifyOnChange(e){
            console.log(e.target.value)
        }

        $(document).ready(function(){
            var validator = $("#options").validate({
                errorPlacement: function(error, element) {
                    error.appendTo(element.parent('div'));
                },
            });

            $('#download_method').on('change', function(e) {
                var method = $(this).find('option:selected').val();
                $('.method_note').hide();
                $('.method_note[data-method="'+method+'"]').show();
            });

            $('#download_method').trigger('change');
        });

        const captchaMethodSelect = document.getElementById('captcha_method');
        const captchaOptionsBlocks = document.querySelectorAll('.captcha_options_block');
        if (elementExists(captchaMethodSelect)) {
            captchaMethodSelect.addEventListener('change', function(e) {
                const showOptionsBlock = document.getElementById('captcha_' + e.target.value)
                console.log(showOptionsBlock);
                for (let i = 0; i < captchaOptionsBlocks.length; i++) {
                    const captchaOptionsBlock = captchaOptionsBlocks[i];
                    captchaOptionsBlock.classList.add('d-none');
                }

                if (elementExists(showOptionsBlock)) {
                    showOptionsBlock.classList.remove('d-none');
                }
            });
        }

        // Mail system options visibility control
        const mailSystemSelect = document.getElementById('mail_system_use');
        if (elementExists(mailSystemSelect)) {
            function toggleMailFields() {
                const selectedValue = mailSystemSelect.value;
                const authFields = document.querySelectorAll('.mail-auth-field');
                const smtpFields = document.querySelectorAll('.mail-smtp-field');

                // Find SMTP section header
                const h3Elements = document.querySelectorAll('h3');
                let smtpSection = null;
                h3Elements.forEach(h3 => {
                    if (h3.textContent.includes('SMTP options')) {
                        smtpSection = h3;
                    }
                });

                // Hide all conditional fields initially
                authFields.forEach(field => field.style.display = 'none');
                smtpFields.forEach(field => field.style.display = 'none');

                // Hide SMTP section header
                if (smtpSection) {
                    smtpSection.style.display = 'none';
                }

                // Show username/password fields for SMTP and Gmail
                if (selectedValue === 'smtp' || selectedValue === 'gmail') {
                    authFields.forEach(field => field.style.display = '');
                }

                // Show SMTP-specific fields and section only for SMTP
                if (selectedValue === 'smtp') {
                    smtpFields.forEach(field => field.style.display = '');
                    if (smtpSection) {
                        smtpSection.style.display = '';
                    }
                }
            }

            // Set initial state
            toggleMailFields();

            // Listen for changes
            mailSystemSelect.addEventListener('change', toggleMailFields);
        }
    };
})();