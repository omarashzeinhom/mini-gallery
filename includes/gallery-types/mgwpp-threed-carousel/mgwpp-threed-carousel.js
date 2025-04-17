(function ($) {
  'use strict';

  $(document).ready(function () {
    $('.mg-3d-carousel-container').each(function () {
      const container = $(this);
      const postId = container.data('post-id');
      const settings = window['mg3dSettings_' + postId] || {};

      const config = {
        radius: settings.radius || 240,
        autoRotate: settings.autoRotate !== false,
        rotateSpeed: settings.rotateSpeed || -10,
        imgWidth: settings.imgWidth || 250,
        imgHeight: settings.imgHeight || 250,
        mobileScale: settings.mobileScale || 0.7,
        isTouch: ('ontouchstart' in window) || (navigator.maxTouchPoints > 0)
      };

      // DOM elements
      const spinContainer = container.find('.mg-spin-container');
      const items = spinContainer.find('.mg-3d-item');
      let currentAngle = 0;
      let isDragging = false;
      let startX = 0;
      let animationFrame;

      // Initialize carousel
      function init() {
        setSizes();
        positionItems();
        setupEventListeners();
        setupResponsive();
        if (config.autoRotate) startAutoRotate();
      }

      function setSizes() {
        spinContainer.css({
          width: config.imgWidth + 'px',
          height: config.imgHeight + 'px'
        });
      }

      function positionItems() {
        const totalItems = items.length;
        const angleStep = 360 / totalItems;
        const activeRadius = window.innerWidth <= 768 ?
          config.radius * config.mobileScale :
          config.radius;

        items.each(function (index) {
          const angle = angleStep * index;
          $(this).css({
            transform: `rotateY(${angle}deg) translateZ(${activeRadius}px)`
          });
        });
      }

      function setupEventListeners() {
        const eventType = config.isTouch ? 'touchstart' : 'mousedown';

        container.on(eventType, function (e) {
          e.preventDefault();
          startX = config.isTouch ? e.originalEvent.touches[0].clientX : e.clientX;
          isDragging = true;
          if (config.autoRotate) stopAutoRotate();
        });

        $(document).on(config.isTouch ? 'touchmove' : 'mousemove', handleMove);
        $(document).on(config.isTouch ? 'touchend' : 'mouseup', handleEnd);
      }

      function handleMove(e) {
        if (!isDragging) return;
        const currentX = config.isTouch ? e.originalEvent.touches[0].clientX : e.clientX;
        const deltaX = currentX - startX;
        currentAngle += deltaX * 0.5;
        updateRotation();
        startX = currentX;
      }

      function handleEnd() {
        isDragging = false;
        if (config.autoRotate) startAutoRotate();
      }

      function updateRotation() {
        spinContainer.css('transform',
          `translate(-50%, -50%) rotateX(-10deg) rotateY(${currentAngle}deg)`
        );
      }

      function startAutoRotate() {
        cancelAnimationFrame(animationFrame);
        animate();
      }

      function stopAutoRotate() {
        cancelAnimationFrame(animationFrame);
      }

      function animate() {
        currentAngle += config.rotateSpeed * 0.02;
        updateRotation();
        animationFrame = requestAnimationFrame(animate);
      }

      function setupResponsive() {
        $(window).on('resize', function () {
          positionItems();
          spinContainer.css('transform',
            `translate(-50%, -50%) rotateX(-10deg) rotateY(${currentAngle}deg)`
          );
        });
      }
      // Handle user interaction
      function setupEventListeners() {
        let startX = 0, startY = 0;

        // Mouse events
        container.on('mousedown', function (e) {
          e.preventDefault();
          startX = e.clientX;
          startY = e.clientY;
          isDragging = true;
        });

        $(document).on('mousemove', function (e) {
          if (!isDragging) return;
          const deltaX = e.clientX - startX;
          currentAngle += deltaX * 0.5;
          spinContainer.css('transform', `rotateY(${currentAngle}deg)`);
          startX = e.clientX;
        });

        $(document).on('mouseup', function () {
          isDragging = false;
        });

        // Touch events
        container.on('touchstart', function (e) {
          startX = e.touches[0].clientX;
          startY = e.touches[0].clientY;
        });

        container.on('touchmove', function (e) {
          const deltaX = e.touches[0].clientX - startX;
          currentAngle += deltaX * 0.5;
          spinContainer.css('transform', `rotateY(${currentAngle}deg)`);
          startX = e.touches[0].clientX;
        });

        // Window resize
        let resizeTimeout;
        $(window).on('resize', function () {
          clearTimeout(resizeTimeout);
          resizeTimeout = setTimeout(() => {
            config.radius = Math.min(window.innerWidth * 0.3, 300);
            positionItems();
          }, 100);
        });
      }


      init();
    });
  });
})(jQuery);