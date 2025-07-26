jQuery(document).ready(function ($) {
    // Ensure MGWPPData is available
    if (typeof MGWPPData === 'undefined') {
        console.error('MGWPPData not found. AJAX functionality may not work.');
        return;
    }

    // Create modal element if it doesn't exist
    if ($('#mgwpp-files-modal').length === 0) {
        $('body').append(`
            <div id="mgwpp-files-modal" class="mgwpp-modal" style="display:none;">
                <div class="mgwpp-modal-content">
                    <span class="mgwpp-close-modal">&times;</span>
                    <h3 class="mgwpp-modal-title">Module Files</h3>
                    <div class="mgwpp-modal-body">
                        <ul class="mgwpp-file-list"></ul>
                    </div>
                    <div class="mgwpp-modal-footer">
                        <button class="button button-primary mgwpp-close-modal">Close</button>
                    </div>
                </div>
            </div>
        `);
    }

    function showNotice(message, type = 'success') {
        let $noticeArea = $('#mgwpp-notice-area');
        
        // Create notice area if it doesn't exist
        if (!$noticeArea.length) {
            $noticeArea = $('<div id="mgwpp-notice-area"></div>');
            $('.mgwpp-modules-view').prepend($noticeArea);
        }
        
        const noticeHtml = `<div class="notice notice-${type} is-dismissible">
            <p>${message}</p>
            <button type="button" class="notice-dismiss">
                <span class="screen-reader-text">Dismiss this notice.</span>
            </button>
        </div>`;
        
        $noticeArea.html(noticeHtml);
        
        // Auto-hide success notices after 3 seconds
        if (type === 'success') {
            setTimeout(() => {
                $noticeArea.find('.notice').fadeOut();
            }, 3000);
        }
        
        // Handle dismiss button
        $noticeArea.find('.notice-dismiss').on('click', function () {
            $(this).closest('.notice').fadeOut();
        });
    }

    function toggleModuleAjax(module, status, isIndividual = true) {
        const $card = $(`.mgwpp-module-card[data-module="${module}"]`);
        const $toggle = $card.find('.mgwpp-module-toggle');
        
        // Add loading state
        $card.addClass('loading');
        
        const ajaxData = {
            action: 'toggle_module_status',
            nonce: MGWPPData.nonce,
            is_bulk: isIndividual ? 0 : 1
        };
        
        if (isIndividual) {
            ajaxData.module = module;
            ajaxData.status = status;
        } else {
            // For bulk operations, collect all enabled modules
            const enabledModules = [];
            $('.mgwpp-module-toggle:checked').each(function () {
                enabledModules.push($(this).closest('.mgwpp-module-card').data('module'));
            });
            ajaxData.modules = enabledModules;
        }

        return $.ajax({
            url: MGWPPData.ajaxurl,
            type: 'POST',
            data: ajaxData,
            dataType: 'json',
            timeout: 10000
        }).done(function (response) {
            if (response && response.success) {
                // Update UI for both individual and bulk operations
                updateAllCards(response.data.enabled_modules);
                
                // Update performance metrics
                if (response.data && response.data.metrics) {
                    $('.mgwpp-performance-metrics').html(response.data.metrics);
                }
                
                const message = isIndividual ?
                    'Module settings updated successfully!' :
                    'All settings saved successfully!';
                showNotice(message);
            } else {
                throw new Error(response.data || 'Unknown error occurred');
            }
        }).fail(function (xhr) {
            // Revert toggle state on error
            if (isIndividual) {
                $toggle.prop('checked', !status);
            }
            
            let errorMsg = MGWPPData.genericError;
            
            if (xhr.responseJSON && xhr.responseJSON.data) {
                errorMsg = xhr.responseJSON.data;
            } else if (xhr.status === 0) {
                errorMsg = 'Network error. Please check your connection.';
            } else if (xhr.status === 403) {
                errorMsg = 'Permission denied. Please refresh the page and try again.';
            }
            
            showNotice(errorMsg, 'error');
            console.error('AJAX Error:', xhr);
            
        }).always(function () {
            $card.removeClass('loading');
        });
    }

    // Update enabled modules hidden field
    function updateEnabledModulesField(enabledModules) {
        $('#mgwpp-enabled-modules').val(enabledModules.join(','));
    }

    // Update all cards based on enabled modules
    function updateAllCards(enabledModules) {
        $('.mgwpp-module-card').each(function () {
            const $card = $(this);
            const module = $card.data('module');
            const isActive = enabledModules.includes(module);
            
            // Update card active state
            $card.toggleClass('active', isActive);
            
            // Update toggle state without triggering change event
            $card.find('.mgwpp-module-toggle')
                .prop('checked', isActive);
        });
        
        // Update enabled gallery types section
        updateEnabledGalleryTypes(enabledModules);
        
        // Update hidden field
        updateEnabledModulesField(enabledModules);
    }

    function updateEnabledGalleryTypes(enabledModules) {
        const $container = $('.mgwpp-enabled-gallery-types .mgwpp-stats-grid');
        $container.empty();
        
        enabledModules.forEach(module => {
            const $card = $(`.mgwpp-module-card[data-module="${module}"]`);
            if ($card.length) {
                const iconSrc = $card.find('.module-icon img').attr('src');
                const moduleName = $card.find('.module-info h3').text();

                const badgeHtml = `
                    <div class="mgwpp-stat-card" data-module="${module}">
                        <img src="${iconSrc}" alt="${moduleName}" class="mgwpp-stat-card-icon">
                        ${moduleName}
                        <div class="mgwpp-switch">
                            <input type="checkbox" checked disabled>
                            <span class="mgwpp-switch-slider round"></span>
                        </div>
                    </div>
                `;

                $container.append(badgeHtml);
            }
        });
    }

    // Modal functions
    function showFilesModal(files) {
        const $modal = $('#mgwpp-files-modal');
        const $fileList = $modal.find('.mgwpp-file-list');
        
        $fileList.empty();
        
        if (files.length > 0) {
            files.forEach(file => {
                $fileList.append(`<li>${file}</li>`);
            });
        } else {
            $fileList.append('<li>No files found for this module</li>');
        }
        
        $modal.fadeIn();
    }
    
    function closeFilesModal() {
        $('#mgwpp-files-modal').fadeOut();
    }

    // Initialize only on submodules page
    if ($('.mgwpp-modules-view').length) {
        // Initialize toggle states from PHP data
        if (typeof MGWPPData.enabledModules !== 'undefined') {
            updateAllCards(MGWPPData.enabledModules);
        } else {
            // Fallback: Initialize from current DOM state
            const enabledModules = [];
            $('.mgwpp-module-card').each(function() {
                const $card = $(this);
                if ($card.find('.mgwpp-module-toggle').is(':checked')) {
                    enabledModules.push($card.data('module'));
                }
            });
            updateAllCards(enabledModules);
        }

        // Individual module toggle handler
        $(document).on('change', '.mgwpp-modules-view .mgwpp-module-toggle', function (e) {
            e.preventDefault();
            
            const $toggle = $(this);
            const $card = $toggle.closest('.mgwpp-module-card');
            const module = $card.data('module');
            const status = $toggle.is(':checked');

            // Validate module data
            if (!module) {
                showNotice('Invalid module data', 'error');
                $toggle.prop('checked', !status);
                return;
            }

            toggleModuleAjax(module, status, true);
        });

        // Save all settings button handler
        $(document).on('click', '#mgwpp-save-settings', function (e) {
            e.preventDefault();
            
            // Submit the form instead of using AJAX for bulk save
            $('#mgwpp-submodules-form').submit();
        });

        // Handle click events on module cards (excluding the toggle switch)
        $(document).on('click', '.mgwpp-module-card', function (e) {
            // Don't trigger if clicking on the toggle switch itself
            if ($(e.target).closest('.mgwpp-switch, .module-actions').length) {
                return;
            }
            
            const $card = $(this);
            const $toggle = $card.find('.mgwpp-module-toggle');
            const newState = !$toggle.is(':checked');
            
            // Toggle the switch
            $toggle.prop('checked', newState).trigger('change');
        });
        
        // Handle file list clicks in performance metrics
        $(document).on('click', '.module-asset-details tbody td:nth-child(3)', function() {
            const $row = $(this).closest('tr');
            const files = $row.data('files') || [];
            showFilesModal(files);
        });
        
        // Handle modal close events
        $(document).on('click', '.mgwpp-close-modal, .mgwpp-modal-footer .button', closeFilesModal);
        
        // Close modal when clicking outside content
        $(document).on('click', '#mgwpp-files-modal', function(e) {
            if ($(e.target).is('#mgwpp-files-modal')) {
                closeFilesModal();
            }
        });
    }

    // Global error handler for uncaught AJAX errors
    $(document).ajaxError(function (event, jqXHR, ajaxSettings) {
        if (ajaxSettings.url.includes('toggle_module_status')) {
            console.error('AJAX Error in module toggle:', jqXHR);
        }
    });
});