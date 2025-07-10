// mg-admin-scripts.js
jQuery(document).ready(function ($) {
  // **************
  // INITIALIZATIONS
  // **************
    let mediaFrame;

  // Main initialization controller
    function initDashboard()
    {
        initColorPickers();
        initMediaUpload();
        initFormHandlers();
        initThemeSystem();
        initGalleryPreviews();
        initProNoticeDismissal();
    }

  // *****************
  // CORE FUNCTIONALITY
  // *****************

  // Color picker initialization
    function initColorPickers()
    {
        $('.color-picker').wpColorPicker();
    }

  // Media upload handler
    function initMediaUpload()
    {
        $('.mgwpp-media-upload').click(function (e) {
            e.preventDefault();
            handleMediaSelection();
        });
    }

    function handleMediaSelection()
    {
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

        mediaFrame.on('select', processSelectedMedia);
        mediaFrame.open();
    }

    function processSelectedMedia()
    {
        const attachments = mediaFrame.state().get('selection').toJSON();
        const mediaIds = attachments.map(attachment => attachment.id);

        $('#selected_media').val(mediaIds.join(','));
        updateMediaPreview(attachments);
    }

    function updateMediaPreview(attachments)
    {
        const preview = $('.mgwpp-media-preview').empty();
        attachments.forEach(attachment => {
            preview.append(`
                <div class="media-thumbnail">
                    <img src="${attachment.sizes.thumbnail.url}"
                         alt="${attachment.alt}"
                         style="width: 80px; height: 80px;">
                </div>
            `);
        });
    }

  // **************
  // FORM MANAGEMENT
  // **************
    function initFormHandlers()
    {
        $('form').on('submit', handleFormSubmission);
    }

    function handleFormSubmission(e)
    {
        e.preventDefault();
        const form = $(this);
        const notice = $('#mgwpp-gallery-notice');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            beforeSend: () => resetNotice(notice),
            success: (response) => showSuccess(notice),
            error: (xhr) => showError(xhr, notice)
        });
    }

    function resetNotice(notice)
    {
        notice.hide().removeClass('success error');
    }

    function showSuccess(notice)
    {
        notice.addClass('success')
        .html(mgwppMedia.gallery_success)
        .show();
        setTimeout(() => {
            window.location.href = 'admin.php?page=mgwpp_galleries';
        }, 1500);
    }

    function showError(xhr, notice)
    {
        const errorMsg = xhr.responseJSON?.message ||
        xhr.responseText ||
        mgwppMedia.generic_error;
        notice.addClass('error').html(errorMsg).show();
    }

  // ************
  // THEME SYSTEM
  // ************
    function initThemeSystem()
    {
        window.toggleDashboardTheme = handleThemeToggle;
        applySavedTheme();
    }

    function handleThemeToggle()
    {
        $('body').toggleClass('dark');
        persistThemeState();
        toggleThemeIcons();
    }

    function persistThemeState()
    {
        localStorage.setItem(
            'mgwpp-theme',
            $('body').hasClass('dark') ? 'dark' : 'light'
        );
    }

    function toggleThemeIcons()
    {
        $('#theme-icon-moon, #theme-icon-sun').toggleClass('hidden');
    }

    function applySavedTheme()
    {
        if (localStorage.getItem('mgwpp-theme') === 'dark') {
            $('body').addClass('dark');
            $('#theme-icon-moon').addClass('hidden');
            $('#theme-icon-sun').removeClass('hidden');
        }
    }

  // ********************
  // GALLERY PREVIEW SYSTEM
  // ********************
    function initGalleryPreviews()
    {
        $('#gallery_type').change(updateGalleryPreview);
    }

    function updateGalleryPreview()
    {
        const option = $(this).find('option:selected');
        $('#preview_img').attr('src', option.data('image') || '');
        $('#preview_demo').attr('href', option.data('demo') || '#');
        $('#gallery_preview').toggle(!!option.data('image'));
    }

  // **********************
  // PRO NOTICE DISMISSAL
  // **********************
    function initProNoticeDismissal()
    {
        $(document).on('click', '.mg-pro-elements-notice .notice-dismiss', dismissProNotice);
    }

    function dismissProNotice()
    {
        $.post(ajaxurl, {
            action: 'mg_dismiss_pro_elements_notice'
        });
    }

  // ********************
  // BOOTSTRAP THE SYSTEM
  // ********************
    if (typeof wp !== 'undefined' && wp.media && $.fn.wpColorPicker) {
        initDashboard();
    } else {
        setTimeout(initDashboard, 500);
    }
});
document.addEventListener('DOMContentLoaded', function () {
    const themeToggle = document.getElementById('mgwpp-theme-toggle');
    const body = document.body; // Or your container element

    if (themeToggle) {
        // Get initial state from localStorage
        const isDark = localStorage.getItem('mgwppTheme') === 'dark';
        const icon = themeToggle.querySelector('img');
        
        // Initialize theme
        if (isDark) {
            body.classList.add('mgwpp-dark-mode');
            icon.src = icon.dataset.sun;
        }

        themeToggle.addEventListener('click', function () {
            const isDark = body.classList.toggle('mgwpp-dark-mode');
            icon.src = isDark ? icon.dataset.sun : icon.dataset.moon;
            
            // Persist in localStorage
            localStorage.setItem('mgwppTheme', isDark ? 'dark' : 'light');
            
            // Update data attribute for server-side reference
            themeToggle.dataset.currentTheme = isDark ? 'dark' : 'light';
        });
    }
});