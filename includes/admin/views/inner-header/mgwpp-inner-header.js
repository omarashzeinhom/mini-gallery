jQuery(document).ready(function ($) {
    // Theme Toggle Handler
    const themeToggle = $('#mgwpp-theme-toggle');
    const loaderOverlay = $('.mgwpp-loader-overlay');
    
    if (themeToggle.length) {
        const icon = themeToggle.find('img');
        const body = $('body');
        const sunIcon = themeToggle.data('sun');
        const moonIcon = themeToggle.data('moon');

        themeToggle.on('click', function (e) {
            e.preventDefault();
            
            const currentTheme = themeToggle.data('current-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

            // Show loader immediately
            loaderOverlay.fadeIn(100);
            
            // Optimistic UI update
            body.toggleClass('mgwpp-dark-mode', newTheme === 'dark');
            icon.attr('src', newTheme === 'dark' ? sunIcon : moonIcon);
            themeToggle.data('current-theme', newTheme);

            // AJAX request
            $.ajax({
                url: mgwppThemeData.ajax_url,
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'mgwpp_toggle_theme',
                    security: mgwppThemeData.nonce,
                    theme: newTheme
                },
                success: function (response) {
                    if (!response.success) {
                        revertUI();
                        console.error('Error:', response.data?.message);
                    }
                    loaderOverlay.fadeOut(100);
                },
                error: function (xhr, status, error) {
                    revertUI();
                    console.error(`AJAX Error(${xhr.status}):`, error);
                    loaderOverlay.fadeOut(100);
                }
            });

            function revertUI() {
                // Revert to previous theme
                body.toggleClass('mgwpp-dark-mode', currentTheme === 'dark');
                icon.attr('src', currentTheme === 'dark' ? sunIcon : moonIcon);
                themeToggle.data('current-theme', currentTheme);
            }
        });
    }

    // Global AJAX loader
    $(document).ajaxSend(function (event, jqxhr, settings) {
        if (settings.url.includes('admin-ajax.php') && 
            settings.data.includes('action=mgwpp')) {
            loaderOverlay.fadeIn(100);
        }
    });
    
    $(document).ajaxComplete(function () {
        loaderOverlay.fadeOut(100);
    });
});