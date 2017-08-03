<?php

/**
 * Admin class
 */
class WCQRCodesAdmin {

    function __construct() {
        add_action('add_meta_boxes', array($this, 'add_qr_metabox'), 30);
        add_action('edit_post', array($this, 'save_product_qr_code'), 10, 2);
        add_action('before_delete_post', array($this, 'delete_associated_qr_code'), 10, 1);
        add_action('admin_enqueue_scripts', array(&$this, 'enqueue_admin_script'), 30);
    }

    /**
     * Add QR Code Metabox in product page
     * @global type $WooCommerceQrCodes
     */
    public function add_qr_metabox() {
        add_meta_box('qr-product-metabox', __('QR Code', 'wc-qr-codes'), array($this, 'qr_product_metabox_callback'), 'product', 'side', 'high');
    }

    /**
     * QR Code metabox callback function
     * @param object $product
     */
    public function qr_product_metabox_callback($product) {
        $is_qr_code_exist = get_post_meta($product->ID, '_is_qr_code_exist', true);
        if (!empty($is_qr_code_exist)) {
            $product_qr_code = get_post_meta($product->ID, '_product_qr_code', true);
            if (!empty($product_qr_code) && file_exists(WCQRC_QR_IMAGE_DIR . $product_qr_code)) {
                echo '<button type="button" data-product_id="' . $product->ID . '" class="wcqrc-refresh button-primary dashicons-before dashicons-update"></button>';
                echo '<div class="product-qr-code-container">';
                echo '<img class="product-qr-code-img" src="' . WCQRC_QR_IMAGE_URL . $product_qr_code . '" alt="QR Code" />';
                echo '</div>';
            } else {
                delete_post_meta($product->ID, '_is_qr_code_exist');
                delete_post_meta($product->ID, '_product_qr_code');
            }
        }
    }

    /**
     * Save generated QR code 
     * @global type $WooCommerceQrCodes
     * @param type $post_id
     */
    public function save_product_qr_code($post_id, $post) {
        global $WooCommerceQrCodes;
        if ($post->post_type == 'product') {
            $is_qr_code_exist = get_post_meta($post_id, '_is_qr_code_exist', true);
            if (empty($is_qr_code_exist)) {
                $permalink = apply_filters('wcqrc_product_permalink', get_permalink($post_id), $post_id) ;
                $image_name = time() . '_' . $post_id . '.png';
                $qr_size = intval($WooCommerceQrCodes->settings_api->get_option('qr_size', 'wcqrc_basics', 4));
                $qr_frame_size = intval($WooCommerceQrCodes->settings_api->get_option('qr_frame_size', 'wcqrc_basics', 2));
                $WooCommerceQrCodes->QRcode->png(esc_url($permalink), WCQRC_QR_IMAGE_DIR . $image_name, QR_ECLEVEL_M, $qr_size, $qr_frame_size);
                update_post_meta($post_id, '_is_qr_code_exist', 1);
                update_post_meta($post_id, '_product_qr_code', $image_name);
            }
        }
    }

    /**
     * Delete associated QR image
     * @param type $post_id
     */
    public function delete_associated_qr_code($post_id) {
        if (get_post_type($post_id) == 'product') {
            $is_qr_code_exist = get_post_meta($post_id, '_is_qr_code_exist', true);
            if (!empty($is_qr_code_exist)) {
                $product_qr_code = get_post_meta($post_id, '_product_qr_code', true);
                if (!empty($product_qr_code) && file_exists(WCQRC_QR_IMAGE_DIR . $product_qr_code)) {
                    unlink(WCQRC_QR_IMAGE_DIR . $product_qr_code);
                }
            }
        }
    }
    /**
     * enqueue admin sctipt
     * @global type $WooCommerceQrCodes
     */
    public function enqueue_admin_script() {
        global $WooCommerceQrCodes;
        $screen = get_current_screen();
        if ($screen->id == 'product') {
            wp_enqueue_style('wcqrc-product', $WooCommerceQrCodes->plugin_url . 'assets/admin/css/wcqrc-product.css', array(), $WooCommerceQrCodes->version);
            wp_enqueue_script('wcqrc-product', $WooCommerceQrCodes->plugin_url . 'assets/admin/js/wcqrc-product.js', array('jquery'), $WooCommerceQrCodes->version, true);
            wp_localize_script('wcqrc-product', 'wcqrc_product', array('ajax_url' => admin_url('admin-ajax.php')));
        }
    }

}
