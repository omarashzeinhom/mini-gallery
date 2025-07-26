jQuery(document).ready(function ($) {
    'use strict';
    
    // Validate MGWPPData availability
    if (typeof MGWPPData === 'undefined') {
        console.error('MGWPPData not found. AJAX functionality may not work.');
        return;
    }

    // Cache DOM elements
    const $body = $('body');
    const $modulesView = $('.mgwpp-modules-view');
    
    // Only proceed if we're on the submodules page
    if (!$modulesView.length) {
        return;
    }

    // State management
    let isProcessing = false;
    let enabledModules = new Set(MGWPPData.enabledModules || []);

    // Create modal if it doesn't exist
    function initModal() {
        if ($('#mgwpp-files-modal').length === 0) {
            $body.append(`
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
    }

    // Notice system
    function showNotice(message, type = 'success') {
        let $noticeArea = $('#mgwpp-notice-area');
        
        if (!$noticeArea.length) {
            $noticeArea = $('<div id="mgwpp-notice-area"></div>');
            $modulesView.prepend($noticeArea);
        }
        
        const noticeHtml = `
            <div class="notice notice-${type} is-dismissible">
                <p>${message}</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">Dismiss this notice.</span>
                </button>
            </div>
        `;
        
        $noticeArea.html(noticeHtml);
        
        // Auto-hide success notices
        if (type === 'success') {
            setTimeout(() => $noticeArea.find('.notice').fadeOut(), 3000);
        }
    }

    // Handle notice dismiss
    $(document).on('click', '.notice-dismiss', function () {
        $(this).closest('.notice').fadeOut();
    });

    // Update UI state for all cards
    function updateAllCards(newEnabledModules) {
        enabledModules = new Set(newEnabledModules);
        
        $('.mgwpp-module-card').each(function () {
            const $card = $(this);
            const module = $card.data('module');
            const isActive = enabledModules.has(module);
            
            // Update card state
            $card.toggleClass('active', isActive);
            
            // Update toggle without triggering events
            const $toggle = $card.find('.mgwpp-module-toggle');
            $toggle.off('change.temp').prop('checked', isActive).on('change.temp', handleModuleToggle);
        });
        
        updateEnabledGalleryTypes();
    }

    // Update enabled gallery types section
    function updateEnabledGalleryTypes() {
        const $container = $('.mgwpp-enabled-gallery-types .mgwpp-stats-grid');
        $container.empty();
        
        enabledModules.forEach(module => {
            const $card = $(`.mgwpp-module-card[data-module="${module}"]`);
            if ($card.length) {
                const iconSrc = $card.find('.module-icon img').attr('src') || '';
                const moduleName = $card.find('.module-info h3').text() || module;

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

    // AJAX handler for module toggling
    function toggleModuleAjax(module = null, status = null, isIndividual = true) {
        if (isProcessing) {
            return Promise.reject(new Error('Request already in progress'));
        }

        isProcessing = true;
        let $saveButton = null;
        let saveButtonOriginalText = '';
        
        if (!isIndividual) {
            $saveButton = $('#mgwpp-save-settings');
            saveButtonOriginalText = $saveButton.text();
            $saveButton.prop('disabled', true).text('Saving...');
        }
        
        const $card = isIndividual ? $(`.mgwpp-module-card[data-module="${module}"]`) : null;
        
        // Add loading state
        if (isIndividual && $card.length) {
            $card.addClass('loading');
        }
        
        const ajaxData = {
            action: 'toggle_module_status',
            nonce: MGWPPData.nonce,
            is_bulk: isIndividual ? '0' : '1'
        };
        
        if (isIndividual) {
            ajaxData.module = module;
            ajaxData.status = status ? '1' : '0';
        } else {
            // For bulk operations, send current enabled modules
            ajaxData.modules = Array.from(enabledModules);
        }

        return $.ajax({
            url: MGWPPData.ajaxurl,
            type: 'POST',
            data: ajaxData,
            dataType: 'json',
            timeout: 15000
        }).done(function (response) {
            if (response && response.success && response.data) {
                // Update UI with new state
                updateAllCards(response.data.enabled_modules || []);
                
                // Update performance metrics if provided
                if (response.data.metrics) {
                    $('.mgwpp-performance-metrics').html(response.data.metrics);
                }
                
                const message = isIndividual ?
                    'Module settings updated successfully!' :
                    'All settings saved successfully!';
                showNotice(message, 'success');
            } else {
                throw new Error(response.data?.message || 'Unknown error occurred');
            }
        }).fail(function (xhr) {
            // Revert toggle state on error
            if (isIndividual && $card.length) {
                const $toggle = $card.find('.mgwpp-module-toggle');
                $toggle.prop('checked', !status);
            }
            
            let errorMsg = 'An error occurred. Please try again.';
            
            if (xhr.responseJSON?.data?.message) {
                errorMsg = xhr.responseJSON.data.message;
            } else if (xhr.status === 0) {
                errorMsg = 'Network error. Please check your connection.';
            } else if (xhr.status === 403) {
                errorMsg = 'Permission denied. Please refresh the page and try again.';
            } else if (xhr.status === 500) {
                errorMsg = 'Server error. Please contact support if this persists.';
            }
            
            showNotice(errorMsg, 'error');
            console.error('AJAX Error:', {
                status: xhr.status,
                response: xhr.responseJSON,
                error: xhr.statusText
            });
            
        }).always(function () {
            isProcessing = false;
            
            if (isIndividual && $card.length) {
                $card.removeClass('loading');
            } else if ($saveButton) {
                $saveButton.prop('disabled', false).text(saveButtonOriginalText);
            }
        });
    }

    // Individual module toggle handler
    function handleModuleToggle(e) {
        e.preventDefault();
        
        if (isProcessing) {
            return false;
        }
        
        const $toggle = $(this);
        const $card = $toggle.closest('.mgwpp-module-card');
        const module = $card.data('module');
        const status = $toggle.is(':checked');

        // Validate module data
        if (!module) {
            showNotice('Invalid module data', 'error');
            $toggle.prop('checked', !status);
            return false;
        }

        // Update local state immediately for better UX
        if (status) {
            enabledModules.add(module);
        } else {
            enabledModules.delete(module);
        }

        toggleModuleAjax(module, status, true).catch(() => {
            // Revert local state on error
            if (status) {
                enabledModules.delete(module);
            } else {
                enabledModules.add(module);
            }
        });
    }

    // Card click handler (excluding toggle area)
    function handleCardClick(e) {
        // Don't trigger if clicking on the toggle switch or actions
        if ($(e.target).closest('.mgwpp-switch, .module-actions').length) {
            return;
        }
        
        const $card = $(this);
        const $toggle = $card.find('.mgwpp-module-toggle');
        
        // Trigger toggle
        $toggle.prop('checked', !$toggle.is(':checked')).trigger('change');
    }

    // Save all settings handler
    function handleSaveAll(e) {
        e.preventDefault();
        
        if (isProcessing) {
            return false;
        }
        
        toggleModuleAjax(null, null, false);
    }

    // Modal functions
    function showFilesModal(files) {
        const $modal = $('#mgwpp-files-modal');
        const $fileList = $modal.find('.mgwpp-file-list');
        
        $fileList.empty();
        
        if (files && files.length > 0) {
            files.forEach(file => {
                $fileList.append(`<li>${$('<div>').text(file).html()}</li>`);
            });
        } else {
            $fileList.append('<li>No files found for this module</li>');
        }
        
        $modal.fadeIn();
    }
    
    function closeFilesModal() {
        $('#mgwpp-files-modal').fadeOut();
    }

    // File list click handler
    function handleFileListClick() {
        const $row = $(this).closest('tr');
        const filesData = $row.data('files');
        let files = [];
        
        try {
            files = typeof filesData === 'string' ? JSON.parse(filesData) : (filesData || []);
        } catch (e) {
            console.error('Error parsing files data:', e);
            files = [];
        }
        
        showFilesModal(files);
    }

    // Initialize everything
    function init() {
        initModal();
        
        // Initialize UI state
        updateAllCards(Array.from(enabledModules));
        
        // Bind event handlers
        $(document)
            .on('change', '.mgwpp-modules-view .mgwpp-module-toggle', handleModuleToggle)
            .on('click', '.mgwpp-module-card', handleCardClick)
            .on('click', '#mgwpp-save-settings', handleSaveAll)
            .on('click', '.module-asset-details tbody td:nth-child(3)', handleFileListClick)
            .on('click', '.mgwpp-close-modal, .mgwpp-modal-footer .button', closeFilesModal);
        
        // Modal backdrop click
        $(document).on('click', '#mgwpp-files-modal', function(e) {
            if ($(e.target).is('#mgwpp-files-modal')) {
                closeFilesModal();
            }
        });
        
        // Global AJAX error handler
        $(document).ajaxError(function (event, jqXHR, ajaxSettings) {
            if (ajaxSettings.url && ajaxSettings.url.includes('toggle_module_status')) {
                console.error('AJAX Error in module toggle:', {
                    status: jqXHR.status,
                    response: jqXHR.responseJSON,
                    settings: ajaxSettings
                });
            }
        });
    }

    // Start initialization
    init();
});