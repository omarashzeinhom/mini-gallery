<?php
if ( ! defined( 'ABSPATH' ) ) exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class MG_Elementor_Gallery_Multi extends Widget_Base {

    public function get_name() {
        return 'mg_gallery_multi';
    }

    public function get_title() {
        return __( 'Mini Gallery Multi Carousel', 'mini-gallery' );
    }

    public function get_icon() {
        return 'eicon-carousel';
    }

    public function get_categories() {
        return [ 'minigallery' ];
    }

    protected function _register_controls() {

        // Content Controls
        $this->start_controls_section(
            'content_section',
            [
                'label' => __( 'Content', 'mini-gallery' ),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'gallery_id',
            [
                'label'   => __( 'Select Gallery', 'mini-gallery' ),
                'type'    => Controls_Manager::SELECT,
                'options' => $this->get_galleries(),
                'default' => '',
            ]
        );

        $this->add_control(
            'images_per_page',
            [
                'label'   => __( 'Images per Page', 'mini-gallery' ),
                'type'    => Controls_Manager::NUMBER,
                'default' => 6,
            ]
        );

        $this->add_control(
            'paged',
            [
                'label'   => __( 'Current Page', 'mini-gallery' ),
                'type'    => Controls_Manager::NUMBER,
                'default' => 1,
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings   = $this->get_settings_for_display();
        $gallery_id = $settings['gallery_id'];

        if ( ! $gallery_id ) {
            echo esc_html__( 'Please select a gallery.', 'mini-gallery' );
            return;
        }

        $images = get_attached_media( 'image', $gallery_id );
        if ( ! $images ) {
            echo esc_html__( 'No images found for this gallery.', 'mini-gallery' );
            return;
        }

        $images_per_page = intval( $settings['images_per_page'] );
        $paged           = intval( $settings['paged'] );
        $offset          = ( $paged - 1 ) * $images_per_page;
        $images_page     = array_slice( $images, $offset, $images_per_page );
        ?>
        <div id="mg-multi-carousel" class="mg-gallery multi-carousel" data-page="<?php echo esc_attr( $paged ); ?>">
            <?php foreach ( $images_page as $image ) : ?>
                <div class="mg-multi-carousel-slide">
                    <?php echo wp_get_attachment_image( $image->ID, 'medium', false, ['loading' => 'lazy'] ); ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

    private function get_galleries() {
        $galleries = get_posts( [
            'post_type'   => 'mgwpp_soora',
            'numberposts' => -1,
        ] );
        $options = [];
        foreach ( $galleries as $gallery ) {
            $options[ $gallery->ID ] = $gallery->post_title;
        }
        return $options;
    }
}
