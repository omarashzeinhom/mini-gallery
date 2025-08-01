(($) => {
    // Initialize when document is ready
    $(document).ready(() => {
        initAlbumForm();
        initSortableGalleries();
        initTooltips();
        initTableActions();
    });
    
    function initAlbumForm() {
        // Media uploader for album cover
        $('.mgwpp-upload-cover-btn').click(function(e) {
            e.preventDefault();
            let image_frame;

            if (image_frame) {
                image_frame.open();
                return;
            }

            image_frame = wp.media({
                title: mgwpp_admin_vars.select_cover || 'Select Album Cover Image',
                multiple: false,
                library: {
                    type: 'image'
                }
            });

            image_frame.on('select', function() {
                const attachment = image_frame.state().get('selection').first().toJSON();
                $('#album_cover_id').val(attachment.id);
                $('#album-cover-preview').html(
                    `<img src="${attachment.url}" alt="${mgwpp_admin_vars.album_cover}" class="mgwpp-cover-image">`
                );
                $('#preview-cover-image').attr('src', attachment.url);
                $('.mgwpp-remove-cover-btn').show();
            });

            image_frame.open();
        });

        // Remove cover image
        $('.mgwpp-remove-cover-btn').click(function(e) {
            e.preventDefault();
            $('#album_cover_id').val('');
            $('#album-cover-preview').html(
                `<img src="${mgwpp_admin_vars.placeholder_url}" alt="${mgwpp_admin_vars.album_preview}" class="mgwpp-cover-image">`
            );
            $('#preview-cover-image').attr('src', mgwpp_admin_vars.placeholder_url);
            $(this).hide();
        });

        // Live preview for album title
        $('#album_title').on('input', function() {
            const title = $(this).val();
            $('#preview-title').text(title || mgwpp_admin_vars.album_title);
        });

        // Live preview for album description
        $('#album_description').on('input', function() {
            const description = $(this).val();
            $('#preview-description').text(description || mgwpp_admin_vars.album_description);
        });

        // Gallery search functionality
        $('#gallery-search').on('keyup', function() {
            const value = $(this).val().toLowerCase();
            $('.mgwpp-gallery-item').each(function() {
                const galleryText = $(this).text().toLowerCase();
                $(this).toggle(galleryText.indexOf(value) > -1);
            });
        });

        // Update selected galleries preview
        $('.mgwpp-gallery-checkbox-input').on('change', function() {
            updateGalleriesPreview();
        });

        // Reset form button
        $('.mgwpp-reset-btn').click(function() {
            setTimeout(() => {
                $('#preview-title').text(mgwpp_admin_vars.album_title);
                $('#preview-description').text(mgwpp_admin_vars.album_description);
                $('#preview-cover-image').attr('src', mgwpp_admin_vars.placeholder_url);
                $('#preview-galleries-list').html(
                    `<li class="mgwpp-empty-selection">${mgwpp_admin_vars.no_galleries}</li>`
                );
                $('#album-cover-preview').html(
                    `<img src="${mgwpp_admin_vars.placeholder_url}" alt="${mgwpp_admin_vars.album_preview}">`
                );
                $('.mgwpp-remove-cover-btn').hide();
            }, 100);
        });
    }

    function updateGalleriesPreview() {
        const selectedGalleries = [];
        $('.mgwpp-gallery-checkbox-input:checked').each(function() {
            const galleryTitle = $(this).closest('.mgwpp-gallery-item').find('h4').text().trim();
            selectedGalleries.push(galleryTitle);
        });

        const $previewList = $('#preview-galleries-list');
        if (selectedGalleries.length > 0) {
            let html = '';
            selectedGalleries.forEach(gallery => {
                html += `<li>${gallery}</li>`;
            });
            $previewList.html(html);
        } else {
            $previewList.html(
                `<li class="mgwpp-empty-selection">${mgwpp_admin_vars.no_galleries}</li>`
            );
        }
    }

    function initSortableGalleries() {
        if ($.fn.sortable) {
            $(".mgwpp-gallery-grid").sortable({
                items: ".mgwpp-gallery-item",
                placeholder: "mgwpp-sortable-placeholder",
                opacity: 0.7,
                cursor: "move",
                update: () => updateGalleriesPreview()
            });
        }
    }

    function initTooltips() {
        $(".mgwpp-help-tip").each(function() {
            $(this).tooltip({
                content: function() {
                    return $(this).data("tip");
                },
                classes: {
                    "ui-tooltip": "mgwpp-tooltip"
                }
            });
        });
    }

    function initTableActions() {
        // Bulk actions
        $(document).on("click", "#doaction, #doaction2", function(e) {
            const action = $(this).prev("select").val();
            if (action === "delete" && !confirm(mgwpp_admin_vars.confirm_delete)) {
                e.preventDefault();
            }
        });

        // Single delete
        $(document).on("click", ".mgwpp-action-delete", function(e) {
            if (!confirm(mgwpp_admin_vars.confirm_delete_single)) {
                e.preventDefault();
            }
        });

        // Checkbox toggle
        $(document).on("click", "#cb-select-all-1, #cb-select-all-2", function() {
            const isChecked = $(this).prop("checked");
            $('.mgwpp-albums-table input[type="checkbox"]').prop("checked", isChecked);
        });

        // Copy shortcode
        $(document).on('click', '.mgwpp-copy-shortcode', function(e) {
            e.preventDefault();
            const text = $(this).data('clipboard-text');
            navigator.clipboard.writeText(text).then(() => {
                const $copied = $('<div class="mgwpp-copied">Copied!</div>');
                $('body').append($copied);
                setTimeout(() => $copied.fadeOut(500, () => $copied.remove()), 2000);
            });
        });
    }
})(jQuery);