<?php
if (!defined('ABSPATH')) {
    exit;
}

$gallery_manager = new MG_Gallery_Manager();
$galleries = $gallery_manager->get_all_galleries();
?>

<div class="wrap">
    <h1>All Galleries</h1>
    
    <div class="mg-galleries-header">
        <a href="<?php echo admin_url('admin.php?page=mg-visual-editor'); ?>" class="button button-primary">
            Create New Gallery
        </a>
    </div>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Title</th>
                <th>Type</th>
                <th>Created</th>
                <th>Updated</th>
                <th>Shortcode</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($galleries)): ?>
                <tr>
                    <td colspan="6">No galleries found. <a href="<?php echo admin_url('admin.php?page=mg-visual-editor'); ?>">Create your first gallery</a>.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($galleries as $gallery): ?>
                    <tr>
                        <td><strong><?php echo esc_html($gallery->title); ?></strong></td>
                        <td><?php echo esc_html(ucfirst($gallery->gallery_type)); ?></td>
                        <td><?php echo esc_html(date('M j, Y', strtotime($gallery->created_at))); ?></td>
                        <td><?php echo esc_html(date('M j, Y', strtotime($gallery->updated_at))); ?></td>
                        <td>
                            <code>[mg_gallery id="<?php echo intval($gallery->id); ?>"]</code>
                            <button class="button button-small copy-shortcode" data-shortcode='[mg_gallery id="<?php echo intval($gallery->id); ?>"]'>Copy</button>
                        </td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=mg-visual-editor&gallery_id=' . intval($gallery->id)); ?>" class="button button-small">Edit</a>
                            <button class="button button-small duplicate-gallery" data-gallery-id="<?php echo intval($gallery->id); ?>">Duplicate</button>
                            <button class="button button-small button-link-delete delete-gallery" data-gallery-id="<?php echo intval($gallery->id); ?>">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    $('.copy-shortcode').on('click', function() {
        var shortcode = $(this).data('shortcode');
        navigator.clipboard.writeText(shortcode).then(function() {
            alert('Shortcode copied to clipboard!');
        });
    });
    
    $('.duplicate-gallery').on('click', function() {
        var galleryId = $(this).data('gallery-id');
        if (confirm('Are you sure you want to duplicate this gallery?')) {
            $.post(ajaxurl, {
                action: 'mg_duplicate_gallery',
                gallery_id: galleryId,
                nonce: '<?php echo wp_create_nonce('mg_visual_editor_nonce'); ?>'
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error duplicating gallery');
                }
            });
        }
    });
    
    $('.delete-gallery').on('click', function() {
        var galleryId = $(this).data('gallery-id');
        if (confirm('Are you sure you want to delete this gallery? This action cannot be undone.')) {
            $.post(ajaxurl, {
                action: 'mg_delete_gallery',
                gallery_id: galleryId,
                nonce: '<?php echo wp_create_nonce('mg_visual_editor_nonce'); ?>'
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error deleting gallery');
                }
            });
        }
    });
});
</script>
