jQuery(document).ready(function($) {
    // =============================================
    // Gallery Preview Functionality
    // =============================================
    $('#gallery_type').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var previewImg = selectedOption.data('image');
        var demoUrl = selectedOption.data('demo');
        
        if (previewImg) {
            $('#preview_img').attr('src', previewImg);
            $('#preview_demo').attr('href', demoUrl);
            $('#gallery_preview').show();
        } else {
            $('#gallery_preview').hide();
        }
    });

    // =============================================
    // Gallery Form Submission (with file upload)
    // =============================================
    $('#mgwpp_galleries_content form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var formData = new FormData(this);
        var notice = $('#mgwpp-gallery-notice');
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                notice.hide().removeClass('success error');
                form.find('input[type="submit"]').prop('disabled', true).val('Uploading...');
            },
            success: function(response) {
                // Show success message
                notice.addClass('success').html(`
                    <p>Gallery created successfully!</p>
                    <p>You can view it in the "Existing Galleries" section below.</p>
                `).show();
                
                // Reset form
                form[0].reset();
                form.find('input[type="submit"]').prop('disabled', false).val('Upload Images');
                
                // Hide preview if shown
                $('#gallery_preview').hide();
                
                // Refresh galleries list
                location.reload(); // Simple solution - reload the page
                
                // Scroll to notice
                $('html, body').animate({
                    scrollTop: notice.offset().top - 20
                }, 500);
            },
            error: function(xhr) {
                var errorMsg = 'An error occurred while creating the gallery.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    errorMsg = xhr.responseText;
                }
                
                notice.addClass('error').html(`
                    <p>${errorMsg}</p>
                    <p>Please try again or check your file selections.</p>
                `).show();
                
                form.find('input[type="submit"]').prop('disabled', false).val('Upload Images');
                
                $('html, body').animate({
                    scrollTop: notice.offset().top - 20
                }, 500);
            }
        });
    });

    // =============================================
    // Album Form Submission
    // =============================================
    $('#mgwpp_albums_content form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var notice = $('#mgwpp-album-notice');
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            beforeSend: function() {
                notice.hide().removeClass('success error');
                form.find('input[type="submit"]').prop('disabled', true).val('Creating...');
            },
            success: function(response) {
                notice.addClass('success').html(`
                    <p>Album created successfully!</p>
                    <p>You can view it in the "Existing Albums" section below.</p>
                `).show();
                
                form[0].reset();
                form.find('input[type="submit"]').prop('disabled', false).val('Create Album');
                
                // Refresh albums list
                location.reload();
                
                $('html, body').animate({
                    scrollTop: notice.offset().top - 20
                }, 500);
            },
            error: function(xhr) {
                var errorMsg = 'An error occurred while creating the album.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    errorMsg = xhr.responseText;
                }
                
                notice.addClass('error').html(`
                    <p>${errorMsg}</p>
                    <p>Please check your selections and try again.</p>
                `).show();
                
                form.find('input[type="submit"]').prop('disabled', false).val('Create Album');
                
                $('html, body').animate({
                    scrollTop: notice.offset().top - 20
                }, 500);
            }
        });
    });

    // =============================================
    // Theme Toggle Functionality
    // =============================================
    function toggleDashboardTheme() {
        $('body').toggleClass('dark');
        localStorage.setItem('mgwpp-theme', $('body').hasClass('dark') ? 'dark' : 'light');
        $('#theme-icon-moon').toggleClass('hidden');
        $('#theme-icon-sun').toggleClass('hidden');
    }

    // Initialize theme from localStorage
    if (localStorage.getItem('mgwpp-theme') === 'dark') {
        $('body').addClass('dark');
        $('#theme-icon-moon').addClass('hidden');
        $('#theme-icon-sun').removeClass('hidden');
    }

    window.toggleDashboardTheme = toggleDashboardTheme;
});