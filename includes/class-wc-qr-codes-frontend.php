<?php

/**
 * Frondend class
 */
class WCQRCodesFrentend {

    function __construct() {
        add_filter('woocommerce_product_tabs', array(&$this, 'wc_qr_codes_product_tab'));
    }

    /**
     * add new woocommerce tab for display qr codes
     * @param array $tabs
     * @return array
     */
    function wc_qr_codes_product_tab($tabs) {
        $tabs['test_tab'] = array(
            'title' => __('QR Code', 'wc-qr-codes'),
            'priority' => 50,
            'callback' => array(&$this, 'wc_qr_codes_product_tab_content')
        );

        return $tabs;
    }
    /**
     * Display QR code in product page
     * @global object $product
     */
    function wc_qr_codes_product_tab_content() {
        global $product;
        echo '<h2>'.__('QR Code', 'wc-qr-codes').'</h2>';
        if (get_wc_product_qr_code_src($product->get_id())) {
            echo '<div class="wc-qr-codes-container">';
            echo '<img class="wcqrc-qr-code-img" src="' . get_wc_product_qr_code_src($product->get_id()) . '" alt="QR Code" />';
            echo '</div>';
        }
    }
}
