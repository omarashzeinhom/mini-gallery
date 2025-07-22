<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery Modules - Mini Gallery</title>
    <style>
        /* Base Styles */
        :root {
            --mgwpp-primary-color: #1dc1dc;
            --mgwpp-primary-hover: #1aa7c0;
            --mgwpp-border-radius-sm: 4px;
            --mgwpp-border-radius-md: 8px;
            --mgwpp-border-radius-lg: 16px;
            --mgwpp-transition-speed: 0.3s;
            --mgwpp-icon-bg-light: rgba(29, 193, 220, 0.1);
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif;
            background-color: #f0f0f1;
            color: #1d2327;
            line-height: 1.5;
            padding: 20px;
        }
        
        .mgwpp-modules-view {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        h1 {
            font-size: 23px;
            font-weight: 600;
            margin: 0 0 25px 0;
            padding: 9px 0 4px;
            line-height: 1.3;
        }
        
        h2 {
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 15px 0;
            color: #23282d;
        }
        
        /* Gallery Types Header */
        .mgwpp-gallery-types-header {
            background: #fff;
            border-radius: var(--mgwpp-border-radius-md);
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .mgwpp-enabled-gallery-types {
            display: grid;
            gap: 24px;
        }
        
        .mgwpp-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .mgwpp-stat-card {
            display: flex;
            align-items: center;
            background: #f1f1f1;
            border-radius: var(--mgwpp-border-radius-lg);
            padding: 15px;
            min-height: 80px;
            font-size: 13px;
            gap: 12px;
            border: 0.5px solid rgba(190, 189, 189, 0.418);
            color: #333;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
            position: relative;
        }
        
        .mgwpp-stat-card:hover {
            background: #e9e9e9;
            transform: translateY(-1px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .mgwpp-stat-card-icon {
            width: 48px;
            height: 48px;
            object-fit: contain;
        }
        
        /* Module Cards */
        .mgwpp-modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .mgwpp-modules-grid.loaded {
            opacity: 1;
        }
        
        .mgwpp-module-card {
            padding: 0;
            background: #fff;
            border-radius: var(--mgwpp-border-radius-md);
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: all var(--mgwpp-transition-speed) ease;
            position: relative;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        /* Active/Inactive States */
        .mgwpp-module-card.active {
            border-left: 4px solid var(--mgwpp-primary-color);
            opacity: 1;
        }
        
        .mgwpp-module-card.inactive {
            border-left: 4px solid #ccc;
            opacity: 0.7;
        }
        
        /* Card Header */
        .module-header {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            position: relative;
        }
        
        .module-icon {
            width: 64px;
            height: 64px;
            margin-right: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--mgwpp-icon-bg-light);
            border-radius: var(--mgwpp-border-radius-sm);
            padding: 4px;
            flex-shrink: 0;
        }
        
        .module-icon img {
            max-width: 100%;
            max-height: 100%;
        }
        
        .module-header h3 {
            margin: 0;
            font-size: 15px;
            font-weight: 600;
            flex-grow: 1;
        }
        
        .module-actions {
            display: flex;
            align-items: center;
        }
        
        /* Module Metadata */
        .module-meta {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #777;
            padding: 0 15px;
        }
        
        .module-description {
            padding: 15px;
            font-size: 13px;
            line-height: 1.5;
            color: #444;
        }
        
        /* Modern Toggle Switch */
        .mgwpp-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
            z-index: 10;
        }
        
        .mgwpp-switch input {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            opacity: 0;
            z-index: 12;
            margin: 0;
            cursor: pointer;
        }
        
        .mgwpp-switch-slider {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            border-radius: var(--mgwpp-border-radius-lg);
            transition: var(--mgwpp-transition-speed);
            z-index: 11;
        }
        
        .mgwpp-switch-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            border-radius: 50%;
            transition: var(--mgwpp-transition-speed);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            z-index: 11;
        }
        
        /* Active state */
        .mgwpp-switch input:checked + .mgwpp-switch-slider {
            background-color: var(--mgwpp-primary-color);
        }
        
        .mgwpp-switch input:checked + .mgwpp-switch-slider:before {
            transform: translateX(26px);
        }
        
        /* Card hover effect */
        .mgwpp-module-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        
        /* Performance Metrics */
        .mgwpp-performance-metrics {
            margin-top: 40px;
            padding-top: 40px;
            border-top: 2px solid #f0f0f0;
        }
        
        .performance-metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .metric-card {
            background: #fff;
            border-radius: var(--mgwpp-border-radius-md);
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .metric-card h3 {
            font-size: 16px;
            margin-bottom: 15px;
            color: #23282d;
        }
        
        .metric-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--mgwpp-primary-color);
            margin-bottom: 5px;
        }
        
        .metric-size {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .module-asset-details {
            background: #fff;
            border-radius: var(--mgwpp-border-radius-md);
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        
        .module-asset-details h3 {
            margin-bottom: 15px;
        }
        
        .wp-list-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .wp-list-table th,
        .wp-list-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .wp-list-table th {
            font-weight: 600;
        }
        
        .wp-list-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .module-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .module-status.enabled {
            background-color: #d1f0f5;
            color: #1a9caf;
        }
        
        .module-status.disabled {
            background-color: #f0f0f1;
            color: #757575;
        }
        
        /* Save Button */
        .mgwpp-save-wrapper {
            margin: 20px 0 0;
            text-align: right;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 4px;
            border-left: 4px solid #2271b1;
        }
        
        #mgwpp-save-settings {
            font-size: 16px;
            padding: 8px 20px;
            height: auto;
            background: #2271b1;
            border-color: #2271b1;
            color: #fff;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        #mgwpp-save-settings:hover {
            background: #135e96;
            border-color: #135e96;
        }
        
        #mgwpp-save-settings:disabled {
            background: #a7aaad;
            border-color: #a7aaad;
            cursor: not-allowed;
        }
        
        /* Loading state */
        .mgwpp-loading .mgwpp-switch-slider {
            opacity: 0.6;
        }
        
        .mgwpp-loading .mgwpp-switch-slider:before {
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .mgwpp-stats-grid,
            .mgwpp-modules-grid {
                grid-template-columns: 1fr;
            }
            
            .module-header {
                flex-direction: column;
                text-align: center;
            }
            
            .module-icon {
                margin-right: 0;
                margin-bottom: 10px;
            }
            
            .module-actions {
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="mgwpp-modules-view">
        <h1>Gallery Modules</h1>
        
        <div class="mgwpp-gallery-types-header">
            <h2>Enabled Gallery Types</h2>
            <div class="mgwpp-enabled-gallery-types">
                <div class="mgwpp-stats-grid">
                    <div class="mgwpp-stat-card" data-module="single_carousel">
                        <img src="https://via.placeholder.com/48" alt="Single Carousel" class="mgwpp-stat-card-icon">
                        <span>Single Carousel</span>
                        <div class="mgwpp-switch">
                            <input type="checkbox" checked disabled>
                            <span class="mgwpp-switch-slider"></span>
                        </div>
                    </div>
                    <div class="mgwpp-stat-card" data-module="multi_carousel">
                        <img src="https://via.placeholder.com/48" alt="Multi Carousel" class="mgwpp-stat-card-icon">
                        <span>Multi Carousel</span>
                        <div class="mgwpp-switch">
                            <input type="checkbox" checked disabled>
                            <span class="mgwpp-switch-slider"></span>
                        </div>
                    </div>
                    <div class="mgwpp-stat-card" data-module="grid">
                        <img src="https://via.placeholder.com/48" alt="Grid" class="mgwpp-stat-card-icon">
                        <span>Grid</span>
                        <div class="mgwpp-switch">
                            <input type="checkbox" checked disabled>
                            <span class="mgwpp-switch-slider"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mgwpp-save-wrapper">
                <button id="mgwpp-save-settings" class="button button-primary">
                    Save All Changes
                </button>
            </div>
        </div>
        
        <div class="mgwpp-modules-grid">
            <div class="mgwpp-module-card active" data-module="single_carousel">
                <div class="module-header">
                    <div class="module-icon">
                        <img src="https://via.placeholder.com/64" alt="Single Carousel">
                    </div>
                    <div class="module-info">
                        <h3>Single Carousel</h3>
                        <div class="module-meta">
                            <span class="version">1.2.0</span>
                            <span class="author">Mini Gallery Team</span>
                        </div>
                    </div>
                    <div class="module-actions">
                        <label class="mgwpp-switch">
                            <input type="checkbox" class="mgwpp-module-toggle" name="mgwpp_enabled_sub_modules[]" value="single_carousel" checked>
                            <span class="mgwpp-switch-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="module-description">
                    Display a single row of gallery items in a carousel format with smooth transitions.
                </div>
            </div>
            
            <div class="mgwpp-module-card active" data-module="multi_carousel">
                <div class="module-header">
                    <div class="module-icon">
                        <img src="https://via.placeholder.com/64" alt="Multi Carousel">
                    </div>
                    <div class="module-info">
                        <h3>Multi Carousel</h3>
                        <div class="module-meta">
                            <span class="version">1.1.5</span>
                            <span class="author">Mini Gallery Team</span>
                        </div>
                    </div>
                    <div class="module-actions">
                        <label class="mgwpp-switch">
                            <input type="checkbox" class="mgwpp-module-toggle" name="mgwpp_enabled_sub_modules[]" value="multi_carousel" checked>
                            <span class="mgwpp-switch-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="module-description">
                    Display multiple rows of gallery items in a carousel format with advanced controls.
                </div>
            </div>
            
            <div class="mgwpp-module-card active" data-module="grid">
                <div class="module-header">
                    <div class="module-icon">
                        <img src="https://via.placeholder.com/64" alt="Grid">
                    </div>
                    <div class="module-info">
                        <h3>Grid</h3>
                        <div class="module-meta">
                            <span class="version">1.3.2</span>
                            <span class="author">Mini Gallery Team</span>
                        </div>
                    </div>
                    <div class="module-actions">
                        <label class="mgwpp-switch">
                            <input type="checkbox" class="mgwpp-module-toggle" name="mgwpp_enabled_sub_modules[]" value="grid" checked>
                            <span class="mgwpp-switch-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="module-description">
                    Display gallery items in a responsive grid layout with adjustable columns.
                </div>
            </div>
            
            <div class="mgwpp-module-card inactive" data-module="mega_slider">
                <div class="module-header">
                    <div class="module-icon">
                        <img src="https://via.placeholder.com/64" alt="Mega Slider">
                    </div>
                    <div class="module-info">
                        <h3>Mega Slider</h3>
                        <div class="module-meta">
                            <span class="version">1.0.8</span>
                            <span class="author">Mini Gallery Team</span>
                        </div>
                    </div>
                    <div class="module-actions">
                        <label class="mgwpp-switch">
                            <input type="checkbox" class="mgwpp-module-toggle" name="mgwpp_enabled_sub_modules[]" value="mega_slider">
                            <span class="mgwpp-switch-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="module-description">
                    Display gallery items in a large, full-width slider with featured items.
                </div>
            </div>
            
            <div class="mgwpp-module-card inactive" data-module="lightbox">
                <div class="module-header">
                    <div class="module-icon">
                        <img src="https://via.placeholder.com/64" alt="Lightbox">
                    </div>
                    <div class="module-info">
                        <h3>Lightbox</h3>
                        <div class="module-meta">
                            <span class="version">1.4.0</span>
                            <span class="author">Mini Gallery Team</span>
                        </div>
                    </div>
                    <div class="module-actions">
                        <label class="mgwpp-switch">
                            <input type="checkbox" class="mgwpp-module-toggle" name="mgwpp_enabled_sub_modules[]" value="lightbox">
                            <span class="mgwpp-switch-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="module-description">
                    Add lightbox functionality to your gallery images for enhanced viewing.
                </div>
            </div>
        </div>
        
        <div class="mgwpp-performance-metrics">
            <h2>Performance Overview</h2>
            <div class="performance-metrics-grid">
                <div class="metric-card">
                    <h3>Active Modules</h3>
                    <div class="metric-value">3</div>
                    <div class="metric-size">124.5 KB</div>
                    <div class="metric-files">18 files</div>
                </div>
                
                <div class="metric-card">
                    <h3>Inactive Modules</h3>
                    <div class="metric-value">2</div>
                    <div class="metric-size">86.3 KB</div>
                    <div class="metric-files">12 files</div>
                </div>
                
                <div class="metric-card">
                    <h3>Performance Savings</h3>
                    <div class="metric-value">41%</div>
                    <div class="metric-size">86.3 KB</div>
                    <div class="metric-description">of total assets</div>
                </div>
            </div>
            
            <div class="module-asset-details">
                <h3>Module Details</h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Module</th>
                            <th>Status</th>
                            <th>Files</th>
                            <th>Size</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Single Carousel</td>
                            <td><span class="module-status enabled">Enabled</span></td>
                            <td>6</td>
                            <td>42.5 KB</td>
                        </tr>
                        <tr>
                            <td>Multi Carousel</td>
                            <td><span class="module-status enabled">Enabled</span></td>
                            <td>7</td>
                            <td>48.2 KB</td>
                        </tr>
                        <tr>
                            <td>Grid</td>
                            <td><span class="module-status enabled">Enabled</span></td>
                            <td>5</td>
                            <td>33.8 KB</td>
                        </tr>
                        <tr>
                            <td>Mega Slider</td>
                            <td><span class="module-status disabled">Disabled</span></td>
                            <td>8</td>
                            <td>54.7 KB</td>
                        </tr>
                        <tr>
                            <td>Lightbox</td>
                            <td><span class="module-status disabled">Disabled</span></td>
                            <td>4</td>
                            <td>31.6 KB</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize the modules grid
            document.querySelector('.mgwpp-modules-grid').classList.add('loaded');
            
            // Module toggle functionality
            const toggleButtons = document.querySelectorAll('.mgwpp-module-toggle');
            const saveButton = document.getElementById('mgwpp-save-settings');
            
            toggleButtons.forEach(button => {
                button.addEventListener('change', function() {
                    const moduleCard = this.closest('.mgwpp-module-card');
                    const moduleSlug = this.value;
                    const isActive = this.checked;
                    
                    toggleModule(moduleSlug, isActive, moduleCard);
                });
            });
            
            // Save button functionality
            saveButton.addEventListener('click', function() {
                saveAllSettings();
            });
            
            // Toggle module function
            function toggleModule(slug, status, card) {
                // Add loading state
                const switchContainer = card.querySelector('.mgwpp-switch');
                switchContainer.classList.add('mgwpp-loading');
                
                // Simulate API call
                setTimeout(() => {
                    // Update UI
                    card.classList.toggle('active', status);
                    card.classList.toggle('inactive', !status);
                    
                    // Remove loading state
                    switchContainer.classList.remove('mgwpp-loading');
                    
                    // Update performance metrics (in a real app, this would come from the API)
                    updatePerformanceMetrics();
                }, 800);
            }
            
            // Save all settings function
            function saveAllSettings() {
                saveButton.disabled = true;
                saveButton.textContent = 'Saving...';
                
                // Simulate API call
                setTimeout(() => {
                    // Show success
                    saveButton.textContent = 'Saved!';
                    
                    // Revert after delay
                    setTimeout(() => {
                        saveButton.textContent = 'Save All Changes';
                        saveButton.disabled = false;
                    }, 1500);
                }, 1200);
            }
            
            // Update performance metrics
            function updatePerformanceMetrics() {
                // In a real app, this would come from the API
                const activeModules = document.querySelectorAll('.mgwpp-module-toggle:checked').length;
                const inactiveModules = document.querySelectorAll('.mgwpp-module-toggle:not(:checked)').length;
                
                document.querySelector('.metric-card:nth-child(1) .metric-value').textContent = activeModules;
                document.querySelector('.metric-card:nth-child(2) .metric-value').textContent = inactiveModules;
                
                // Update module status in table
                document.querySelectorAll('.mgwpp-module-card').forEach(card => {
                    const moduleSlug = card.dataset.module;
                    const isActive = card.classList.contains('active');
                    const statusCell = document.querySelector(`.wp-list-table td:first-child:contains('${moduleSlug.replace('_', ' ')}')`).parentNode.querySelector('.module-status');
                    
                    if (statusCell) {
                        statusCell.textContent = isActive ? 'Enabled' : 'Disabled';
                        statusCell.className = 'module-status ' + (isActive ? 'enabled' : 'disabled');
                    }
                });
            }
            
            // Helper for case-insensitive :contains selector
            jQuery.expr[':'].contains = function(a, i, m) {
                return jQuery(a).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
            };
        });
    </script>
</body>
</html>