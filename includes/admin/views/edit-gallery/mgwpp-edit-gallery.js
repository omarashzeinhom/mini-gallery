jQuery(document).ready(function ($) {
    // Initialize variables
    var mediaUploader;
    var isReordering = false;
    var $imageContainer = $('.mgwpp-image-container');

    // Make images sortable
    $imageContainer.sortable({
        placeholder: 'mgwpp-image-item ui-sortable-placeholder',
        opacity: 0.6,
        revert: 200,
        start: function () {
            isReordering = true;
            $('.mgwpp-remove-image').hide();
        },
        stop: function () {
            isReordering = false;
        }
    });

    // Add Images button
    $('.mgwpp-add-images').on('click', function (e) {
        e.preventDefault();

        // If the uploader object has already been created, reopen the dialog
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        // Extend the wp.media object
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Select Images for Gallery',
            button: {
                text: 'Add to Gallery'
            },
            multiple: true,
            library: {
                type: 'image'
            }
        });

        // When a file is selected, grab the IDs and add them to the container
        mediaUploader.on('select', function () {
            var attachments = mediaUploader.state().get('selection').toJSON();

            attachments.forEach(function (attachment) {
                // Check if image already exists
                if ($imageContainer.find('[data-id="' + attachment.id + '"]').length === 0) {
                    var imageItem = $(
                        '<div class="mgwpp-image-item" data-id="' + attachment.id + '">' +
                        '<img src="' + attachment.sizes.thumbnail.url + '">' +
                        '<input type="hidden" name="gallery_images[]" value="' + attachment.id + '">' +
                        '<button type="button" class="mgwpp-remove-image">×</button>' +
                        '</div>'
                    );

                    $imageContainer.append(imageItem);
                }
            });
        });

        // Open the uploader dialog
        mediaUploader.open();
    });

    // Remove Image button
    $imageContainer.on('click', '.mgwpp-remove-image', function (e) {
        e.preventDefault();
        $(this).closest('.mgwpp-image-item').fadeOut(300, function () {
            $(this).remove();
        });
    });

    // Reorder button
    $('.mgwpp-reorder-images').on('click', function (e) {
        e.preventDefault();
        isReordering = !isReordering;

        if (isReordering) {
            $(this).addClass('button-primary');
            $('.mgwpp-remove-image').hide();
            $imageContainer.sortable('enable');
        } else {
            $(this).removeClass('button-primary');
            $imageContainer.sortable('disable');
        }
    });

    // Preview button
    $('.mgwpp-preview-gallery').on('click', function (e) {
        e.preventDefault();
        var galleryId = $('input[name="gallery_id"]').val();
        var previewUrl = '?mgwpp_preview=1&gallery_id=' + galleryId + '&_wpnonce=' + mgwpp_preview_nonce;
        window.open(previewUrl, '_blank');
    });

    // Show remove buttons on hover (except when reordering)
    $imageContainer.on('mouseenter', '.mgwpp-image-item', function () {
        if (!isReordering) {
            $(this).find('.mgwpp-remove-image').show();
        }
    }).on('mouseleave', '.mgwpp-image-item', function () {
        $(this).find('.mgwpp-remove-image').hide();
    });

    // Called In edit gallery in root folder

    jQuery(function ($) {
        // Refresh preview handler
        $('#mgwpp-refresh-preview').on('click', function () {
            const container = $('#mgwpp-preview-container');
            const galleryId = $('.mgwpp-gallery-id').text().replace('ID: ', '');

            container.html('<div class="mgwpp-preview-loading"><p>' + mgwpp_preview.loading_msg + '</p></div>');

            $.ajax({
                url: mgwpp_preview.ajax_url,
                type: 'POST',
                data: {
                    action: 'mgwpp_refresh_preview',
                    gallery_id: galleryId,
                    nonce: mgwpp_preview.nonce
                },
                success: function (response) {
                    if (response.success) {
                        container.html(response.data.html);
                    } else {
                        container.html('<div class="error">' + mgwpp_preview.error_msg + '</div>');
                    }
                },
                error: function () {
                    container.html('<div class="error">' + mgwpp_preview.error_msg + '</div>');
                }
            });
        });

        // Initial preview load
        $('#mgwpp-refresh-preview').trigger('click');
    });


    // Initialize color pickers
    $('.mgwpp-color-field').wpColorPicker();

    // Refresh preview handler
    $('#mgwpp-refresh-preview').on('click', function () {
        const container = $('#mgwpp-preview-container');
        const galleryId = $('.mgwpp-gallery-id').text().replace('ID: ', '');

        container.html('<div class="mgwpp-preview-loading"><p>' + mgwpp_preview.loading_msg + '</p></div>');

        $.ajax({
            url: mgwpp_preview.ajax_url,
            type: 'POST',
            data: {
                action: 'mgwpp_refresh_preview',
                gallery_id: galleryId,
                nonce: mgwpp_preview.nonce
            },
            success: function (response) {
                if (response.success) {
                    container.html(response.data.html);
                } else {
                    container.html('<div class="error">' + response.data + '</div>');
                }
            },
            error: function (xhr) {
                container.html('<div class="error">' + mgwpp_preview.error_msg + '</div>');
                console.error('Preview error:', xhr.responseText);
            }
        });
    });

    // Initial preview load
    $('#mgwpp-refresh-preview').trigger('click');

    // Image uploader
    $('#mgwpp-add-images').on('click', function (e) {
        e.preventDefault();

        const frame = wp.media({
            title: 'Select Gallery Images',
            multiple: true,
            library: { type: 'image' }
        });

        frame.on('select', function () {
            const attachments = frame.state().get('selection').toJSON();
            const container = $('#mgwpp-images-container');

            attachments.forEach(function (attachment) {
                const index = Date.now(); // Unique index

                const item = $(
                    '<div class="mgwpp-image-item" data-index="' + index + '">' +
                    '  <div class="mgwpp-image-preview">' +
                    '    <img src="' + attachment.url + '" alt="Gallery image">' +
                    '    <button type="button" class="mgwpp-remove-image">×</button>' +
                    '  </div>' +
                    '  <input type="hidden" name="mgwpp_gallery_images[' + index + '][id]" value="' + attachment.id + '">' +
                    '  <div class="mgwpp-image-cta">' +
                    '    <input type="text" name="mgwpp_gallery_images[' + index + '][cta_text]" placeholder="">' +
                    '    <input type="url" name="mgwpp_gallery_images[' + index + '][cta_link]" placeholder="">' +
                    '  </div>' +
                    '</div>'
                );

                container.append(item);
            });
        });

        frame.open();
    });

    // Remove image
    $(document).on('click', '.mgwpp-remove-image', function () {
        $(this).closest('.mgwpp-image-item').remove();
    });

    // Make images sortable
    $('#mgwpp-images-container').sortable({
        handle: '.mgwpp-image-preview',
        placeholder: 'mgwpp-sortable-placeholder',
        update: function () {
            // Re-index items after sorting
            $('#mgwpp-images-container .mgwpp-image-item').each(function (index) {
                $(this).attr('data-index', index);

                // Update input names with new index
                $(this).find('[name^="mgwpp_gallery_images"]').each(function () {
                    const name = $(this).attr('name').replace(/\[\d+\]/, '[' + index + ']');
                    $(this).attr('name', name);
                });
            });
        }
    });
});

// Add this after the existing JavaScript code
jQuery(function ($) {
    // Save Gallery Order - AJAX handler
    $('#mgwpp-save-order-btn').on('click', function (e) {
        e.preventDefault();

        const $btn = $(this);
        const originalText = $btn.text();
        $btn.text(mgwppEdit.i18n.saving).prop('disabled', true);

        // Get ordered image IDs
        const imageIds = [];
        $('.mgwpp-image-container .mgwpp-image-item').each(function () {
            imageIds.push($(this).data('id'));
        });

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
                if (response.success) {
                    $btn.text(mgwppEdit.i18n.saved);
                    setTimeout(() => {
                        $btn.text(originalText).prop('disabled', false);
                    }, 2000);
                } else {
                    alert('Error: ' + response.data.message);
                    $btn.text(originalText).prop('disabled', false);
                }
            },
            error: function () {
                alert(mgwppEdit.i18n.saveFailed);
                $btn.text(originalText).prop('disabled', false);
            }
        });
    });

    // Initialize sortable with update event
    $('.mgwpp-image-container.sortable').sortable({
        placeholder: 'mgwpp-image-item ui-sortable-placeholder',
        update: function () {
            // Visual feedback that order changed
            $('#mgwpp-save-order-btn').css('background-color', '#d54e21');
        }
    });
});