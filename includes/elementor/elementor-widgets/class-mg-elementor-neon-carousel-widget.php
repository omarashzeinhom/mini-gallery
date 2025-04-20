<?php

if (!defined('ABSPATH')) {
    exit;
}

class MG_Elementor_Neon_Carousel extends \Elementor\Widget_Base
{

    public function get_script_depends()
    {
        return ['mgwpp-neon-carousel-js'];
    }
    
    public function get_style_depends()
    {
        return ['mgwpp-neon-carousel-styles'];
    }
    
    // Get widget name
    public function get_name()
    {
        return 'mg_neon_carousel';
    }

    // Get widget title
    public function get_title()
    {
        return __('Mini Gallery Neon Carousel', 'mini-gallery');
    }

    // Get widget icon (Elementor predefined icon)
    public function get_icon()
    {
        return 'eicon-slider-album';
    }

    // Get widget categories
    public function get_categories()
    {
        return ['minigallery'];
    }

    // Register widget controls (settings)
    protected function _register_controls()
    {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'mini-gallery'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // Gallery selection
        $this->add_control(
            'gallery_id',
            [
                'label' => __('Select Gallery', 'mini-gallery'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $this->get_galleries(),
                'default' => '',
            ]
        );

        // Autoplay control
        $this->add_control(
            'autoplay',
            [
                'label' => __('Autoplay', 'mini-gallery'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'mini-gallery'),
                'label_off' => __('No', 'mini-gallery'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        // Autoplay speed control
        $this->add_control(
            'autoplay_speed',
            [
                'label' => __('Autoplay Speed (ms)', 'mini-gallery'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 5000,
                'condition' => ['autoplay' => 'yes'],
            ]
        );

        // Display navigation dots control
        $this->add_control(
            'show_dots',
            [
                'label' => __('Show Navigation Dots', 'mini-gallery'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        // Show image previews control
        $this->add_control(
            'show_previews',
            [
                'label' => __('Show Image Previews', 'mini-gallery'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        // Zoom effect control
        $this->add_control(
            'zoom_effect',
            [
                'label' => __('Zoom Effect', 'mini-gallery'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();

        // Style section
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Style', 'mini-gallery'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Neon primary color control
        $this->add_control(
            'neon_primary_color',
            [
                'label' => __('Neon Primary Color', 'mini-gallery'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#00f3ff',
                'selectors' => ['{{WRAPPER}}' => '--neon-primary: {{VALUE}};'],
            ]
        );

        // Neon secondary color control
        $this->add_control(
            'neon_secondary_color',
            [
                'label' => __('Neon Secondary Color', 'mini-gallery'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ff007f',
                'selectors' => ['{{WRAPPER}}' => '--neon-secondary: {{VALUE}};'],
            ]
        );

        // Background color control
        $this->add_control(
            'background_color',
            [
                'label' => __('Background Color', 'mini-gallery'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#0a0a0a',
                'selectors' => ['{{WRAPPER}}' => '--dark-bg: {{VALUE}};'],
            ]
        );

        // Slide height control
        $this->add_responsive_control(
            'slide_height',
            [
                'label' => __('Slide Height', 'mini-gallery'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['vh', 'px'],
                'range' => [
                    'vh' => ['min' => 30, 'max' => 100],
                    'px' => ['min' => 300, 'max' => 2000],
                ],
                'default' => ['unit' => 'vh', 'size' => 93],
                'selectors' => ['{{WRAPPER}} .neon-slider' => 'height: {{SIZE}}{{UNIT}};'],
            ]
        );

        // Overlay opacity control
        $this->add_control(
            'overlay_opacity',
            [
                'label' => __('Overlay Opacity', 'mini-gallery'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'range' => ['px' => ['min' => 0, 'max' => 1, 'step' => 0.1]],
                'default' => ['size' => 0.9],
                'selectors' => [
                    '{{WRAPPER}} .neon-slide::before' => 'opacity: {{SIZE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Typography section
        $this->start_controls_section(
            'typography_section',
            [
                'label' => __('Typography', 'mini-gallery'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Title color control
        $this->add_control(
            'title_color',
            [
                'label' => __('Title Color', 'mini-gallery'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => ['{{WRAPPER}} .neon-title' => 'color: {{VALUE}};'],
            ]
        );

        // Title typography control
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => __('Title Typography', 'mini-gallery'),
                'selector' => '{{WRAPPER}} .neon-title',
            ]
        );

        $this->end_controls_section();
    }

    // Render widget
    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $gallery_id = $settings['gallery_id'];
        
        if (!$gallery_id) {
            echo esc_html__('Please select a gallery.', 'mini-gallery');
            return;
        }
    
        $images = get_attached_media('image', $gallery_id);
    
        // Set fallback values for neon colors if not set
        $neon_primary = isset($settings['neon_primary_color']) ? $settings['neon_primary_color'] : '#00f3ff';  // default neon primary color
        $neon_secondary = isset($settings['neon_secondary_color']) ? $settings['neon_secondary_color'] : '#ff007f';  // default neon secondary color
    
        // Pass settings to the renderer
        echo '<div class="mg-neon-carousel" data-settings="'.esc_attr(
            wp_json_encode(
                [
                'autoplay' => $settings['autoplay'] === 'yes',
                'autoplay_speed' => $settings['autoplay_speed'],
                'show_dots' => $settings['show_dots'] === 'yes',
                'show_previews' => $settings['show_previews'] === 'yes',
                'zoom_effect' => $settings['zoom_effect'] === 'yes',
                'neon_primary' => $neon_primary,
                'neon_secondary' => $neon_secondary
                ]
            )
        ).'">';
    
        echo wp_kses_post(MGWPP_Neon_Carousel::render($gallery_id, $images));
        echo '</div>';
    }
    
    // Get available galleries
    private function get_galleries()
    {
        $galleries = get_posts(
            [
            'post_type' => 'mgwpp_soora',
            'numberposts' => 100,
            'post_status' => 'publish',
            ]
        );
    
        $options = ['' => __('Select Gallery', 'mini-gallery')];
    
        foreach ($galleries as $gallery) {
            if (!$gallery instanceof WP_Post) {
                continue;
            }
            $options[$gallery->ID] = esc_html($gallery->post_title);
        }
    
        return $options;
    }
    // Enqueue editor scripts
    public function enqueue_editor_scripts()
    {
        wp_enqueue_style(
            'mg-neon-carousel-editor',
            plugin_dir_url(__FILE__) . 'admin/css/editor.css',
            [],
            filemtime(plugin_dir_path(__FILE__) . 'admin/css/editor.css')
        );
    }
}
