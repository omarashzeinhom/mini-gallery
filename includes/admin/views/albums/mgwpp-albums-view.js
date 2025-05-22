/**
 * Mini Gallery WordPress Plugin - Admin Scripts
 */

;(($) => {
  // Initialize when document is ready
  $(document).ready(() => {
    // Add animation classes to elements
    animateElements()

    // Initialize sortable galleries
    initSortableGalleries()

    // Initialize tooltips
    initTooltips()
  })

  /**
   * Add animation classes to elements for a staggered entrance effect
   */
  function animateElements() {
    const elements = [
      ".mgwpp-header",
      ".mgwpp-stat-card",
      ".mgwpp-tabs-container",
      ".mgwpp-album-form-card",
      ".mgwpp-album-preview-card",
    ]

    elements.forEach((selector, index) => {
      setTimeout(() => {
        $(selector).addClass("mgwpp-animated")
      }, index * 100)
    })
  }

  /**
   * Initialize sortable functionality for galleries
   */
  function initSortableGalleries() {
    if ($.fn.sortable) {
      $(".mgwpp-gallery-grid").sortable({
        items: ".mgwpp-gallery-item",
        placeholder: "mgwpp-sortable-placeholder",
        opacity: 0.7,
        cursor: "move",
        update: (event, ui) => {
          // Update the order in the preview
          updateGalleriesPreview()
        },
      })
    }
  }

  /**
   * Initialize tooltips for better UX
   */
  function initTooltips() {
    $(".mgwpp-help-tip").each(function () {
      $(this).attr("title", $(this).data("tip"))

      if ($.fn.tooltip) {
        $(this).tooltip({
          position: {
            my: "center bottom-10",
            at: "center top",
          },
          tooltipClass: "mgwpp-tooltip",
          content: function () {
            return $(this).data("tip")
          },
        })
      }
    })
  }

  /**
   * Handle bulk actions for albums
   */
  $(document).on("click", "#doaction, #doaction2", function (e) {
    const action = $(this).prev("select").val()

    if (action === "delete") {
      if (!confirm(mgwpp_admin_vars.confirm_delete)) {
        e.preventDefault()
      }
    }
  })

  /**
   * Handle album deletion confirmation
   */
  $(document).on("click", ".mgwpp-delete-album", (e) => {
    if (!confirm(mgwpp_admin_vars.confirm_delete_single)) {
      e.preventDefault()
    }
  })

  /**
   * Toggle all checkboxes in the table
   */
  $(document).on("click", "#cb-select-all-1, #cb-select-all-2", function () {
    const isChecked = $(this).prop("checked")
    $('.mgwpp-albums-table input[type="checkbox"]').prop("checked", isChecked)
  })

  /**
   * Update galleries preview (dummy function, needs implementation)
   */
  function updateGalleriesPreview() {
    // TODO: Implement the logic to update the galleries preview
    console.log("Update galleries preview function called")
  }
})(jQuery)
