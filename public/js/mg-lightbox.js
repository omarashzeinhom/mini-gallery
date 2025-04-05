/**
 * Enhanced Mini Gallery Lightbox
 * Improved lightbox functionality with smooth transitions and keyboard navigation
 */
document.addEventListener("DOMContentLoaded", () => {
    const lightbox = document.getElementById("mgwpp-lightbox")
    if (!lightbox) return
  
    const items = Array.from(document.querySelectorAll(".mgwpp-gallery-item"))
    const imgContainer = lightbox.querySelector(".mgwpp-lightbox-image-container")
    const captionEl = lightbox.querySelector(".mgwpp-lightbox-caption")
    const overlayEl = lightbox.querySelector(".mgwpp-lightbox-overlay")
  
    let currentIndex = 0
    let isTransitioning = false
  
    function openLightbox(index) {
      if (!items[index] || isTransitioning) return
  
      isTransitioning = true
      const item = items[index]
      const caption = item.dataset.caption || ""
      const albumTitle =
        item.closest(".mgwpp-gallery-container")?.querySelector(".mgwpp-gallery-title")?.textContent || ""
  
      // Clear previous image
      imgContainer.innerHTML = ""
  
      // Create new image with loading state
      const img = document.createElement("img")
      img.src = item.href
      img.alt = item.querySelector("img")?.alt || ""
      img.classList.add("mgwpp-lightbox-image")
  
      // Wait for image to load
      img.onload = () => {
        isTransitioning = false
      }
  
      img.onerror = () => {
        isTransitioning = false
        img.alt = "Error loading image"
      }
  
      imgContainer.appendChild(img)
      captionEl.textContent = caption
      overlayEl.textContent = albumTitle
  
      lightbox.classList.add("active")
      document.body.classList.add("lightbox-open")
      //document.body.style.overflow = "hidden" // Prevent scrolling
      currentIndex = index
  
      // Announce to screen readers
      announceForScreenReaders(`Image ${index + 1} of ${items.length}. ${caption}`)
    }
  
    function closeLightbox() {
      lightbox.classList.remove("active")
      document.body.classList.remove("lightbox-open")
      //document.body.style.overflow = "" // Restore scrolling
  
      // Announce to screen readers
      announceForScreenReaders("Lightbox closed")
    }
  
    function navigate(direction) {
      if (isTransitioning) return
  
      const newIndex = (currentIndex + direction + items.length) % items.length
  
      // Announce to screen readers
      announceForScreenReaders(`Navigating to image ${newIndex + 1} of ${items.length}`)
  
      openLightbox(newIndex)
    }
  
    // Create screen reader announcement element
    function announceForScreenReaders(message) {
      const announcement = document.createElement("div")
      announcement.className = "sr-only"
      announcement.setAttribute("aria-live", "polite")
      announcement.textContent = message
      document.body.appendChild(announcement)
  
      // Remove after announcement
      setTimeout(() => {
        document.body.removeChild(announcement)
      }, 1000)
    }
  
    // Enhance navigation buttons with aria labels
    const prevBtn = lightbox.querySelector(".mgwpp-prev")
    const nextBtn = lightbox.querySelector(".mgwpp-next")
    const closeBtn = lightbox.querySelector(".mgwpp-close")
  
    if (prevBtn) prevBtn.setAttribute("aria-label", "Previous image")
    if (nextBtn) nextBtn.setAttribute("aria-label", "Next image")
    if (closeBtn) closeBtn.setAttribute("aria-label", "Close lightbox")
  
    // Event listeners
    document.addEventListener("click", (e) => {
      const item = e.target.closest(".mgwpp-gallery-item")
      if (item) {
        e.preventDefault()
        openLightbox(items.indexOf(item))
      }
  
      if (e.target.classList.contains("mgwpp-close")) {
        closeLightbox()
      }
  
      // Close when clicking on the background (but not on the image or controls)
      if (e.target === lightbox || e.target.classList.contains("mgwpp-lightbox-content")) {
        closeLightbox()
      }
  
      if (e.target.classList.contains("mgwpp-prev")) navigate(-1)
      if (e.target.classList.contains("mgwpp-next")) navigate(1)
    })
  
    document.addEventListener("keydown", (e) => {
      if (!lightbox.classList.contains("active")) return
  
      if (e.key === "Escape") closeLightbox()
      if (e.key === "ArrowLeft") navigate(-1)
      if (e.key === "ArrowRight") navigate(1)
    })
  
    // Touch swipe support
    let touchStartX = 0
    let touchEndX = 0
  
    lightbox.addEventListener(
      "touchstart",
      (e) => {
        touchStartX = e.changedTouches[0].screenX
      },
      { passive: true },
    )
  
    lightbox.addEventListener(
      "touchend",
      (e) => {
        touchEndX = e.changedTouches[0].screenX
        handleSwipe()
      },
      { passive: true },
    )
  
    function handleSwipe() {
      const swipeThreshold = 50
      if (touchEndX < touchStartX - swipeThreshold) {
        // Swipe left, go to next
        navigate(1)
      }
      if (touchEndX > touchStartX + swipeThreshold) {
        // Swipe right, go to previous
        navigate(-1)
      }
    }
  })
  
  