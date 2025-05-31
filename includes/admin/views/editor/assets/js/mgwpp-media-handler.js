jQuery(function($) {
    // Open media library when button is clicked
    $(document).on('click', '.mgwpp-open-media-library', function(e) {
        e.preventDefault();
        
        const editorId = $(this).data('editor');
        const frame = wp.media({
            title: mgMedia.title,
            button: { text: mgMedia.button },
            multiple: true,
            library: { type: 'image' }
        });
        
        // Handle selection
        frame.on('select', function() {
            const attachments = frame.state().get('selection').toJSON();
            const container = $(`#${editorId} .mgwpp-selected-media`);
            const hiddenInput = $(`#${editorId} .mgwpp-selected-media-ids`);
            const ids = [];
            
            attachments.forEach(attachment => {
                // Add to visual list
                container.append(`
                    <li class="mgwpp-media-item" data-id="${attachment.id}">
                        <img src="${attachment.sizes.thumbnail.url}" alt="${attachment.title}">
                        <button type="button" class="mgwpp-remove-media">
                            <span class="dashicons dashicons-no"></span>
                        </button>
                    </li>
                `);
                
                // Add to hidden input
                ids.push(attachment.id);
            });
            
            // Update hidden field with comma-separated IDs
            hiddenInput.val(ids.join(','));
        });
        
        frame.open();
    });
    
    // Remove media item
    $(document).on('click', '.mgwpp-remove-media', function() {
        const item = $(this).closest('.mgwpp-media-item');
        const itemId = item.data('id');
        const hiddenInput = item.closest('.mgwpp-media-selector').find('.mgwpp-selected-media-ids');
        const ids = hiddenInput.val().split(',').filter(id => id != itemId);
        
        hiddenInput.val(ids.join(','));
        item.remove();
    });
});