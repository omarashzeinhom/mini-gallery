// mg-admin-scripts.js
jQuery(document).ready(function ($) {
  // **************
  // INITIALIZATIONS
  // **************
  let mediaFrame;

  // Main initialization controller
  function initDashboard() {
    initColorPickers();
    initMediaUpload();
    initFormHandlers();
    initThemeSystem();
    initGalleryPreviews();
    initProNoticeDismissal();
  }

  // *****************
  // CORE FUNCTIONALITY
  // *****************

  // Color picker initialization
  function initColorPickers() {
    $('.color-picker').wpColorPicker();
  }

  // Media upload handler
  function initMediaUpload() {
    $('.mgwpp-media-upload').click(function (e) {
      e.preventDefault();
      handleMediaSelection();
    });
  }

  function handleMediaSelection() {
    if (mediaFrame) {
      mediaFrame.open();
      return;
    }

    mediaFrame = wp.media({
      title: mgwppMedia.text_title,
      button: { text: mgwppMedia.text_select },
      library: { type: 'image' },
      multiple: true
    });

    mediaFrame.on('select', processSelectedMedia);
    mediaFrame.open();
  }

  function processSelectedMedia() {
    const attachments = mediaFrame.state().get('selection').toJSON();
    const mediaIds = attachments.map(attachment => attachment.id);

    $('#selected_media').val(mediaIds.join(','));
    updateMediaPreview(attachments);
  }

  function updateMediaPreview(attachments) {
    const preview = $('.mgwpp-media-preview').empty();
    attachments.forEach(attachment => {
      preview.append(`
                <div class="media-thumbnail">
                    <img src="${attachment.sizes.thumbnail.url}" 
                         alt="${attachment.alt}" 
                         style="width: 80px; height: 80px;">
                </div>
            `);
    });
  }

  // **************
  // FORM MANAGEMENT
  // **************
  function initFormHandlers() {
    $('form').on('submit', handleFormSubmission);
  }

  function handleFormSubmission(e) {
    e.preventDefault();
    const form = $(this);
    const notice = $('#mgwpp-gallery-notice');

    $.ajax({
      url: form.attr('action'),
      type: 'POST',
      data: form.serialize(),
      beforeSend: () => resetNotice(notice),
      success: (response) => showSuccess(notice),
      error: (xhr) => showError(xhr, notice)
    });
  }

  function resetNotice(notice) {
    notice.hide().removeClass('success error');
  }

  function showSuccess(notice) {
    notice.addClass('success')
      .html(mgwppMedia.gallery_success)
      .show();
    setTimeout(() => {
      window.location.href = 'admin.php?page=mgwpp_galleries';
    }, 1500);
  }

  function showError(xhr, notice) {
    const errorMsg = xhr.responseJSON?.message ||
      xhr.responseText ||
      mgwppMedia.generic_error;
    notice.addClass('error').html(errorMsg).show();
  }

  // ************
  // THEME SYSTEM
  // ************
  function initThemeSystem() {
    window.toggleDashboardTheme = handleThemeToggle;
    applySavedTheme();
  }

  function handleThemeToggle() {
    $('body').toggleClass('dark');
    persistThemeState();
    toggleThemeIcons();
  }

  function persistThemeState() {
    localStorage.setItem(
      'mgwpp-theme',
      $('body').hasClass('dark') ? 'dark' : 'light'
    );
  }

  function toggleThemeIcons() {
    $('#theme-icon-moon, #theme-icon-sun').toggleClass('hidden');
  }

  function applySavedTheme() {
    if (localStorage.getItem('mgwpp-theme') === 'dark') {
      $('body').addClass('dark');
      $('#theme-icon-moon').addClass('hidden');
      $('#theme-icon-sun').removeClass('hidden');
    }
  }

  // ********************
  // GALLERY PREVIEW SYSTEM
  // ********************
  function initGalleryPreviews() {
    $('#gallery_type').change(updateGalleryPreview);
  }

  function updateGalleryPreview() {
    const option = $(this).find('option:selected');
    $('#preview_img').attr('src', option.data('image') || '');
    $('#preview_demo').attr('href', option.data('demo') || '#');
    $('#gallery_preview').toggle(!!option.data('image'));
  }

  // **********************
  // PRO NOTICE DISMISSAL
  // **********************
  function initProNoticeDismissal() {
    $(document).on('click', '.mg-pro-elements-notice .notice-dismiss', dismissProNotice);
  }

  function dismissProNotice() {
    $.post(ajaxurl, {
      action: 'mg_dismiss_pro_elements_notice'
    });
  }

  // ********************
  // BOOTSTRAP THE SYSTEM
  // ********************
  if (typeof wp !== 'undefined' && wp.media && $.fn.wpColorPicker) {
    initDashboard();
  } else {
    setTimeout(initDashboard, 500);
  }
});





// Theme Toggle
const themeToggle = document.getElementById('themeToggle');
const body = document.body;

// Check for saved theme preference or use system preference
const savedTheme = localStorage.getItem('mgwpp-theme');
if (savedTheme === 'dark') {
  body.classList.add('dark-mode');
} else if (savedTheme === 'light') {
  body.classList.remove('dark-mode');
} else {
  // Check system preference
  if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
    body.classList.add('dark-mode');
  }
}

// Toggle theme when button is clicked
themeToggle.addEventListener('click', () => {
  body.classList.toggle('dark-mode');

  // Save preference
  if (body.classList.contains('dark-mode')) {
    localStorage.setItem('mgwpp-theme', 'dark');
  } else {
    localStorage.setItem('mgwpp-theme', 'light');
  }
});

// Filter Buttons
const filterButtons = document.querySelectorAll('.mgwpp-filter-button');

filterButtons.forEach(button => {
  button.addEventListener('click', () => {
    // Remove active class from all buttons
    filterButtons.forEach(btn => btn.classList.remove('active'));

    // Add active class to clicked button
    button.classList.add('active');

    // Here you would filter the gallery items
    // For demo purposes, we'll just add a loading animation
    const loadingSpinner = document.querySelector('.mgwpp-loading');
    loadingSpinner.style.display = 'flex';

    setTimeout(() => {
      loadingSpinner.style.display = 'none';
    }, 800);
  });
});

// Lightbox Functionality
const galleryCards = document.querySelectorAll('.mgwpp-gallery-card');
const lightbox = document.getElementById('lightbox');
const lightboxImg = document.getElementById('lightboxImg');
const lightboxClose = document.getElementById('lightboxClose');
const lightboxPrev = document.getElementById('lightboxPrev');
const lightboxNext = document.getElementById('lightboxNext');

let currentImageIndex = 0;
const images = Array.from(document.querySelectorAll('.mgwpp-gallery-img')).map(img => img.src);

galleryCards.forEach((card, index) => {
  card.addEventListener('click', () => {
    currentImageIndex = index;
    lightboxImg.src = images[currentImageIndex];
    lightbox.classList.add('active');
    document.body.style.overflow = 'hidden'; // Prevent scrolling
  });
});

lightboxClose.addEventListener('click', () => {
  lightbox.classList.remove('active');
  document.body.style.overflow = ''; // Re-enable scrolling
});

lightboxPrev.addEventListener('click', () => {
  currentImageIndex = (currentImageIndex - 1 + images.length) % images.length;
  lightboxImg.src = images[currentImageIndex];
});

lightboxNext.addEventListener('click', () => {
  currentImageIndex = (currentImageIndex + 1) % images.length;
  lightboxImg.src = images[currentImageIndex];
});

// Close lightbox with escape key
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape' && lightbox.classList.contains('active')) {
    lightbox.classList.remove('active');
    document.body.style.overflow = '';
  }
});

// Search functionality
const searchInput = document.querySelector('.mgwpp-search-input');

searchInput.addEventListener('input', (e) => {
  const searchTerm = e.target.value.toLowerCase();
  const galleryTitles = document.querySelectorAll('.mgwpp-gallery-title');

  galleryTitles.forEach((title, index) => {
    const card = galleryCards[index];
    const titleText = title.textContent.toLowerCase();

    if (titleText.includes(searchTerm)) {
      card.style.display = 'block';
    } else {
      card.style.display = 'none';
    }
  });
});

// Add 3D tilt effect to cards
galleryCards.forEach(card => {
  card.addEventListener('mousemove', (e) => {
    const rect = card.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    const xRotation = ((y - rect.height / 2) / rect.height) * 10;
    const yRotation = ((x - rect.width / 2) / rect.width) * -10;

    card.style.transform = `perspective(1000px) rotateX(${xRotation}deg) rotateY(${yRotation}deg) scale3d(1.05, 1.05, 1.05)`;
  });

  card.addEventListener('mouseout', () => {
    card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) scale3d(1, 1, 1)';
  });
});

// Simulate loading more content
window.addEventListener('scroll', () => {
  const scrollPosition = window.scrollY + window.innerHeight;
  const pageHeight = document.body.scrollHeight;

  if (scrollPosition >= pageHeight - 200) {
    const loadingSpinner = document.querySelector('.mgwpp-loading');
    loadingSpinner.style.display = 'flex';

    // Simulate loading more content
    setTimeout(() => {
      loadingSpinner.style.display = 'none';
      // Here you would add more gallery items
    }, 1500);
  }
});





jQuery(document).ready(function ($) {
  const updateIcons = (isDark) => {
    // Update toggle button
    const toggleIcon = isDark ?
      `${mgwppData.pluginUrl}/includes/admin/images/icons/sun-icon.png` :
      `${mgwppData.pluginUrl}/includes/admin/images/icons/moon-icon.png`;
    $('#mgwpp-theme-toggle img').attr('src', toggleIcon);

    // Update all dynamic icons
    $('.mgwpp-icon').each(function () {
      const newSrc = isDark ?
        $(this).data('light-src') :
        $(this).data('dark-src');
      $(this).attr('src', newSrc);
    });
  };

  $('#mgwpp-theme-toggle').on('click', function (e) {
    e.preventDefault();
    const $container = $('.mgwpp-dashboard-container');
    const isDark = !$container.hasClass('mgwpp-dark');

    // Toggle UI immediately
    $container.toggleClass('mgwpp-dark', isDark);
    updateIcons(isDark);

    // Persist preference
    $.ajax({
      url: mgwppData.ajaxUrl,
      method: 'POST',
      data: {
        action: 'mgwpp_save_theme_preference',
        dark_mode: isDark,
        nonce: mgwppData.nonce
      },
      success: () => {
        document.cookie = `mgwpp_dark_mode=${isDark}; path=/; max-age=${365 * 24 * 60 * 60}`;
      }
    });
  });
});