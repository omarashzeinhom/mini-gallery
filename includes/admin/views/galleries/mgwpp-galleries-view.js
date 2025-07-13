jQuery(document).ready(function($) {
    // Only run on galleries pages
    if (!$('.mgwpp-gallery-grid').length) return;

    // 1. COPY SHORTCODE FUNCTIONALITY
    // ==============================
    
    // Unified copy function
    const copyToClipboard = (text, $element) => {
        // Try modern Clipboard API first
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text)
                .then(() => showFeedback($element, true))
                .catch(() => showFeedback($element, false));
        } 
        // Fallback for older browsers
        else {
            try {
                const $temp = $('<textarea>').val(text).appendTo('body').select();
                const success = document.execCommand('copy');
                $temp.remove();
                showFeedback($element, success);
            } catch (err) {
                showFeedback($element, false);
            }
        }
    };
    
    // Show feedback based on element type
    const showFeedback = ($element, success) => {
        const isInput = $element.is('input');
        const originalContent = isInput ? $element.val() : $element.text();
        const feedbackText = success ? mgwppAdmin.i18n.copied : mgwppAdmin.i18n.copyFailed;
        
        if (isInput) {
            $element.val(feedbackText);
        } else {
            $element.text(feedbackText);
        }
        
        setTimeout(() => {
            isInput ? $element.val(originalContent) : $element.text(originalContent);
        }, 1500);
    };
    
    // Event delegation for copy actions
    $(document)
        .on('click', '.mgwpp-shortcode-input', function() {
            this.select();
            copyToClipboard(this.value, $(this));
        })
        .on('click', '.mgwpp-copy-shortcode', function(e) {
            e.preventDefault();
            copyToClipboard($(this).siblings('.mgwpp-shortcode-input').val(), $(this));
        });

    // 2. BULK ACTIONS FUNCTIONALITY
    // =============================
    
    // Cache DOM elements
    const $bulkContainer = $('.mgwpp-bulk-actions');
    const $selectAllContainer = $('.mgwpp-select-all-container');
    const $bulkCheckboxes = $('.mgwpp-bulk-checkbox');
    const $bulkActionSelect = $('#mgwpp-bulk-action');
    const $selectedCount = $('#mgwpp-selected-count');
    
    // Toggle bulk UI visibility
    const toggleBulkActions = () => {
        const checkedCount = $bulkCheckboxes.filter(':checked').length;
        
        $bulkContainer.toggle(checkedCount > 0);
        $selectAllContainer.toggle($bulkCheckboxes.length > 0);
        
        if (checkedCount > 0) {
            $selectedCount.text(
                mgwppAdmin.i18n.selectedCount.replace('%d', checkedCount)
            );
        }
    };
    
    // Initialize bulk UI
    toggleBulkActions();
    
    // Event handlers
    $(document)
        .on('change', '.mgwpp-bulk-checkbox', toggleBulkActions)
        .on('change', '#mgwpp-toggle-all', function() {
            const isChecked = $(this).prop('checked');
            $bulkCheckboxes.prop('checked', isChecked);
            toggleBulkActions();
        })
        .on('click', '#mgwpp-apply-bulk-action', function() {
            if ($bulkActionSelect.val() !== 'delete') return;
            
            const galleryIds = $bulkCheckboxes.filter(':checked').map(function() {
                return this.value;
            }).get();
            
            if (galleryIds.length === 0) return;
            
            if (!confirm(mgwppAdmin.i18n.confirmDelete)) return;
            
            $.post(mgwppAdmin.ajaxUrl, {
                action: 'mgwpp_bulk_delete_galleries',
                ids: galleryIds,
                nonce: mgwppAdmin.nonce
            })
            .then(response => response.success ? location.reload() : alert(mgwppAdmin.i18n.deleteError))
            .catch(() => alert(mgwppAdmin.i18n.deleteError));
        });
});