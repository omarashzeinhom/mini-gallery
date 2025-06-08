jQuery(document).ready(function($) {
    // Initialize variables
    var mediaUploader;
    var isReordering = false;
    var $imageContainer = $('.mgwpp-image-container');
    
    // Make images sortable
    $imageContainer.sortable({
        placeholder: 'mgwpp-image-item ui-sortable-placeholder',
        opacity: 0.6,
        revert: 200,
        start: function() {
            isReordering = true;
            $('.mgwpp-remove-image').hide();
        },
        stop: function() {
            isReordering = false;
        }
    });
    
    // Add Images button
    $('.mgwpp-add-images').on('click', function(e) {
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
        mediaUploader.on('select', function() {
            var attachments = mediaUploader.state().get('selection').toJSON();
            
            attachments.forEach(function(attachment) {
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
    $imageContainer.on('click', '.mgwpp-remove-image', function(e) {
        e.preventDefault();
        $(this).closest('.mgwpp-image-item').fadeOut(300, function() {
            $(this).remove();
        });
    });
    
    // Reorder button
    $('.mgwpp-reorder-images').on('click', function(e) {
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
    $('.mgwpp-preview-gallery').on('click', function(e) {
        e.preventDefault();
        var galleryId = $('input[name="gallery_id"]').val();
        var previewUrl = '?mgwpp_preview=1&gallery_id=' + galleryId + '&_wpnonce=' + mgwpp_preview_nonce;
        window.open(previewUrl, '_blank');
    });
    
    // Show remove buttons on hover (except when reordering)
    $imageContainer.on('mouseenter', '.mgwpp-image-item', function() {
        if (!isReordering) {
            $(this).find('.mgwpp-remove-image').show();
        }
    }).on('mouseleave', '.mgwpp-image-item', function() {
        $(this).find('.mgwpp-remove-image').hide();
    });
});