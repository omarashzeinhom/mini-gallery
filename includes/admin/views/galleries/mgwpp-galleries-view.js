jQuery(document).ready(function($) {
    // Only run on galleries pages
    if (!$('.mgwpp-gallery-grid').length) return;

    // 1. COPY SHORTCODE FUNCTIONALITY
    // ==============================
    
    // Handle input click
    $(document).on('click', '.mgwpp-shortcode-input', function() {
        this.select();
        copyToClipboard(this.value, $(this));
    });
    
    // Handle copy button click
    $(document).on('click', '.mgwpp-copy-shortcode', function(e) {
        e.preventDefault();
        const $input = $(this).siblings('.mgwpp-shortcode-input');
        copyToClipboard($input.val(), $(this));
    });
    
    // Unified copy function
    function copyToClipboard(text, $element) {
        // Try modern Clipboard API first
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                showFeedback($element, true);
            }).catch(() => {
                showFeedback($element, false);
            });
        } 
        // Fallback for older browsers
        else {
            try {
                const success = document.execCommand('copy');
                showFeedback($element, success);
            } catch (err) {
                showFeedback($element, false);
            }
        }
    }
    
    // Show feedback based on element type
    function showFeedback($element, success) {
        const isInput = $element.is('input');
        const originalContent = isInput ? $element.val() : $element.text();
        const successText = mgwppAdmin.i18n.copied;
        const errorText = mgwppAdmin.i18n.copyFailed;
        
        if (isInput) {
            $element.val(success ? successText : errorText);
        } else {
            $element.text(success ? successText : errorText);
        }
        
        setTimeout(() => {
            isInput ? $element.val(originalContent) : $element.text(originalContent);
        }, 1500);
    }
    
    // 2. BULK ACTIONS FUNCTIONALITY
    // =============================
    
    // Show/hide bulk actions
    $(document).on('change', '.mgwpp-bulk-checkbox', function() {
        const checkedCount = $('.mgwpp-bulk-checkbox:checked').length;
        $('.mgwpp-bulk-actions').toggle(checkedCount > 0);
        
        // Update selected count
        if (checkedCount > 0) {
            $('#mgwpp-selected-count').text(
                mgwppAdmin.i18n.selectedCount.replace('%d', checkedCount)
            );
        }
    });
    
    // Toggle all checkboxes
    $(document).on('change', '#mgwpp-toggle-all', function() {
        const isChecked = $(this).prop('checked');
        $('.mgwpp-bulk-checkbox').prop('checked', isChecked).trigger('change');
    });
    
    // Apply bulk action
    $(document).on('click', '#mgwpp-apply-bulk-action', function() {
        const action = $('#mgwpp-bulk-action').val();
        if (action !== 'delete') return;
        
        const galleryIds = $('.mgwpp-bulk-checkbox:checked').map(function() {
            return this.value;
        }).get();
        
        if (galleryIds.length === 0) return;
        
        // Confirm deletion
        if (!confirm(mgwppAdmin.i18n.confirmDelete)) return;
        
        // AJAX request
        $.post(mgwppAdmin.ajaxUrl, {
            action: 'mgwpp_bulk_delete_galleries',
            ids: galleryIds,
            nonce: mgwppAdmin.nonce
        }).then(response => {
            if (response.success) {
                location.reload();
            } else {
                alert(mgwppAdmin.i18n.deleteError);
            }
        }).fail(() => {
            alert(mgwppAdmin.i18n.deleteError);
        });
    });
});