document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('mgwpp-theme-toggle');
    const body = document.body; // Or your container element

    if(themeToggle) {
        // Get initial state from localStorage
        const isDark = localStorage.getItem('mgwppTheme') === 'dark';
        const icon = themeToggle.querySelector('img');
        
        // Initialize theme
        if(isDark) {
            body.classList.add('mgwpp-dark-mode');
            icon.src = icon.dataset.sun;
        }

        themeToggle.addEventListener('click', function() {
            const isDark = body.classList.toggle('mgwpp-dark-mode');
            icon.src = isDark ? icon.dataset.sun : icon.dataset.moon;
            
            // Persist in localStorage
            localStorage.setItem('mgwppTheme', isDark ? 'dark' : 'light');
            
            // Update data attribute for server-side reference
            themeToggle.dataset.currentTheme = isDark ? 'dark' : 'light';
        });
    }
});