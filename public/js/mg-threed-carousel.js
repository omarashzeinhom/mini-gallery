(function($) {
  'use strict';

  $(document).ready(function() {
    $('.mg-3d-carousel-container').each(function() {
      const container = $(this);
      const postId = container.data('post-id');
      const settings = window['mg3dSettings_' + postId] || {};

      const config = {
        radius: settings.radius || 240,
        autoRotate: settings.autoRotate !== false,
        rotateSpeed: settings.rotateSpeed || -10,
        imgWidth: settings.imgWidth || 250,
        imgHeight: settings.imgHeight || 250,
        bgMusic: settings.bgMusic || '',
        bgMusicControls: settings.bgMusicControls !== false
      };

      // DOM elements
      const dragContainer = container.find('.mg-drag-container');
      const spinContainer = container.find('.mg-spin-container');
      const items = spinContainer.find('.mg-3d-item');

      // State management
      let isDragging = false;
      let currentAngle = 0;
      let autoRotateInterval;
      
      // Initialize carousel
      function init() {
        setSizes();
        positionItems();
        setupEventListeners();
        //if(config.autoRotate) startAutoRotate();
      }

      // Set dynamic sizes
      function setSizes() {
        spinContainer.css({
          width: config.imgWidth + 'px',
          height: config.imgHeight + 'px'
        });
      }

      // Position items in 3D space
      function positionItems() {
        const totalItems = items.length;
        const angleStep = 360 / totalItems;
        
        items.each(function(index) {
          const angle = angleStep * index;
          $(this).css({
            transform: `rotateY(${angle}deg) translateZ(${config.radius}px)`
          });
        });
      }

      // Handle user interaction
      function setupEventListeners() {
        let startX = 0, startY = 0;
        
        // Mouse events
        container.on('mousedown', function(e) {
          e.preventDefault();
          startX = e.clientX;
          startY = e.clientY;
          isDragging = true;
        });

        $(document).on('mousemove', function(e) {
          if(!isDragging) return;
          const deltaX = e.clientX - startX;
          currentAngle += deltaX * 0.5;
          spinContainer.css('transform', `rotateY(${currentAngle}deg)`);
          startX = e.clientX;
        });

        $(document).on('mouseup', function() {
          isDragging = false;
        });

        // Touch events
        container.on('touchstart', function(e) {
          startX = e.touches[0].clientX;
          startY = e.touches[0].clientY;
        });

        container.on('touchmove', function(e) {
          const deltaX = e.touches[0].clientX - startX;
          currentAngle += deltaX * 0.5;
          spinContainer.css('transform', `rotateY(${currentAngle}deg)`);
          startX = e.touches[0].clientX;
        });

        // Window resize
        let resizeTimeout;
        $(window).on('resize', function() {
          clearTimeout(resizeTimeout);
          resizeTimeout = setTimeout(() => {
            config.radius = Math.min(window.innerWidth * 0.3, 300);
            positionItems();
          }, 100);
        });
      }

      // Auto-rotation system
      autoRotateInterval = setInterval(() => {
        currentAngle += (config.rotateSpeed * 0.01);
        spinContainer.css('transform', `rotateY(${currentAngle}deg)`);
      }, 30);
      
      // Initialization
      init();
    });
  });
})(jQuery);