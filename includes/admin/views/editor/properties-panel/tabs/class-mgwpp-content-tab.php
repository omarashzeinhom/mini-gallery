<?php
/* includes/admin/editor/properties-panel/tabs/class-mgwpp-content-tab.php */
if (!defined('ABSPATH')) exit;

class MGWPP_Content_Tab {
    public function render() { ?>
        <div class="mgwpp-tab-content active" data-tab="content">
            <div class="mgwpp-control-group">
                <label><?php _e('Item Type', 'mini-gallery'); ?></label>
                <select id="item_type" class="mgwpp-item-type-selector">
                    <option value="image"><?php _e('Image', 'mini-gallery'); ?></option>
                    <option value="video"><?php _e('Video', 'mini-gallery'); ?></option>
                    <option value="text"><?php _e('Text', 'mini-gallery'); ?></option>
                    <option value="button"><?php _e('Button', 'mini-gallery'); ?></option>
                </select>
            </div>

            <div class="mgwpp-control-group">
                <label><?php _e('Item Title', 'mini-gallery'); ?></label>
                <input type="text" id="item_title" class="mgwpp-item-title" placeholder="<?php _e('Enter item title...', 'mini-gallery'); ?>">
            </div>

            <!-- Image Content -->
            <div class="mgwpp-content-type mgwpp-image-content">
                <div class="mgwpp-control-group">
                    <label><?php _e('Image', 'mini-gallery'); ?></label>
                    <div class="mgwpp-image-selector">
                        <div class="mgwpp-image-preview">
                            <img id="item_image_preview" src="" alt="" style="display: none;">
                            <div class="mgwpp-image-placeholder">
                                <span class="dashicons dashicons-format-image"></span>
                                <p><?php _e('No image selected', 'mini-gallery'); ?></p>
                            </div>
                        </div>
                        <div class="mgwpp-image-controls">
                            <button type="button" class="button mgwpp-select-image">
                                <?php _e('Select Image', 'mini-gallery'); ?>
                            </button>
                            <button type="button" class="button mgwpp-remove-image" style="display: none;">
                                <?php _e('Remove Image', 'mini-gallery'); ?>
                            </button>
                        </div>
                        <input type="hidden" id="item_image_id" class="mgwpp-image-id">
                        <input type="hidden" id="item_image_url" class="mgwpp-image-url">
                    </div>
                </div>
                <div class="mgwpp-control-group">
                    <label><?php _e('Alt Text', 'mini-gallery'); ?></label>
                    <input type="text" id="item_alt_text" class="mgwpp-alt-text" placeholder="<?php _e('Image description...', 'mini-gallery'); ?>">
                </div>
            </div>

            <!-- Video Content -->
            <div class="mgwpp-content-type mgwpp-video-content" style="display: none;">
                <div class="mgwpp-video-source-tabs">
                    <button type="button" class="mgwpp-video-tab active" data-source="upload">
                        <?php _e('Upload', 'mini-gallery'); ?>
                    </button>
                    <button type="button" class="mgwpp-video-tab" data-source="url">
                        <?php _e('URL', 'mini-gallery'); ?>
                    </button>
                    <button type="button" class="mgwpp-video-tab" data-source="youtube">
                        <?php _e('YouTube', 'mini-gallery'); ?>
                    </button>
                    <button type="button" class="mgwpp-video-tab" data-source="vimeo">
                        <?php _e('Vimeo', 'mini-gallery'); ?>
                    </button>
                </div>

                <div class="mgwpp-video-source-content">
                    <div class="mgwpp-video-upload active" data-source="upload">
                        <button type="button" class="button mgwpp-select-video">
                            <?php _e('Select Video', 'mini-gallery'); ?>
                        </button>
                        <input type="hidden" id="item_video_id" class="mgwpp-video-id">
                    </div>
                    <div class="mgwpp-video-url" data-source="url">
                        <input type="url" id="item_video_url" placeholder="<?php _e('Video URL...', 'mini-gallery'); ?>">
                    </div>
                    <div class="mgwpp-video-youtube" data-source="youtube">
                        <input type="text" id="item_youtube_id" placeholder="<?php _e('YouTube Video ID...', 'mini-gallery'); ?>">
                    </div>
                    <div class="mgwpp-video-vimeo" data-source="vimeo">
                        <input type="text" id="item_vimeo_id" placeholder="<?php _e('Vimeo Video ID...', 'mini-gallery'); ?>">
                    </div>
                </div>
            </div>

            <!-- Text Content -->
            <div class="mgwpp-content-type mgwpp-text-content" style="display: none;">
                <div class="mgwpp-control-group">
                    <label><?php _e('Text Content', 'mini-gallery'); ?></label>
                    <div class="mgwpp-text-editor">
                        <div class="mgwpp-text-toolbar">
                            <button type="button" class="mgwpp-text-bold" title="<?php _e('Bold', 'mini-gallery'); ?>">
                                <strong>B</strong>
                            </button>
                            <button type="button" class="mgwpp-text-italic" title="<?php _e('Italic', 'mini-gallery'); ?>">
                                <em>I</em>
                            </button>
                            <button type="button" class="mgwpp-text-underline" title="<?php _e('Underline', 'mini-gallery'); ?>">
                                <u>U</u>
                            </button>
                        </div>
                        <textarea id="item_text_content" rows="5" placeholder="<?php _e('Enter your text content...', 'mini-gallery'); ?>"></textarea>
                    </div>
                </div>
            </div>

            <!-- Button Content -->
            <div class="mgwpp-content-type mgwpp-button-content" style="display: none;">
                <div class="mgwpp-control-group">
                    <label><?php _e('Button Text', 'mini-gallery'); ?></label>
                    <input type="text" id="item_button_text" placeholder="<?php _e('Button Text...', 'mini-gallery'); ?>">
                </div>
                <div class="mgwpp-control-group">
                    <label><?php _e('Button URL', 'mini-gallery'); ?></label>
                    <input type="url" id="item_button_url" placeholder="<?php _e('https://...', 'mini-gallery'); ?>">
                </div>
                <div class="mgwpp-control-group">
                    <label><?php _e('Button Style', 'mini-gallery'); ?></label>
                    <select id="item_button_style">
                        <option value="primary"><?php _e('Primary', 'mini-gallery'); ?></option>
                        <option value="secondary"><?php _e('Secondary', 'mini-gallery'); ?></option>
                        <option value="outline"><?php _e('Outline', 'mini-gallery'); ?></option>
                        <option value="link"><?php _e('Link', 'mini-gallery'); ?></option>
                    </select>
                </div>
                <div class="mgwpp-control-group">
                    <label>
                        <input type="checkbox" id="item_button_new_tab">
                        <?php _e('Open in new tab', 'mini-gallery'); ?>
                    </label>
                </div>
            </div>
        </div>
    <?php }
}

