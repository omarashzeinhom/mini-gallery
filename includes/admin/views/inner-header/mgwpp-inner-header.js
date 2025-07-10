jQuery(document).ready(function ($) {
    const themeToggle = $('#mgwpp-theme-toggle');

    if (!themeToggle.length || typeof mgwppHeader === 'undefined') {
        console.warn('MiniGallery: Theme toggle missing required context');
        return;
    }

    if (themeToggle.length) {
        const icon = themeToggle.find('img');
        const body = $('body');

        // Handle image loading errors
        icon.on('error', function () {
            const isDark = body.hasClass('mgwpp-dark-mode');
            $(this).attr(
                'src',
                isDark ? $(this).data('sun-fallback') : $(this).data('moon-fallback')
            );
        });

        // Initialize icon from body class
        const isDarkMode = body.hasClass('mgwpp-dark-mode');
        icon.attr(
            'src',
            isDarkMode ? themeToggle.data('sun') : themeToggle.data('moon')
        );

        themeToggle.on('click', function (e) {
            e.preventDefault();
            // Existing AJAX logic with added:
            if (!mgwppHeader?.ajaxurl) {
                console.error('MiniGallery: Missing AJAX endpoint');
                return;
            }
            const currentTheme = themeToggle.data('current-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

            // Optimistic UI update
            body.toggleClass('mgwpp-dark-mode', newTheme === 'dark');
            icon.attr(
                'src',
                newTheme === 'dark' ? themeToggle.data('sun') : themeToggle.data('moon')
            );
            themeToggle.data('current-theme', newTheme);

            // Persist via AJAX
            $.ajax({
                url: mgwppHeader.ajaxurl,
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'mgwpp_toggle_theme',
                    security: mgwppHeader.nonce,
                    theme: newTheme
                },
                success: function (response) {
                    if (!response.success) {
                        console.error('Error:', response.data.message);
                        revertUI();
                        showErrorToast(response.data.message);
                    }
                },
                error: function (xhr, status, error) {
                    console.error(`AJAX Error(${xhr.status}):`, error);
                    revertUI();
                    showErrorToast('Connection error - settings not saved');
                }
            });

            // Add error notification function
            function showErrorToast(message)
            {
                const toast = $(`<div class="mgwpp-error-toast">${message}</div>`);
                $('body').append(toast);
                setTimeout(() => toast.remove(), 3000);
            }
        });
    }
});