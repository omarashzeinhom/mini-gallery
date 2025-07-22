jQuery(document).ready(function ($) {
    // Ensure MGWPPData is available
    if (typeof MGWPPData === 'undefined') {
        console.error('MGWPPData not found. AJAX functionality may not work.');
        return;
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
        $noticeArea.find('.notice-dismiss').on('click', function() {
            $(this).closest('.notice').fadeOut();
        });
    }

    function toggleModuleAjax(module, status, isIndividual = true) {
        const $card = $(`.mgwpp-module-card[data-module="${module}"]`);
        const $toggle = $card.find('.mgwpp-module-toggle');
        
        //  loading state
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
            timeout: 10000 // 10 second timeout
        }).done(function (response) {
            if (response.success) {
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

    // Update all cards based on enabled modules
    function updateAllCards(enabledModules) {
        $('.mgwpp-module-card').each(function() {
            const $card = $(this);
            const module = $card.data('module');
            const isActive = enabledModules.includes(module);
            
            // Update card active state
            $card.toggleClass('active', isActive);
            
            // Update toggle state without triggering change event
            $card.find('.mgwpp-module-toggle')
                .prop('checked', isActive)
                .data('prev-state', isActive);
        });
        
        // Update enabled gallery types section
        updateEnabledGalleryTypes(enabledModules);
    }

    function updateEnabledGalleryTypes(enabledModules) {
        const $container = $('.mgwpp-enabled-gallery-types .mgwpp-stats-grid');
        $container.empty();
        
        enabledModules.forEach(module => {
            const $card = $(`.mgwpp-module-card[data-module="${module}"]`);
            if ($card.length) {
                const iconSrc = $card.find('.module-icon img').attr('src');
                const moduleName = $card.find('h3').text();

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

    // Initialize only on submodules page
    if ($('.mgwpp-modules-view').length) {
        // Initialize toggle states
        const enabledModules = JSON.parse('<?php echo json_encode(get_option("mgwpp_enabled_sub_modules", array_keys($this->sub_modules)) ?>');
        updateAllCards(enabledModules);

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
            
            const $button = $(this);
            const originalText = $button.text();

            // Prevent multiple clicks
            if ($button.prop('disabled')) {
                return;
            }

            $button.text('Saving...').prop('disabled', true);

            // Use a small delay to show the "Saving..." state
            setTimeout(() => {
                toggleModuleAjax(null, null, false).always(() => {
                    $button.text(originalText).prop('disabled', false);
                });
            }, 100);
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
    }

    // Global error handler for uncaught AJAX errors
    $(document).ajaxError(function(event, jqXHR, ajaxSettings) {
        if (ajaxSettings.url.includes('toggle_module_status')) {
            console.error('AJAX Error in module toggle:', jqXHR);
        }
    });
});