<div class="mgwpp-control-tabs">
    <nav class="mgwpp-tab-nav">
        <button class="nav-tab active" data-target="content"><?php _e('Content', 'mini-gallery'); ?></button>
        <button class="nav-tab" data-target="layout"><?php _e('Layout', 'mini-gallery'); ?></button>
        <button class="nav-tab" data-target="style"><?php _e('Style', 'mini-gallery'); ?></button>
    </nav>

    <div class="mgwpp-tab-content active" data-tab="content">
        <div class="mgwpp-media-library-upload">
            <button class="button mgwpp-add-media">
                <?php _e('Add Images', 'mini-gallery'); ?>
            </button>
        </div>
        <div class="mgwpp-media-list sortable"></div>
    </div>

    <div class="mgwpp-tab-content" data-tab="layout">
        <div class="mgwpp-control-group">
            <label><?php _e('Columns', 'mini-gallery'); ?></label>
            <input type="number" class="mgwpp-columns" min="1" max="6" value="3">
        </div>
        <!-- Add more layout controls -->
    </div>

    <div class="mgwpp-tab-content" data-tab="style">
        <div class="mgwpp-control-group">
            <label><?php _e('Thumbnail Style', 'mini-gallery'); ?></label>
            <select class="mgwpp-thumbnail-style">
                <option value="default"><?php _e('Default', 'mini-gallery'); ?></option>
                <option value="rounded"><?php _e('Rounded Corners', 'mini-gallery'); ?></option>
                <option value="circle"><?php _e('Circular', 'mini-gallery'); ?></option>
            </select>
        </div>
        <!-- Add more style controls -->
    </div>
</div>