document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('mgwpp-theme-toggle');
    
    if(themeToggle) {
        // Get initial state from body class
        const isDarkMode = document.body.classList.contains('mgwpp-dark-mode');
        const icon = themeToggle.querySelector('img');
        
        // Set initial icon
        icon.src = isDarkMode ? icon.dataset.sun : icon.dataset.moon;
        
        themeToggle.addEventListener('click', function(e) {
            e.preventDefault();
            const isDark = !document.body.classList.contains('mgwpp-dark-mode');
            
            // Toggle body class
            document.body.classList.toggle('mgwpp-dark-mode', isDark);
            
            // Update icon immediately
            icon.src = isDark ? icon.dataset.sun : icon.dataset.moon;
            
            // Persist via AJAX to user meta
            jQuery.ajax({
                url: mgwppHeader.ajaxurl,
                type: 'POST',
                data: {
                    action: 'mgwpp_toggle_theme',
                    security: mgwppHeader.nonce
                },
                success: function(response) {
                    if(!response.success) {
                        // Revert on error
                        document.body.classList.toggle('mgwpp-dark-mode', !isDark);
                        icon.src = !isDark ? icon.dataset.sun : icon.dataset.moon;
                    }
                }
            });
        });
    }
});