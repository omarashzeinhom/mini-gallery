jQuery(function ($) {
    // Initialize variables
    var mediaUploader;
    var $imageContainer = $('.mgwpp-image-container');

    // SINGLE sortable initialization - remove duplicates
    $imageContainer.sortable({
        placeholder: 'mgwpp-image-item ui-sortable-placeholder',
        opacity: 0.6,
        revert: 200,
        cursor: 'move',
        start: function (event, ui) {
            // Hide remove buttons during drag
            $('.mgwpp-remove-image').hide();
            ui.placeholder.height(ui.item.height());
        },
        stop: function (event, ui) {
            // Show remove buttons after drag
            $('.mgwpp-remove-image').show();

            // Update hidden input order to match visual order
            updateHiddenInputOrder();

            // Visual feedback that order changed
            $('#mgwpp-save-order-btn').css('background-color', '#d54e21');
        }
    });

    // Function to update hidden input order
    function updateHiddenInputOrder() {
        $imageContainer.find('.mgwpp-image-item').each(function (index) {
            var $item = $(this);
            var imageId = $item.data('id');

            // Update or create hidden input with correct order
            var $hiddenInput = $item.find('input[name="gallery_images[]"]');
            if ($hiddenInput.length === 0) {
                $hiddenInput = $('<input type="hidden" name="gallery_images[]">');
                $item.append($hiddenInput);
            }
            $hiddenInput.val(imageId);
        });
    }

    // Add Images button
    $('.mgwpp-add-images').on('click', function (e) {
        e.preventDefault();

        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        mediaUploader = wp.media({
            title: 'Select Images for Gallery',
            button: {
                text: 'Add to Gallery'
            },
            multiple: true,
            library: {
                type: 'image'
            }
        });

        mediaUploader.on('select', function () {
            var attachments = mediaUploader.state().get('selection').toJSON();
            var $noImages = $('.mgwpp-no-images');

            // Remove "no images" message if present
            if ($noImages.length) {
                $noImages.remove();
            }

            attachments.forEach(function (attachment) {
                // Check if image already exists
                if ($imageContainer.find('[data-id="' + attachment.id + '"]').length === 0) {
                    var imageItem = $(
                        '<div class="mgwpp-image-item" data-id="' + attachment.id + '">' +
                        '<img src="' + attachment.sizes.thumbnail.url + '" alt="">' +
                        '<input type="hidden" name="gallery_images[]" value="' + attachment.id + '">' +
                        '<button type="button" class="mgwpp-remove-image" title="Remove image">×</button>' +
                        '</div>'
                    );

                    $imageContainer.append(imageItem);
                }
            });
        });

        mediaUploader.open();
    });

    // Remove Image button
    $imageContainer.on('click', '.mgwpp-remove-image', function (e) {
        e.preventDefault();
        $(this).closest('.mgwpp-image-item').fadeOut(300, function () {
            $(this).remove();

            // Check if container is empty
            if ($imageContainer.find('.mgwpp-image-item').length === 0) {
                $imageContainer.append('<p class="mgwpp-no-images">No images added to this gallery yet.</p>');
            }
        });
    });

    // Show/hide remove buttons on hover
    $imageContainer.on('mouseenter', '.mgwpp-image-item', function () {
        $(this).find('.mgwpp-remove-image').show();
    }).on('mouseleave', '.mgwpp-image-item', function () {
        if (!$(this).is('.ui-sortable-helper')) {
            $(this).find('.mgwpp-remove-image').hide();
        }
    });

    // Save Gallery Order - AJAX handler
    $('#mgwpp-save-order-btn').on('click', function (e) {
        e.preventDefault();

        const $btn = $(this);
        const originalText = $btn.text();
        $btn.text(mgwppEdit.i18n.saving).prop('disabled', true);

        // Get ordered image IDs from data attributes (more reliable)
        const imageIds = [];
        $imageContainer.find('.mgwpp-image-item').each(function () {
            const imageId = $(this).data('id');
            if (imageId) {
                imageIds.push(parseInt(imageId));
            }
        });

        // Debug log
        console.log('Saving order:', imageIds);

        // AJAX request
        $.ajax({
            url: mgwppEdit.ajaxUrl,
            type: 'POST',
            data: {
                action: 'mgwpp_save_gallery_order',
                gallery_id: $('input[name="gallery_id"]').val(),
                image_ids: imageIds,
                nonce: mgwppEdit.nonce
            },
            success: function (response) {
                console.log('Save response:', response);

                if (response.success) {
                    $btn.text(mgwppEdit.i18n.saved).css('background-color', '#46b450');

                    // Update hidden inputs to match saved order
                    updateHiddenInputOrder();
                    // REFRESH PREVIEW IFRAME
                    refreshPreviewIframe();
                    setTimeout(() => {
                        $btn.text(originalText).prop('disabled', false).css('background-color', '');
                    }, 2000);
                } else {
                    alert('Error: ' + (response.data.message || 'Unknown error'));
                    $btn.text(originalText).prop('disabled', false).css('background-color', '');
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX error:', xhr.responseText);
                alert(mgwppEdit.i18n.saveFailed + ': ' + error);
                $btn.text(originalText).prop('disabled', false).css('background-color', '');
            }

        });
    });
    function refreshPreviewIframe() {
        const $previewFrame = $('#mgwpp-preview-frame');
        if ($previewFrame.length) {
            // Get current src
            let currentSrc = $previewFrame.attr('src');

            // Remove existing timestamp if present
            currentSrc = currentSrc.replace(/[?&]t=\d+/, '');

            // Add new timestamp to bypass cache
            const separator = currentSrc.includes('?') ? '&' : '?';
            const newSrc = currentSrc + separator + 't=' + Date.now();

            // Refresh iframe
            $previewFrame.attr('src', newSrc);
        }
    }

    // Initialize color pickers if they exist
    if ($.fn.wpColorPicker) {
        $('.mgwpp-color-field').wpColorPicker();
    }
});