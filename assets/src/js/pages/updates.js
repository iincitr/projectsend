(function () {
    'use strict';

    admin.pages.updates = function () {
        function initUpdates() {
            console.log('Updates page loaded');
            console.log('json_strings available:', typeof json_strings !== 'undefined');
            if (typeof json_strings !== 'undefined') {
                console.log('Base URI:', json_strings.uri.base);
            }
            var requirementsCheck = document.getElementById('requirements-check');
            var startUpdateButton = document.getElementById('start-update');
            var updateProgressCard = document.getElementById('update-progress-card');
            var updateProgress = document.getElementById('update-progress');
            var requirementsCard = document.getElementById('requirements-card');
            var progressBar = document.querySelector('.progress-bar');
            var progressStatus = document.getElementById('progress-status');
            var updateError = document.getElementById('update-error');
            var errorMessage = document.getElementById('error-message');
            var updateSuccess = document.getElementById('update-success');
            var requirementsList = document.querySelector('.requirements-list');

            // Note: Error handling now redirects to updates.php with flash message

            var updateSteps = ['download', 'backup', 'extract', 'finalize'];
            var currentStep = 0;

            // Check system requirements on page load
            checkRequirements();

            // Handle update button click
            if (startUpdateButton) {
                startUpdateButton.addEventListener('click', startUpdate);
            }

            function checkRequirements() {
                console.log('Starting requirements check');
                console.log('URL:', json_strings.uri.base + 'process.php?do=check_update_requirements');
                fetch(json_strings.uri.base + 'process.php?do=check_update_requirements')
                    .then(response => response.json())
                    .then(data => {
                        // Hide loading indicator
                        requirementsCheck.querySelector('.text-center').style.display = 'none';
                        requirementsList.style.display = 'block';
                        requirementsList.innerHTML = '';

                        // Display requirements
                        if (data.requirements) {
                            data.requirements.forEach(function(req) {
                                var li = document.createElement('li');
                                li.className = req.status ? 'requirement-pass' : 'requirement-fail';
                                li.innerHTML = '<strong>' + req.name + '</strong>: ' + req.message;
                                requirementsList.appendChild(li);
                            });
                        }

                        // Enable update button if all requirements pass
                        if (data.can_update) {
                            startUpdateButton.disabled = false;
                            startUpdateButton.classList.remove('btn-secondary');
                            startUpdateButton.classList.add('btn-primary');
                        } else {
                            startUpdateButton.disabled = true;
                            startUpdateButton.innerHTML = '<i class="fa fa-times"></i> ' + json_strings.updates.cannot_update;
                        }
                    })
                    .catch(error => {
                        console.error('Error checking requirements:', error);
                        console.log('Full error details:', error);
                        requirementsCheck.querySelector('.text-center').style.display = 'none';
                        requirementsList.style.display = 'block';
                        requirementsList.innerHTML = '<li class="requirement-fail">' + json_strings.updates.error_checking_requirements + '</li>';
                    });
            }

            function startUpdate() {
                // Disable button
                startUpdateButton.disabled = true;

                // Hide requirements card and show progress card
                if (requirementsCard) {
                    requirementsCard.style.display = 'none';
                }
                if (updateProgressCard) {
                    updateProgressCard.style.display = 'block';
                }

                // Reset error and success states
                if (updateError) {
                    updateError.style.display = 'none';
                }
                if (updateSuccess) {
                    updateSuccess.style.display = 'none';
                }

                // Smooth scroll to top of page
                window.scrollTo({ top: 0, behavior: 'smooth' });

                // Start with first step
                currentStep = 0;
                processUpdateStep();
            }

            function processUpdateStep() {
                if (currentStep >= updateSteps.length) {
                    // Update completed
                    showSuccess();
                    return;
                }

                var step = updateSteps[currentStep];
                updateStepIndicator(step, 'active');
                updateProgressBar((currentStep / updateSteps.length) * 100);

                var stepMessages = {
                    'download': json_strings.updates.downloading_update,
                    'backup': json_strings.updates.creating_backup,
                    'extract': json_strings.updates.installing_files,
                    'finalize': json_strings.updates.finalizing_update
                };

                progressStatus.textContent = stepMessages[step] || json_strings.updates.processing;

                // Prepare request data
                var formData = new FormData();
                formData.append('step', step);
                formData.append('csrf_token', csrf_token);

                if (step === 'download' && typeof update_download_url !== 'undefined') {
                    formData.append('url', update_download_url);
                    if (typeof update_sha256_hash !== 'undefined' && update_sha256_hash) {
                        formData.append('hash', update_sha256_hash);
                    }
                }

                // Execute step
                fetch(json_strings.uri.base + 'process.php?do=perform_system_update', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status + ': ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Update step response:', data);
                    if (data.status === 'success') {
                        updateStepIndicator(step, 'complete');
                        currentStep++;

                        // Continue to next step
                        setTimeout(function() {
                            processUpdateStep();
                        }, 500);
                    } else {
                        // Error occurred
                        console.error('Update step failed:', data);
                        showError(data.message || json_strings.updates.update_failed);

                        // Attempt rollback if we're past the download step
                        if (currentStep > 0) {
                            attemptRollback();
                        }
                    }
                })
                .catch(error => {
                    console.error('Update error:', error);
                    showError(json_strings.updates.update_failed + ': ' + error.message);

                    if (currentStep > 0) {
                        attemptRollback();
                    }
                });
            }

            function attemptRollback() {
                progressStatus.textContent = json_strings.updates.rolling_back;
                updateProgressBar(0);

                var formData = new FormData();
                formData.append('step', 'rollback');
                formData.append('csrf_token', csrf_token);

                fetch(json_strings.uri.base + 'process.php?do=perform_system_update', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        errorMessage.innerHTML += '<br>' + json_strings.updates.rollback_successful;
                    } else {
                        errorMessage.innerHTML += '<br>' + json_strings.updates.rollback_failed;
                    }
                })
                .catch(error => {
                    console.error('Rollback error:', error);
                    errorMessage.innerHTML += '<br>' + json_strings.updates.rollback_failed;
                });
            }

            function updateStepIndicator(step, status) {
                var indicator = document.querySelector('.step-indicator[data-step="' + step + '"]');
                if (indicator) {
                    // Remove all status classes
                    indicator.classList.remove('step-active', 'step-complete', 'step-error');

                    // Add appropriate class
                    if (status === 'active') {
                        indicator.classList.add('step-active');
                    } else if (status === 'complete') {
                        indicator.classList.add('step-complete');
                    } else if (status === 'error') {
                        indicator.classList.add('step-error');
                    }
                }
            }

            function updateProgressBar(percent) {
                progressBar.style.width = percent + '%';
                progressBar.setAttribute('aria-valuenow', percent);
            }

            function showError(message) {
                console.error('Update error occurred:', message);

                // Redirect to updates page with error message
                var errorParam = encodeURIComponent(message);
                window.location.href = json_strings.uri.base + 'updates.php?error=' + errorParam;
            }

            function showSuccess() {
                progressBar.classList.remove('progress-bar-animated');
                progressBar.classList.add('bg-success');
                updateProgressBar(100);
                progressStatus.textContent = json_strings.updates.update_complete;
                updateSuccess.style.display = 'block';

                // Mark all steps as complete
                updateSteps.forEach(function(step) {
                    updateStepIndicator(step, 'complete');
                });

                // Reload page after 5 seconds
                setTimeout(function() {
                    window.location.reload();
                }, 5000);
            }
        }

        // Check if DOM is already loaded or wait for it
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initUpdates);
        } else {
            initUpdates();
        }
    };
})();