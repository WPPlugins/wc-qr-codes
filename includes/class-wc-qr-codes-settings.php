<?php

/**
 * WC Qr codes settings
 *
 * @author Bappa Mal
 */
if ( !class_exists('WCQRC_Settings' ) ):
class WCQRC_Settings {

    private $settings_api;

    function __construct($settings_api) {
        $this->settings_api = $settings_api;

        add_action( 'admin_init', array($this, 'admin_init') );
        add_action( 'admin_menu', array($this, 'admin_menu'), 60 );
    }

    function admin_init() {

        //set the settings
        $this->settings_api->set_sections( $this->get_settings_sections() );
        $this->settings_api->set_fields( $this->get_settings_fields() );

        //initialize settings
        $this->settings_api->admin_init();
    }

    function admin_menu() {
        add_submenu_page('woocommerce', __('QR Codes', 'wc-qr-codes'), __('QR Codes', 'wc-qr-codes'), 'manage_woocommerce', 'wcqrc_settings', array($this, 'plugin_page'));
    }

    function get_settings_sections() {
        $sections = array(
            array(
                'id'    => 'wcqrc_basics',
                'title' => __( 'QR Codes Settings', 'wc-qr-codes' )
            )
        );
        return $sections;
    }

    /**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
    function get_settings_fields() {
        $settings_fields = array(
            'wcqrc_basics' => array(
                array(
                    'name'    => 'qr_size',
                    'label'   => __( 'QR codes size', 'wc-qr-codes' ),
                    'desc'    => __( 'Select size of qr codes', 'wc-qr-codes' ),
                    'type'    => 'select',
                    'default' => '4',
                    'options' => array(
                        '1' => '1',
                        '2'  => '2',
                        '3' => '3',
                        '4'  => '4',
                        '5' => '5',
                        '6'  => '6',
                        '7' => '7',
                        '8'  => '8',
                        '9' => '9',
                        '10'  => '10',
                    )
                ),
                array(
                    'name'    => 'qr_frame_size',
                    'label'   => __( 'QR frame size', 'wc-qr-codes' ),
                    'desc'    => __( 'Select frame size for qr codes', 'wc-qr-codes' ),
                    'type'    => 'select',
                    'default' => '2',
                    'options' => array(
                        '2'  => '2',
                        '4'  => '4',
                        '6'  => '6',
                        '8'  => '8',
                        '10'  => '10',
                    )
                )
            )
        );

        return $settings_fields;
    }

    function plugin_page() {
        echo '<div class="wrap">';

        $this->settings_api->show_navigation();
        $this->settings_api->show_forms();

        echo '</div>';
    }

    /**
     * Get all the pages
     *
     * @return array page names with key value pairs
     */
    function get_pages() {
        $pages = get_pages();
        $pages_options = array();
        if ( $pages ) {
            foreach ($pages as $page) {
                $pages_options[$page->ID] = $page->post_title;
            }
        }

        return $pages_options;
    }

}
endif;
