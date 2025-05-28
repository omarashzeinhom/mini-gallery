<?php
if (!defined('ABSPATH')) {
    exit;
}

$media_handler = new MG_Media_Handler();
$media_items = $media_handler->get_media_library();
?>

<div class="wrap">
    <h1>Visual Gallery Editor</h1>
    
    <div id="mg-visual-editor-app">
        <div class="editor-loading">
            <p>Loading Visual Editor...</p>
        </div>
    </div>
    
    <script type="text/javascript">
        window.mgEditorData = {
            galleryId: <?php echo intval($gallery_id); ?>,
            mediaItems: <?php echo wp_json_encode($media_items); ?>,
            nonce: '<?php echo wp_create_nonce('mg_visual_editor_nonce'); ?>'
        };
    </script>
</div>
