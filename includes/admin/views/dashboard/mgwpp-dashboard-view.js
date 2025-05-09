document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('mgwpp-theme-toggle');
    const container = document.querySelector('.mgwpp-dashboard-container');
    
    if(themeToggle && container) {
        // Initialize from cookie
        const isDarkMode = document.cookie.includes('mgwpp_dark_mode=true');
        if(isDarkMode) container.classList.add('mgwpp-dark');
        
        themeToggle.addEventListener('click', function() {
            const isDark = container.classList.toggle('mgwpp-dark');
            const icon = this.querySelector('img');
            
            // Toggle icons using data attributes
            icon.src = isDark ? icon.dataset.sun : icon.dataset.moon;
            
            // Set cookie
            document.cookie = `mgwpp_dark_mode=${isDark}; path=/; max-age=${31536000}`;
        });
    }
});