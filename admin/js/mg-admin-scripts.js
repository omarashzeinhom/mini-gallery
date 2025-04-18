// In mg-admin-scripts.js
jQuery(document).ready(function ($) {
    let mediaFrame;

    $('.mgwpp-media-upload').click(function (e) {
        e.preventDefault();

        if (mediaFrame) {
            mediaFrame.open();
            return;
        }

        mediaFrame = wp.media({
            title: mgwppMedia.text_title,
            button: { text: mgwppMedia.text_select },
            library: { type: 'image' },
            multiple: true
        });

        mediaFrame.on('select', function () {
            const attachments = mediaFrame.state().get('selection').toJSON();
            const mediaIds = attachments.map(attachment => attachment.id);
            $('#selected_media').val(mediaIds.join(','));

            // Update preview
            const preview = $('.media-preview').empty();
            attachments.forEach(attachment => {
                preview.append(`
                    <div class="media-thumbnail">
                        <img src="${attachment.sizes.thumbnail.url}" 
                             alt="${attachment.alt}" 
                             style="width: 80px; height: 80px;">
                    </div>
                `);
            });
        });

        mediaFrame.open();
    });

    // Form submission handler
    $('form').on('submit', function (e) {
        e.preventDefault();
        const form = $(this);
        const notice = $('#mgwpp-gallery-notice');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            beforeSend: () => {
                notice.hide().removeClass('success error');
            },
            success: (response) => {
                notice.addClass('success').html(mgwppMedia.gallery_success).show();
                setTimeout(() => {
                    window.location.href = 'admin.php?page=mgwpp_galleries';
                }, 1500);
            },
            error: (xhr) => {
                const errorMsg = xhr.responseJSON?.message ||
                    xhr.responseText ||
                    mgwppMedia.generic_error;
                notice.addClass('error').html(errorMsg).show();
            }
        });
    });


    // Theme toggler
    window.toggleDashboardTheme = () => {
        $('body').toggleClass('dark');
        localStorage.setItem('mgwpp-theme',
            $('body').hasClass('dark') ? 'dark' : 'light'
        );
        $('#theme-icon-moon, #theme-icon-sun').toggleClass('hidden');
    };

    // Initial theme check
    if (localStorage.getItem('mgwpp-theme') === 'dark') {
        $('body').addClass('dark');
        $('#theme-icon-moon').addClass('hidden');
        $('#theme-icon-sun').removeClass('hidden');
    }


    // Gallery type preview
    $('#gallery_type').change(function () {
        const option = $(this).find('option:selected');
        $('#preview_img').attr('src', option.data('image') || '');
        $('#preview_demo').attr('href', option.data('demo') || '#');
        $('#gallery_preview').toggle(!!option.data('image'));
    });
    

      // Dismiss Pro Elements Notice
      $(document).on('click', '.mg-pro-elements-notice .notice-dismiss', function () {
        $.post(ajaxurl, { 
            action: 'mg_dismiss_pro_elements_notice' 
        });
    });
});



