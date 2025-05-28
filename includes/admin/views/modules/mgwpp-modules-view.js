jQuery(document).ready(($) => {
  // Toggle module status when switch is clicked
  $(".mgwpp-modules-grid").on("change", ".mgwpp-module-toggle", function () {
    const $card = $(this).closest(".mgwpp-module-card");
    const module = $card.data("module");
    const isActive = $(this).is(":checked");
    const $badgeContainer = $(".mgwpp-enabled-gallery-types");

    // Get existing badge if it exists
    const $existingBadge = $(`.mgwpp-gallery-type-badge[data-module="${module}"]`);

    // Immediate visual feedback
    $card
      .toggleClass("active inactive", isActive)
      .css("opacity", isActive ? "1" : "0.7");

    // AJAX request to save the status
    $.ajax({
      url: MGWPPData.ajaxurl,
      type: "POST",
      data: {
        action: "toggle_module_status",
        module: module,
        status: isActive,
        nonce: MGWPPData.nonce,
      },
      success: (response) => {
        // Update badge section
        if (isActive && !$existingBadge.length) {
          const badgeHTML = `
            <div class="mgwpp-gallery-type-badge" data-module="${module}">
              <img src="${$card.find('img').attr('src')}"
                   alt="${module}"
                   class="mgwpp-gallery-type-icon" />
              ${$card.find('h3').text()}
              <label class="mgwpp-switch">
                <input type="checkbox" checked>
                <span class="mgwpp-switch-slider round"></span>
              </label>
            </div>
          `;
          $badgeContainer.append(badgeHTML);
        } else if (!isActive && $existingBadge.length) {
          $existingBadge.remove();
        }
      },
      error: () => {
        // Revert visual state
        $card
          .toggleClass("active inactive", !isActive)
          .css("opacity", isActive ? "0.7" : "1");
        $(this).prop("checked", !isActive);

        // Show error notification
        alert("Error saving module status. Please try again.");
      }
    });
  });

  // Initialize toggle states from PHP
  $(".mgwpp-module-toggle").each(function () {
    const isActive = $(this).prop("checked");
    $(this).closest(".mgwpp-module-card")
      .toggleClass("active inactive", isActive)
      .css("opacity", isActive ? "1" : "0.7");
  });
});