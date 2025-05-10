jQuery(function($) {
    $('.module-toggle').on('change', function() {
        const $checkbox = $(this);
        const module = $checkbox.data('module');
        const isActive = $checkbox.is(':checked');
        const $card = $checkbox.closest('.mgwpp-module-card');

        $card.addClass('updating');

        $.ajax({
            url: MGWPPData.ajaxurl,
            type: 'POST',
            data: {
                action: 'toggle_module_status',
                module: module,
                status: isActive ? 1 : 0,
                nonce: MGWPPData.nonce
            },
            success: function(response) {
                $card.removeClass('updating');
                if (response.success) {
                    $card.toggleClass('active inactive');

                    if (isActive) {
                        if ($('.mgwpp-gallery-type-badge[data-module="' + module + '"]').length === 0) {
                            const moduleName = module.replace('_', ' ');
                            const iconUrl = $card.find('.module-icon img').attr('src');

                            const badge = $('<div class="mgwpp-gallery-type-badge" data-module="' + module + '">' +
                                '<img src="' + iconUrl + '" alt="' + moduleName + '" class="mgwpp-gallery-type-icon" />' +
                                '<span>' + moduleName.charAt(0).toUpperCase() + moduleName.slice(1) + '</span>' +
                            '</div>');

                            $('.mgwpp-enabled-gallery-types').append(badge);
                        }
                    } else {
                        $('.mgwpp-gallery-type-badge[data-module="' + module + '"]').remove();
                    }
                } else {
                    alert('Failed to update module status');
                    $checkbox.prop('checked', !isActive);
                }
            }
        });
    });
});
