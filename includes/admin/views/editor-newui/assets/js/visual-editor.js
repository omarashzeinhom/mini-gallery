;(($) => {
  class MGVisualEditor {
    constructor() {
      this.galleryId = window.mgEditorData.galleryId || 0
      this.mediaItems = window.mgEditorData.mediaItems || []
      this.canvasItems = []
      this.selectedItem = null
      this.isDragging = false
      this.dragOffset = { x: 0, y: 0 }
      this.showGrid = true
      this.activeTab = "content"

      this.init()
    }

    init() {
      this.renderEditor()
      this.bindEvents()
      this.loadGallery()
    }

    renderEditor() {
      const editorHTML = `
                <div class="mg-editor-container">
                    <div class="mg-editor-header">
                        <div class="header-content">
                            <h2>Visual Gallery Editor</h2>
                            <div class="header-actions">
                                <select id="gallery-type" class="gallery-type-select">
                                    <option value="grid">Image Grid</option>
                                    <option value="masonry">Masonry</option>
                                    <option value="carousel">Carousel</option>
                                    <option value="slider">Fullscreen Slider</option>
                                    <option value="custom">Custom Layout</option>
                                </select>
                                <button id="save-gallery" class="button button-primary">Save Gallery</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mg-editor-main">
                        <div class="canvas-area">
                            <div class="canvas-toolbar">
                                <button class="button add-media">📷 Add Media</button>
                                <button class="button toggle-grid">⊞ Toggle Grid</button>
                                <button class="button add-text">📝 Add Text</button>
                                <button class="button add-button">🔘 Add Button</button>
                            </div>
                            
                            <div class="canvas-container">
                                <div id="mg-canvas" class="canvas show-grid">
                                    <div class="empty-canvas">
                                        <h3>Your canvas is empty</h3>
                                        <p>Drag items from the media library or use the toolbar to add content</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="editor-sidebar">
                            <div class="sidebar-panel media-panel">
                                <div class="panel-header">
                                    <h3>Media Library</h3>
                                    <input type="search" id="media-search" placeholder="Search media..." class="search-input">
                                </div>
                                <div class="media-grid" id="media-grid"></div>
                            </div>
                            
                            <div class="sidebar-panel properties-panel">
                                <div class="panel-header">
                                    <h3>Properties</h3>
                                    <span class="selected-info" id="selected-info"></span>
                                </div>
                                <div class="properties-content" id="properties-content">
                                    <div class="no-selection">
                                        <p>Select an item on the canvas to edit its properties</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `

      $("#mg-visual-editor-app").html(editorHTML)
      this.renderMediaGrid()
    }

    renderMediaGrid() {
      const mediaGrid = $("#media-grid")
      mediaGrid.empty()

      this.mediaItems.forEach((item) => {
        const mediaItem = $(`
                    <div class="media-item" data-media-id="${item.id}">
                        <div class="media-thumbnail">
                            ${
                              item.type === "image"
                                ? `<img src="${item.thumbnail || item.url}" alt="${item.title}">`
                                : `<div class="video-thumbnail"><span class="video-icon">🎥</span></div>`
                            }
                        </div>
                        <div class="media-info">
                            <span class="media-title">${item.title}</span>
                            <span class="media-type">${item.type}</span>
                        </div>
                    </div>
                `)

        mediaGrid.append(mediaItem)
      })
    }

    bindEvents() {
      const self = this

      // Media item click
      $(document).on("click", ".media-item", function () {
        const mediaId = $(this).data("media-id")
        const mediaItem = self.mediaItems.find((item) => item.id == mediaId)
        if (mediaItem) {
          self.addToCanvas(mediaItem)
        }
      })

      // Toolbar buttons
      $(".toggle-grid").on("click", () => {
        this.showGrid = !this.showGrid
        $("#mg-canvas").toggleClass("show-grid", this.showGrid)
      })

      $(".add-text").on("click", () => this.addNewItem("text"))
      $(".add-button").on("click", () => this.addNewItem("button"))

      // Save gallery
      $("#save-gallery").on("click", () => this.saveGallery())

      // Canvas events
      $(document).on("mousedown", ".canvas-item", function (e) {
        self.handleCanvasMouseDown(e, this)
      })

      $(document).on("mousemove", (e) => {
        self.handleMouseMove(e)
      })

      $(document).on("mouseup", () => {
        self.handleMouseUp()
      })

      // Delete item
      $(document).on("click", ".delete-handle", function (e) {
        e.stopPropagation()
        const itemId = $(this).closest(".canvas-item").data("item-id")
        self.deleteCanvasItem(itemId)
      })

      // Media search
      $("#media-search").on("input", function () {
        const searchTerm = $(this).val().toLowerCase()
        $(".media-item").each(function () {
          const title = $(this).find(".media-title").text().toLowerCase()
          $(this).toggle(title.includes(searchTerm))
        })
      })
    }

    addToCanvas(mediaItem) {
      const newCanvasItem = {
        ...mediaItem,
        id: `canvas_${Date.now()}`,
        position: { x: 100, y: 100 },
        dimensions: { width: 200, height: 150 },
        rotation: 0,
        zIndex: this.canvasItems.length + 1,
      }

      this.canvasItems.push(newCanvasItem)
      this.renderCanvasItem(newCanvasItem)
      this.updateEmptyState()
    }

    addNewItem(type) {
      const newItem = {
        id: `new_${Date.now()}`,
        type: type,
        title: `New ${type}`,
        content: type === "text" ? "Sample text content" : type === "button" ? "Click me" : undefined,
        url: type === "image" ? mgAjax.pluginUrl + "editor/assets/images/placeholder.png" : undefined,
        position: { x: 150, y: 150 },
        dimensions: { width: type === "text" ? 300 : 200, height: type === "text" ? 100 : 150 },
        rotation: 0,
        zIndex: this.canvasItems.length + 1,
      }

      this.canvasItems.push(newItem)
      this.renderCanvasItem(newItem)
      this.selectItem(newItem)
      this.updateEmptyState()
    }

    renderCanvasItem(item) {
      const canvas = $("#mg-canvas")

      let content = ""
      switch (item.type) {
        case "image":
          content = `<img src="${item.url}" alt="${item.title}">`
          break
        case "text":
          content = `<div class="text-content">${item.content}</div>`
          break
        case "button":
          content = `<button class="button-content">${item.content}</button>`
          break
        case "video":
          content = `<video src="${item.url}" controls></video>`
          break
      }

      const canvasItem = $(`
                <div class="canvas-item" data-item-id="${item.id}" style="
                    left: ${item.position.x}px;
                    top: ${item.position.y}px;
                    width: ${item.dimensions.width}px;
                    height: ${item.dimensions.height}px;
                    transform: rotate(${item.rotation}deg);
                    z-index: ${item.zIndex};
                ">
                    <div class="item-content">${content}</div>
                    <div class="item-controls" style="display: none;">
                        <div class="resize-handle resize-nw"></div>
                        <div class="resize-handle resize-ne"></div>
                        <div class="resize-handle resize-sw"></div>
                        <div class="resize-handle resize-se"></div>
                        <div class="rotate-handle"></div>
                        <button class="delete-handle">×</button>
                    </div>
                </div>
            `)

      canvas.append(canvasItem)
    }

    handleCanvasMouseDown(e, element) {
      e.preventDefault()
      e.stopPropagation()

      const itemId = $(element).data("item-id")
      const item = this.canvasItems.find((i) => i.id == itemId)

      if (item) {
        this.selectItem(item)
        this.isDragging = true

        const canvasOffset = $("#mg-canvas").offset()
        this.dragOffset = {
          x: e.pageX - canvasOffset.left - item.position.x,
          y: e.pageY - canvasOffset.top - item.position.y,
        }
      }
    }

    handleMouseMove(e) {
      if (!this.isDragging || !this.selectedItem) return

      const canvasOffset = $("#mg-canvas").offset()
      const newX = e.pageX - canvasOffset.left - this.dragOffset.x
      const newY = e.pageY - canvasOffset.top - this.dragOffset.y

      this.updateCanvasItem(this.selectedItem.id, {
        position: { x: Math.max(0, newX), y: Math.max(0, newY) },
      })
    }

    handleMouseUp() {
      this.isDragging = false
    }

    selectItem(item) {
      this.selectedItem = item

      // Update visual selection
      $(".canvas-item").removeClass("selected")
      $(".item-controls").hide()

      const selectedElement = $(`.canvas-item[data-item-id="${item.id}"]`)
      selectedElement.addClass("selected")
      selectedElement.find(".item-controls").show()

      this.renderPropertiesPanel()
    }

    updateCanvasItem(itemId, updates) {
      const itemIndex = this.canvasItems.findIndex((item) => item.id == itemId)
      if (itemIndex !== -1) {
        Object.assign(this.canvasItems[itemIndex], updates)

        if (this.selectedItem && this.selectedItem.id == itemId) {
          Object.assign(this.selectedItem, updates)
        }

        // Update DOM element
        const element = $(`.canvas-item[data-item-id="${itemId}"]`)
        const item = this.canvasItems[itemIndex]

        element.css({
          left: item.position.x + "px",
          top: item.position.y + "px",
          width: item.dimensions.width + "px",
          height: item.dimensions.height + "px",
          transform: `rotate(${item.rotation}deg)`,
          zIndex: item.zIndex,
        })

        // Update content if changed
        if (updates.content) {
          if (item.type === "text") {
            element.find(".text-content").text(item.content)
          } else if (item.type === "button") {
            element.find(".button-content").text(item.content)
          }
        }
      }
    }

    deleteCanvasItem(itemId) {
      this.canvasItems = this.canvasItems.filter((item) => item.id != itemId)
      $(`.canvas-item[data-item-id="${itemId}"]`).remove()

      if (this.selectedItem && this.selectedItem.id == itemId) {
        this.selectedItem = null
        this.renderPropertiesPanel()
      }

      this.updateEmptyState()
    }

    renderPropertiesPanel() {
      const propertiesContent = $("#properties-content")
      const selectedInfo = $("#selected-info")

      if (!this.selectedItem) {
        selectedInfo.text("")
        propertiesContent.html(
          '<div class="no-selection"><p>Select an item on the canvas to edit its properties</p></div>',
        )
        return
      }

      selectedInfo.text(this.selectedItem.title)

      const propertiesHTML = `
                <div class="tab-navigation">
                    <button class="tab-button ${this.activeTab === "content" ? "active" : ""}" data-tab="content">Content</button>
                    <button class="tab-button ${this.activeTab === "design" ? "active" : ""}" data-tab="design">Design</button>
                </div>
                
                <div class="tab-content">
                    ${this.activeTab === "content" ? this.renderContentTab() : this.renderDesignTab()}
                </div>
            `

      propertiesContent.html(propertiesHTML)
      this.bindPropertiesEvents()
    }

    renderContentTab() {
      const item = this.selectedItem

      let contentFields = ""
      if (item.type === "text") {
        contentFields = `
                    <div class="control-group">
                        <label>Text Content</label>
                        <textarea id="item-content" rows="4" placeholder="Enter your text...">${item.content || ""}</textarea>
                    </div>
                `
      } else if (item.type === "button") {
        contentFields = `
                    <div class="control-group">
                        <label>Button Text</label>
                        <input type="text" id="item-content" value="${item.content || ""}" placeholder="Button text">
                    </div>
                `
      }

      return `
                <div class="content-tab">
                    <div class="control-group">
                        <label>Position</label>
                        <div class="position-controls">
                            <input type="number" id="item-x" value="${item.position.x}" placeholder="X">
                            <input type="number" id="item-y" value="${item.position.y}" placeholder="Y">
                        </div>
                    </div>
                    
                    <div class="control-group">
                        <label>Size</label>
                        <div class="size-controls">
                            <input type="number" id="item-width" value="${item.dimensions.width}" placeholder="Width">
                            <input type="number" id="item-height" value="${item.dimensions.height}" placeholder="Height">
                        </div>
                    </div>
                    
                    <div class="control-group">
                        <label>Rotation: ${item.rotation}°</label>
                        <input type="range" id="item-rotation" min="0" max="360" value="${item.rotation}" class="range-input">
                    </div>
                    
                    ${contentFields}
                </div>
            `
    }

    renderDesignTab() {
      return `
                <div class="design-tab">
                    <div class="control-group">
                        <label>Background Color</label>
                        <input type="color" id="item-bg-color" value="#ffffff" class="color-input">
                    </div>
                    
                    <div class="control-group">
                        <label>Border Radius</label>
                        <input type="range" id="item-border-radius" min="0" max="50" value="0" class="range-input">
                    </div>
                    
                    <div class="control-group">
                        <label>Opacity</label>
                        <input type="range" id="item-opacity" min="0" max="100" value="100" class="range-input">
                    </div>
                </div>
            `
    }

    bindPropertiesEvents() {
      const self = this

      // Tab switching
      $(".tab-button").on("click", function () {
        self.activeTab = $(this).data("tab")
        self.renderPropertiesPanel()
      })

      // Position controls
      $("#item-x, #item-y").on("input", () => {
        const x = Number.parseInt($("#item-x").val()) || 0
        const y = Number.parseInt($("#item-y").val()) || 0
        self.updateCanvasItem(self.selectedItem.id, {
          position: { x, y },
        })
      })

      // Size controls
      $("#item-width, #item-height").on("input", () => {
        const width = Number.parseInt($("#item-width").val()) || 0
        const height = Number.parseInt($("#item-height").val()) || 0
        self.updateCanvasItem(self.selectedItem.id, {
          dimensions: { width, height },
        })
      })

      // Rotation
      $("#item-rotation").on("input", function () {
        const rotation = Number.parseInt($(this).val()) || 0
        self.updateCanvasItem(self.selectedItem.id, { rotation })
        $(this).prev("label").text(`Rotation: ${rotation}°`)
      })

      // Content
      $("#item-content").on("input", function () {
        const content = $(this).val()
        self.updateCanvasItem(self.selectedItem.id, { content })
      })
    }

    updateEmptyState() {
      const emptyCanvas = $(".empty-canvas")
      if (this.canvasItems.length === 0) {
        emptyCanvas.show()
      } else {
        emptyCanvas.hide()
      }
    }

    loadGallery() {
      if (this.galleryId === 0) return

      $.post(
        window.mgAjax.ajaxurl,
        {
          action: "mg_load_gallery",
          gallery_id: this.galleryId,
          nonce: window.mgAjax.nonce,
        },
        (response) => {
          if (response.success && response.data.items) {
            this.canvasItems = response.data.items
            this.renderCanvas()

            if (response.data.type) {
              $("#gallery-type").val(response.data.type)
            }
          }
        },
      )
    }

    renderCanvas() {
      $("#mg-canvas")
        .empty()
        .append(
          '<div class="empty-canvas"><h3>Your canvas is empty</h3><p>Drag items from the media library or use the toolbar to add content</p></div>',
        )

      this.canvasItems.forEach((item) => {
        this.renderCanvasItem(item)
      })

      this.updateEmptyState()
    }

    saveGallery() {
      const galleryData = {
        title: "Gallery " + (this.galleryId || Date.now()),
        type: $("#gallery-type").val(),
        items: this.canvasItems,
        settings: {},
      }

      $.post(
        window.mgAjax.ajaxurl,
        {
          action: "mg_save_gallery",
          gallery_id: this.galleryId,
          gallery_data: JSON.stringify(galleryData),
          nonce: window.mgAjax.nonce,
        },
        (response) => {
          if (response.success) {
            alert("Gallery saved successfully!")
            if (this.galleryId === 0) {
              this.galleryId = response.data.gallery_id
              window.history.replaceState({}, "", window.location.href + "&gallery_id=" + this.galleryId)
            }
          } else {
            alert("Error saving gallery")
          }
        },
      )
    }
  }

  // Initialize when document is ready
  $(document).ready(() => {
    new MGVisualEditor()
  })
})(jQuery)
