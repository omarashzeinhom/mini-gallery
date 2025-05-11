jQuery(document).ready(($) => {
  // Toggle module status when switch is clicked
  $(".mgwpp-module-toggle").on("change", function () {
    const $card = $(this).closest(".mgwpp-module-card")
    const module = $card.data("module")
    const isActive = $(this).is(":checked")

    // Immediate visual feedback
    if (isActive) {
      $card.removeClass("inactive").addClass("active")
      $card.css("opacity", "1")
    } else {
      $card.removeClass("active").addClass("inactive")
      $card.css("opacity", "0.7")
    }

    // Smooth transition
    $card.css("transition", "opacity 0.3s ease, border-color 0.3s ease")

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
      error: function () {
        // Revert on error
        if (isActive) {
          $card.removeClass("active").addClass("inactive")
          $card.css("opacity", "0.7")
        } else {
          $card.removeClass("inactive").addClass("active")
          $card.css("opacity", "1")
        }
        $(this).prop("checked", !isActive)

        // Show error notification
        alert("Error saving module status. Please try again.")
      },
    })
  })

  // Add hover effect to cards
  $(".mgwpp-module-card").hover(
    function () {
      $(this).css("transform", "translateY(-2px)")
    },
    function () {
      $(this).css("transform", "translateY(0)")
    },
  )
})
