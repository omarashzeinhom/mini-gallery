jQuery(document).ready(function ($) {
    function showNotice(message, type = 'success') {
        $('#mgwpp-notice-area').html(
            `<div class="notice notice-${type} is-dismissible"><p>${message}</p></div>`
        );
    }

    $('.mgwpp-modules-view').on('change', '.mgwpp-module-toggle', function () {
        const $toggle = $(this);
        const $card = $toggle.closest('.mgwpp-module-card');
        const module = $card.data('module');
        const status = $toggle.is(':checked');

        $card.addClass('loading');

        $.ajax({
            url: MGWPPData.ajaxurl,
            type: 'POST',
            data: {
                action: 'toggle_module_status',
                module: module,
                status: status,
                nonce: MGWPPData.nonce,
                // Add bulk operation flag
                is_bulk: 0
            },
            success: function (response) {
                if (response.success) {
                    $card.toggleClass('active', status);
                    updateEnabledGalleryTypes(module, status);

                    if (response.data && response.data.metrics) {
                        $('.mgwpp-performance-metrics').html(response.data.metrics);
                    }

                    showNotice('Settings saved successfully!');
                }
            },
            error: function (xhr) {
                $toggle.prop('checked', !status);
                const errorMsg = xhr.responseJSON && xhr.responseJSON.data ?
                    xhr.responseJSON.data : MGWPPData.genericError;
                showNotice(errorMsg, 'error');
            },
            complete: function () {
                $card.removeClass('loading');
            }
        });
    });

    // Add save button handler
    $('#mgwpp-save-settings').on('click', function () {
        const $button = $(this);
        const originalText = $button.text();

        $button.text('Saving...').prop('disabled', true);

        // Collect all enabled modules
        const enabledModules = [];
        $('.mgwpp-module-toggle:checked').each(function () {
            enabledModules.push($(this).closest('.mgwpp-module-card').data('module'));
        });

        $.ajax({
            url: MGWPPData.ajaxurl,
            type: 'POST',
            data: {
                action: 'toggle_module_status',
                modules: enabledModules,
                nonce: MGWPPData.nonce,
                // Add bulk operation flag
                is_bulk: 1
            },
            success: function (response) {
                if (response.success) {
                    showNotice('All settings saved successfully!');
                    if (response.data && response.data.metrics) {
                        $('.mgwpp-performance-metrics').html(response.data.metrics);
                    }
                }
            },
            error: function (xhr) {
                const errorMsg = xhr.responseJSON && xhr.responseJSON.data ?
                    xhr.responseJSON.data : MGWPPData.genericError;
                showNotice(errorMsg, 'error');
            },
            complete: function () {
                $button.text(originalText).prop('disabled', false);
            }
        });
    });
    // Initialize toggle states - only on submodules page
    if ($('.mgwpp-modules-view').length) {
        $('.mgwpp-module-card').each(function () {
            const $card = $(this);
            const isActive = $card.find('.mgwpp-module-toggle').is(':checked');
            $card.toggleClass('active', isActive);
        });

        // Handle toggle changes - namespaced to submodules
        $('.mgwpp-modules-view').on('change', '.mgwpp-module-toggle', function () {
            const $toggle = $(this);
            const $card = $toggle.closest('.mgwpp-module-card');
            const module = $card.data('module');
            const status = $toggle.is(':checked');

            // Add loading state
            $card.addClass('loading');

            // Send AJAX request
            $.ajax({
                url: MGWPPData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'toggle_module_status',
                    module: module,
                    status: status,
                    nonce: MGWPPData.nonce
                },
                success: function (response) {
                    if (response.success) {
                        // Update UI
                        $card.toggleClass('active', status);

                        // Update enabled gallery types
                        updateEnabledGalleryTypes(module, status);

                        // Update performance metrics with new HTML
                        if (response.data && response.data.metrics) {
                            $('.mgwpp-performance-metrics').html(response.data.metrics);
                        }
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', error);
                    // Revert on error
                    $toggle.prop('checked', !status);
                    alert(MGWPPData.genericError);
                },
                complete: function () {
                    $card.removeClass('loading');
                }
            });
        });

        // Update enabled gallery types display
        function updateEnabledGalleryTypes(module, status) {
            const $container = $('.mgwpp-enabled-gallery-types .mgwpp-stats-grid');
            const $badge = $container.find(`.mgwpp-stat-card[data-module="${module}"]`);

            if (status) {
                // Add badge if it doesn't exist
                if (!$badge.length) {
                    const $card = $(`.mgwpp-module-card[data-module="${module}"]`);
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
            } else {
                // Remove badge if it exists
                if ($badge.length) {
                    $badge.remove();
                }
            }
        }
    }
});