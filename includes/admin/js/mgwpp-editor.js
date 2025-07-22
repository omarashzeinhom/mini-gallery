
jQuery(document).ready(function ($) {
    'use strict';

    const MGWPPEnhancedEditor = {
        currentItem: null,
        galleryData: { items: [] },
        isDirty: false,
        currentSlideIndex: 0,
        slides: [],

        init: function () {
            this.bindEvents();
            this.initSortable();
            this.loadGalleryData();
            // Initialize canvas
            this.canvas.init();
            this.canvasProperties.init();
            this.addNewSlide();
            // Render initial canvas if data exists
            if (this.galleryData.canvas_items && this.galleryData.canvas_items.length > 0) {
                this.canvas.renderCanvas();
            }
        },



        addNewSlide: function () {
            const newSlide = {
                id: 'slide_' + Date.now(),
                items: [],
                background: '#ffffff',
                tranisitons: {}

            };

            this.slides.push(newSlide);
            this.currentSlideIndex = this.slides.length - 1;
            this.renderSliders();
        },


        renderSlides: function () {
            const $slidesContainer = $('.mgwpp-slides-container');
            $slidesContainer.html('');

            this.slides.forEach((slide, index) => {
                const slideHTML =
                    `
                <div
                className="mgwpp-slide ${index === this.currentSlideIndex ? 'active' : ''}"
                data-slide-index="${index}"
                >

                <div class="mgwpp-slide-number">
                ${index + 1}
                </div>
                <div class="mgwpp-slide-items">
                ${this.renderSlideItems(slide.items)}
                </div>

                </div>
                `;
                $slidesContainer.append(slideHTML);


            });


        },

        switchTab: function (e) {
            e.preventDefault();
            const $tab = $(e.currentTarget);
            const target = $tab.data('target');

            // remove active classes
            $tab.closest('.mgwpp-properties-tabs').find('.nav-tab').removeClass('active');

            $tab.closest('.mgwpp-properties-content').find('.mgwpp-tab-content').removeClass('active');

            //  active classes
            $tab.addClass('active');
            $(`.mgwpp-tab-content[data-tab="${target}"]`).addClass('active');
        },

        bindEvents: function () {
            // Update add item buttons
            $(document).on('click', '.mgwpp-add-image', (e) => this.addNewItem(e, 'image'));
            $(document).on('click', '.mgwpp-add-button', (e) => this.addNewItem(e, 'button'));

            // Keep existing add first item binding
            $(document).on('click', '.mgwpp-add-first-item', this.addNewItem.bind(this));
            //  new item
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
            if (!mgwppEditor.galleryId) {
                return;
            }

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

        //  these methods to handle dirty state
        markDirty: function () {
            this.isDirty = true;
            // Optional: Add visual indicator
            $('.mgwpp-save-gallery').addClass('mgwpp-has-changes');
        },

        clearDirty: function () {
            this.isDirty = false;
            // Optional: Remove visual indicator
            $('.mgwpp-save-gallery').removeClass('mgwpp-has-changes');
        },

        //  this method to handle saving
        saveGallery: function (e) {
            e.preventDefault();

            const dataToSave = {
                items: this.galleryData.items || [],
                canvas_items: this.galleryData.canvas_items || [],
                settings: this.galleryData.settings || {}
            };


            $.post(mgwppEditor.ajaxUrl, {
                action: 'mgwpp_save_gallery_data',
                gallery_id: mgwppEditor.galleryId,
                gallery_data: JSON.stringify(dataToSave),
                nonce: mgwppEditor.nonce
            }, (response) => {
                if (response.success) {
                    this.clearDirty();
                    alert(mgwppEditor.strings.saveSuccess);
                    // Refresh canvas items from saved data
                    this.galleryData.canvas_items = response.data.canvas_items || [];
                    this.canvas.renderCanvas();
                } else {
                    alert(mgwppEditor.strings.saveError);
                }
            });
        },


        addNewItem: function (e, type = 'image') {
            if (e) {
                e.preventDefault();
            }


            const newItem = {
                id: 'item_' + Date.now(),
                type: type,

                // Common Properties
                position: {
                    x: 0,
                    y: 0
                },

                size: {
                    width: 100,
                    height: 100
                },
                // Type-specific properties

                ...(type === 'image' && {
                    image_url: '',
                    alt_text: ''
                }),
                ...(type === 'button' && {
                    text: 'Button',
                    url: '#',
                    style: 'primary',
                }),

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


            this.galleryData.items.push(newItem); this.renderItems();
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

            if (!confirm(mgwppEditor.strings.confirmDelete)) {
                return;
            }

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

        populatePropertiesPanel: function (item) {
  // Changed parameter to item
            // Corrected selector typo
            $('.mgwpp-properties-panel input, .mgwpp-properties-panel select, .mgwpp-properties-panel textarea').val('');

            // Setting the basic fields
            this.setFieldValue('item_title', item.title || '');
            this.setFieldValue('item_alt_text', item.alt_text || '');
            this.setFieldValue('item_custom_class', item.custom_class || ''); // Fixed typo
            this.setFieldValue('item_custom_css', item.custom_css || '');

            // Set Item Type
            $('.mgwpp-item-type-selector').val(item.type).trigger('change');

            switch (item.type) {
                case 'image':
                    this.setFieldValue('item_image_url', item.image_url || '');
                    this.setFieldValue('item_image_id', item.image_id || '');
                    break;

                case 'video':
                    this.setFieldValue('item_video_url', item.video_url || '');
                    $('.mgwpp-video-tab').removeClass('active');
                    // Fixed selector syntax
                    $(`.mgwpp-video-tab[data-source="${item.video_source}"]`).addClass('active');
                    break;

                case 'text':
                    this.setFieldValue('item_text_content', item.content || '');
                    break;

                case 'button':
                    // Fixed property name
                    this.setFieldValue('item_button_text', item.button_text || '');
                    this.setFieldValue('item_button_url', item.button_url || '');
                    break;
            }

            // Layout Properties
            this.setFieldValue('item_width_value', item.width_value || 100);
            this.setFieldValue('item_width_unit', item.width_unit || '%');
            this.setFieldValue('item_margin', item.margin || 0);
            this.setFieldValue('item_padding', item.padding || 0);
            this.setFieldValue('item_background_color', item.background_color || '#ffffff');
            this.setFieldValue('item_border_radius', item.border_radius || 0);

            // Animation Properties
            this.setFieldValue('item_entrance_animation', item.entrance_animation || 'none');
            this.setFieldValue('item_animation_duration', item.animation_duration || 0.5);
            this.setFieldValue('item_animation_delay', item.animation_delay || 0);

            // Visibility Options
            // Fixed method name spelling
            this.setCheckboxState('item_hide_mobile', item.hide_on_mobile || false);
            this.setCheckboxState('item_hide_tablet', item.hide_on_tablet || false);

            $('input[type="range"]').trigger('input');
        },

        // Helper Function to set form field values
        setFieldValue: function (selector, value) {
            const $field = $(`#${selector}`);
            if ($field.is(':checkbox')) {
                $field.prop('checked', Boolean(value));
            } else {
                $field.val(value);
            }
        },

        // Fixed method name spelling

        setCheckboxState: function (selector, state) {
  // Changed parameter name
            $(`#${selector}`).prop('checked', Boolean(state));
        },

        //  this method to clear the properties panel
        clearPropertiesPanel: function () {
            $('.mgwpp-properties-panel input, .mgwpp-properties-panel select, .mgwpp-properties-panel textarea').val('');
            $('.mgwpp-properties-tabs .nav-tab').removeClass('active').first().addClass('active');
            $('.mgwpp-tab-content').removeClass('active').first().addClass('active');
            $('.mgwpp-selected-item-info').text(mgwppEditor.strings.selectItem || 'Select an item to edit');
            this.currentItem = null;
        },
        //  to your MGWPPEnhancedEditor object

        // Update the canvas object methods
        canvas: {
            init: function () {
                this.$canvas = $('#mgwpp-main-canvas');
                this.setupCanvasEvents();
                this.initialized = true;
            },

            setupCanvasEvents: function () {
                // Make items draggable
                this.$canvas.sortable({
                    items: '.mgwpp-canvas-item',
                    cursor: 'move',
                    containment: 'parent',
                    start: (e, ui) => this.onDragStart(e, ui),
                    stop: (e, ui) => this.onDragStop(e, ui)
                }).disableSelection();
            },

            addImageToCanvas: function (e) {
                e.preventDefault();
                const self = this;

                const frame = wp.media({
                    title: mgwppEditor.strings.selectImage || 'Select Image',
                    multiple: false,
                    library: { type: 'image' }
                });

                frame.on('select', function () {
                    const attachment = frame.state().get('selection').first().toJSON();

                    const newItem = {
                        id: 'canvas_' + Date.now(),
                        type: 'image',
                        image_url: attachment.url,
                        image_id: attachment.id,
                        alt_text: attachment.alt || '',
                        position: { x: 50, y: 50 },
                        dimensions: { width: 200, height: 200 },
                        z_index: self.getNextZIndex(),
                        rotation: 0
                    };

                    if (!MGWPPEnhancedEditor.galleryData.canvas_items) {
                        MGWPPEnhancedEditor.galleryData.canvas_items = [];
                    }

                    MGWPPEnhancedEditor.galleryData.canvas_items.push(newItem);
                    self.renderCanvas();
                    MGWPPEnhancedEditor.markDirty();
                });

                frame.open();
            },

            renderCanvas: function () {
                if (!MGWPPEnhancedEditor.galleryData.canvas_items ||
                    MGWPPEnhancedEditor.galleryData.canvas_items.length === 0) {
                    this.$canvas.html(`<div class="mgwpp-empty-canvas">
                    <p>${mgwppEditor.strings.emptyCanvas || 'No items on canvas yet.'}</p></div>`);
                    return;
                }

                let html = '';
                MGWPPEnhancedEditor.galleryData.canvas_items.forEach(item => {
                    html += this.renderCanvasItemHTML(item);
                });

                this.$canvas.html(html);
                this.initResizable();
                this.initDraggable();
            },

            initResizable: function () {
                this.$canvas.find('.mgwpp-canvas-item').resizable({
                    handles: 'se',
                    containment: 'parent',
                    resize: (e, ui) => this.onResize(e, ui)
                });
            },

            initDraggable: function () {
                this.$canvas.find('.mgwpp-canvas-item').draggable({
                    containment: 'parent',
                    drag: (e, ui) => this.onDrag(e, ui)
                });
            },

            // Update position handling
            onDrag: function (e, ui) {
                const itemId = ui.helper.data('item-id');
                const item = MGWPPEnhancedEditor.galleryData.canvas_items.find(i => i.id === itemId);

                if (item) {
                    item.position.x = ui.position.left;
                    item.position.y = ui.position.top;
                    MGWPPEnhancedEditor.markDirty();
                }
            },

            // Update resize handling
            onResize: function (e, ui) {
                const itemId = ui.element.data('item-id');
                const item = MGWPPEnhancedEditor.galleryData.canvas_items.find(i => i.id === itemId);

                if (item) {
                    item.dimensions.width = ui.size.width;
                    item.dimensions.height = ui.size.height;
                    MGWPPEnhancedEditor.markDirty();
                }
            }
        },


        //  to your MGWPPEnhancedEditor object
        canvasProperties: {
            init: function () {
                // Position controls
                $(document).on('change', '.mgwpp-pos-x, .mgwpp-pos-y', this.updatePosition.bind(this));

                // Size controls
                $(document).on('change', '.mgwpp-width, .mgwpp-height', this.updateSize.bind(this));

                // Rotation control
                $(document).on('input change', '.mgwpp-rotation', this.updateRotation.bind(this));

                // Layer controls
                $(document).on('click', '.mgwpp-bring-to-front', this.bringToFront.bind(this));
                $(document).on('click', '.mgwpp-send-to-back', this.sendToBack.bind(this));
            },

            updatePosition: function () {
                if (!this.currentItem) {
                    return;
                }

                const x = parseInt($('.mgwpp-pos-x').val()) || 0;
                const y = parseInt($('.mgwpp-pos-y').val()) || 0;

                this.currentItem.position.x = x;
                this.currentItem.position.y = y;

                this.updateCanvasItem(this.currentItem);
                this.markDirty();
            },

            updateSize: function () {
                if (!this.currentItem) {
                    return;
                }

                const width = parseInt($('.mgwpp-width').val()) || 100;
                const height = parseInt($('.mgwpp-height').val()) || 100;

                this.currentItem.dimensions.width = width;
                this.currentItem.dimensions.height = height;

                this.updateCanvasItem(this.currentItem);
                this.markDirty();
            },

            updateRotation: function (e) {
                if (!this.currentItem) {
                    return;
                }

                const rotation = parseInt($(e.currentTarget).val()) || 0;
                this.currentItem.rotation = rotation;

                this.updateCanvasItem(this.currentItem);
                this.markDirty();

                $('.mgwpp-range-value').text(rotation + '°');
            },

            bringToFront: function () {
                if (!this.currentItem) {
                    return;
                }

                this.currentItem.z_index = this.getNextZIndex();
                this.updateCanvasItem(this.currentItem);
                this.markDirty();
            },

            sendToBack: function () {
                if (!this.currentItem) {
                    return;
                }

                if (!this.galleryData.canvas_items || this.galleryData.canvas_items.length === 0) {
                    this.currentItem.z_index = 0;
                } else {
                    const minZIndex = Math.min(...this.galleryData.canvas_items.map(item => item.z_index));
                    this.currentItem.z_index = minZIndex - 1;
                }

                this.updateCanvasItem(this.currentItem);
                this.markDirty();
            },

            updateCanvasItem: function (item) {
                const $item = $(`.mgwpp-canvas-item[data-item-id="${item.id}"]`);

                if ($item.length) {
                    const style = `left:${item.position.x}px;top:${item.position.y}px;` +
                    `width:${item.dimensions.width}px;height:${item.dimensions.height}px;` +
                        `z-index:${item.z_index};` +
                        (item.rotation ? `transform:rotate(${item.rotation}deg);` : '');

                    $item.attr('style', style);
                }
            }
        }

    }
    MGWPPEnhancedEditor.init();
});