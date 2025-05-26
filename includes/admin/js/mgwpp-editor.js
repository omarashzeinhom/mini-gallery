
jQuery(document).ready(function ($) {
    'use strict';

    const MGWPPEnhancedEditor = {
        currentItem: null,
        galleryData: { items: [] },
        isDirty: false,

        init: function () {
            this.bindEvents();
            this.initSortable();
            this.loadGalleryData();
        },

        bindEvents: function () {
            // Add new item
            $(document).on('click', '.mgwpp-add-new-item, .mgwpp-add-first-item', this.addNewItem.bind(this));

            // Item controls
            $(document).on('click', '.mgwpp-item-edit', this.editItem.bind(this));
            $(document).on('click', '.mgwpp-item-duplicate', this.duplicateItem.bind(this));
            $(document).on('click', '.mgwpp-item-delete', this.deleteItem.bind(this));

            // Item selection
            $(document).on('click', '.mgwpp-gallery-item', this.selectItem.bind(this));

            // Properties panel tabs
            $(document).on('click', '.mgwpp-properties-tabs .nav-tab', this.switchTab.bind(this));

            // Item type change
            $(document).on('change', '.mgwpp-item-type-selector', this.changeItemType.bind(this));

            // Content controls
            $(document).on('click', '.mgwpp-select-image', this.selectImage.bind(this));
            $(document).on('click', '.mgwpp-remove-image', this.removeImage.bind(this));
            $(document).on('click', '.mgwpp-select-video', this.selectVideo.bind(this));

            // Video source tabs
            $(document).on('click', '.mgwpp-video-tab', this.switchVideoSource.bind(this));

            // Text editor toolbar
            $(document).on('click', '.mgwpp-text-bold', this.toggleTextFormat.bind(this));
            $(document).on('click', '.mgwpp-text-italic', this.toggleTextFormat.bind(this));
            $(document).on('click', '.mgwpp-text-underline', this.toggleTextFormat.bind(this));

            // Range inputs
            $(document).on('input', 'input[type="range"]', this.updateRangeValue.bind(this));

            // Form changes
            $(document).on('change input', '.mgwpp-properties-panel input, .mgwpp-properties-panel select, .mgwpp-properties-panel textarea', this.onPropertyChange.bind(this));

            // Save gallery
            $(document).on('click', '.mgwpp-save-gallery', this.saveGallery.bind(this));

            // Preview gallery
            $(document).on('click', '.mgwpp-preview-gallery', this.previewGallery.bind(this));

            // Gallery type change
            $(document).on('change', '.mgwpp-gallery-type', this.changeGalleryType.bind(this));

            // Modal controls
            $(document).on('click', '.mgwpp-modal-close', this.closeModal.bind(this));
            $(document).on('click', '.mgwpp-modal', function (e) {
                if (e.target === this) {
                    MGWPPEnhancedEditor.closeModal();
                }
            });

            // Warn about unsaved changes
            $(window).on('beforeunload', function () {
                if (MGWPPEnhancedEditor.isDirty) {
                    return mgwppEditor.strings.unsavedChanges;
                }
            });
        },

        initSortable: function () {
            $('#mgwpp-sortable-items').sortable({
                handle: '.mgwpp-item-drag-handle',
                placeholder: 'mgwpp-item-placeholder',
                update: this.onItemsReordered.bind(this)
            });
        },

        loadGalleryData: function () {
            if (!mgwppEditor.galleryId) return;

            $.post(mgwppEditor.ajaxUrl, {
                action: 'mgwpp_get_gallery_data',
                gallery_id: mgwppEditor.galleryId,
                nonce: mgwppEditor.nonce
            }, (response) => {
                if (response.success) {
                    this.galleryData = response.data;
                    this.renderItems();
                }
            });
        },

        addNewItem: function (e) {
            e.preventDefault();

            const newItem = {
                id: 'item_' + Date.now(),
                type: 'image',
                title: mgwppEditor.strings.newItem || 'New Item',
                image_url: '',
                image_id: 0,
                alt_text: '',
                width_value: 100,
                width_unit: '%',
                margin: 0,
                padding: 0,
                background_color: '#ffffff',
                border_radius: 0,
                entrance_animation: 'none',
                animation_duration: 0.5,
                animation_delay: 0,
                custom_class: '',
                custom_css: '',
                hide_on_mobile: false,
                hide_on_tablet: false
            };

            this.galleryData.items.push(newItem);
            this.renderItems();
            this.selectItemById(newItem.id);
            this.markDirty();
        },

        editItem: function (e) {
            e.preventDefault();
            e.stopPropagation();

            const $item = $(e.currentTarget).closest('.mgwpp-gallery-item');
            const itemId = $item.data('item-id');
            this.selectItemById(itemId);
        },

        selectItem: function (e) {
            const $item = $(e.currentTarget);
            const itemId = $item.data('item-id');
            this.selectItemById(itemId);
        },

        selectItemById: function (itemId) {
            $('.mgwpp-gallery-item').removeClass('selected');
            $(`.mgwpp-gallery-item[data-item-id="${itemId}"]`).addClass('selected');

            const item = this.galleryData.items.find(i => i.id === itemId);
            if (item) {
                this.currentItem = item;
                this.populatePropertiesPanel(item);
                $('.mgwpp-selected-item-info').text(item.title || 'Untitled');
            }
        },

        duplicateItem: function (e) {
            e.preventDefault();
            e.stopPropagation();

            const $item = $(e.currentTarget).closest('.mgwpp-gallery-item');
            const itemIndex = parseInt($item.data('index'));

            $.post(mgwppEditor.ajaxUrl, {
                action: 'mgwpp_duplicate_gallery_item',
                gallery_id: mgwppEditor.galleryId,
                item_index: itemIndex,
                nonce: mgwppEditor.nonce
            }, (response) => {
                if (response.success) {
                    this.galleryData.items.splice(response.data.new_index, 0, response.data.item);
                    this.renderItems();
                    this.markDirty();
                }
            });
        },

        deleteItem: function (e) {
            e.preventDefault();
            e.stopPropagation();

            if (!confirm(mgwppEditor.strings.confirmDelete)) return;

            const $item = $(e.currentTarget).closest('.mgwpp-gallery-item');
            const itemId = $item.data('item-id');

            this.galleryData.items = this.galleryData.items.filter(item => item.id !== itemId);
            this.renderItems();
            this.clearPropertiesPanel();
            this.markDirty();
        },

        renderItems: function () {
            const $container = $('#mgwpp-sortable-items');

            if (this.galleryData.items.length === 0) {
                $container.html(`
                    <div class="mgwpp-empty-stage">
                        <p>${mgwppEditor.strings.noItems || 'No items in this gallery yet.'}</p>
                        <button class="button button-primary mgwpp-add-first-item">
                            ${mgwppEditor.strings.addFirstItem || 'Add Your First Item'}
                        </button>
                    </div>
                `);
                return;
            }

            let html = '';
            this.galleryData.items.forEach((item, index) => {
                html += this.renderItemHTML(item, index);
            });

            $container.html(html);
        },

        renderItemHTML: function (item, index) {
            const previewHTML = this.getItemPreviewHTML(item);
            const typeLabel = this.getItemTypeLabel(item.type);

            return `
                <div class="mgwpp-gallery-item" data-item-id="${item.id}" data-index="${index}">
                    <div class="mgwpp-item-preview">
                        ${previewHTML}
                    </div>
                    <div class="mgwpp-item-controls">
                        <button class="mgwpp-item-edit" title="${mgwppEditor.strings.editItem || 'Edit Item'}">
                            <span class="dashicons dashicons-edit"></span>
                        </button>
                        <button class="mgwpp-item-duplicate" title="${mgwppEditor.strings.duplicateItem || 'Duplicate Item'}">
                            <span class="dashicons dashicons-admin-page"></span>
                        </button>
                        <button class="mgwpp-item-delete" title="${mgwppEditor.strings.deleteItem || 'Delete Item'}">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                        <div class="mgwpp-item-drag-handle">
                            <span class="dashicons dashicons-move"></span>
                        </div>
                    </div>
                    <div class="mgwpp-item-info">
                        <span class="mgwpp-item-type">${typeLabel}</span>
                        <span class="mgwpp-item-title">${item.title || 'Untitled'}</span>
                    </div>
                </div>
            `;
        },

        getItemPreviewHTML: function (item) {
            switch (item.type) {
                case 'image':
                    if (item.image_url) {
                        return `<img src="${item.image_url}" alt="${item.alt_text || ''}">`;
                    }
                    return '<div class="mgwpp-placeholder-image"><span class="dashicons dashicons-format-image"></span></div>';

                case 'video':
                    if (item.video_url) {
                        return `<video src="${item.video_url}" muted></video>`;
                    }
                    return '<div class="mgwpp-placeholder-video"><span class="dashicons dashicons-video-alt3"></span></div>';

                case 'text':
                    return `<div class="mgwpp-text-preview">${item.content || 'Text content...'}</div>`;

                case 'button':
                    return `<div class="mgwpp-button-preview"><button>${item.button_text || 'Button'}</button></div>`;

                default:
                    return '<div class="mgwpp-placeholder-image">';
            }


        },

        getItemTypeLabel: function (type) {
            const labels = {
                image: mgwppEditor.strings.imageItem || 'Image',
                video: mgwppEditor.strings.videItem || 'Video',
                text: mgwppEditor.strings.textItem || 'Text',
                button: mgwppEditor.strings.buttonItem || 'Button',
            };

            return labels[type] || mgwppEditor.strings.unknownItem || 'Unknown';

        },

    }
    MGWPPEnhancedEditor.init();
});