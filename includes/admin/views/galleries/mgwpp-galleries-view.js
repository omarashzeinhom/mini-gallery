jQuery(document).ready(function($) {
    // Copy shortcode to clipboard
    $(document).on('click', '.mgwpp-shortcode-input', function() {
        this.select();
        document.execCommand('copy');
        
        // Show feedback
        var originalText = $(this).val();
        $(this).val(mgwppAdmin.i18n.copied);
        setTimeout(() => {
            $(this).val(originalText);
        }, 1000);
    });
    
    // Enhanced copy with button
    $(document).on('click', '.mgwpp-copy-shortcode', function(e) {
        e.preventDefault();
        var shortcodeInput = $(this).siblings('.mgwpp-shortcode-input');
        shortcodeInput.select();
        
        try {
            document.execCommand('copy');
            $(this).text(mgwppAdmin.i18n.copied);
            setTimeout(() => {
                $(this).text(mgwppAdmin.i18n.copy);
            }, 1000);
        } catch (err) {
            $(this).text(mgwppAdmin.i18n.copyFailed);
        }
    });
});