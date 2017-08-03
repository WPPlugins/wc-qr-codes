<?php

/**
 * ajax class
 *
 * @author bappa
 */
class WCQRCodesAjax {

    /**
     * construct
     */
    function __construct() {
        add_action('wp_ajax_regenerate_qr_code', array($this, 'regenerate_qr_code'));
    }

    /**
     * regenerate product qr code
     */
    public function regenerate_qr_code() {
        global $WooCommerceQrCodes;
        $post_id = isset($_POST['product_id']) ? $_POST['product_id'] : 0;
        if ($post_id != 0) {
            $is_qr_code_exist = get_post_meta($post_id, '_is_qr_code_exist', true);
            if (empty($is_qr_code_exist)) {
                $product_qr_code = get_post_meta($post_id, '_product_qr_code', true);
                if (!empty($product_qr_code) && file_exists(WCQRC_QR_IMAGE_DIR . $product_qr_code)) {
                    unlink(WCQRC_QR_IMAGE_DIR . $product_qr_code);
                }
            }
            $permalink = apply_filters('wcqrc_product_permalink', get_permalink($post_id), $post_id) ;
            $image_name = time() . '_' . $post_id . '.png';
            $qr_size = intval($WooCommerceQrCodes->settings_api->get_option('qr_size', 'wcqrc_basics', 4));
            $qr_frame_size = intval($WooCommerceQrCodes->settings_api->get_option('qr_frame_size', 'wcqrc_basics', 2));
            $WooCommerceQrCodes->QRcode->png(esc_url($permalink), WCQRC_QR_IMAGE_DIR . $image_name, QR_ECLEVEL_M, $qr_size, $qr_frame_size);
            update_post_meta($post_id, '_is_qr_code_exist', 1);
            update_post_meta($post_id, '_product_qr_code', $image_name);
            echo WCQRC_QR_IMAGE_URL . $image_name;
        }
        wp_die();
    }

}
