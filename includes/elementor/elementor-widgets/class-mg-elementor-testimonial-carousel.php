<?php
if (! defined('ABSPATH')) exit;

class MG_Elementor_Testimonial_Carousel extends \Elementor\Widget_Base
{

    public function get_name()
    {
        return 'mgwpp_testimonial_carousel';
    }

    public function get_title()
    {
        return __('Testimonial Carousel', 'mini-gallery');
    }

    public function get_icon()
    {
        return 'eicon-testimonial';
    }

    public function get_categories()
    {
        return ['general'];
    }

    protected function _register_controls()
    {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'mini-gallery'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'testimonial_count',
            [
                'label' => __('Number of Testimonials', 'mini-gallery'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 5
            ]
        );

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

        $this->add_control(
            'interval',
            [
                'label' => __('Autoplay Interval (ms)', 'mini-gallery'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 1000,
                'max' => 10000,
                'step' => 500,
                'default' => 5000,
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        echo do_shortcode('[mgwpp_testimonial_carousel autoplay="' . esc_attr($settings['autoplay']) . '" interval="' . esc_attr($settings['interval']) . '"]');
    }

    protected function _content_template() {}
}

// Register the widget with Elementor
add_action('elementor/widgets/widgets_registered', function () {
    \Elementor\Plugin::instance()->widgets_manager->register(new MG_Elementor_Testimonial_Carousel);
});
