<?php
/**
 * Plugin Name: WooCommerce QR Codes
 * Plugin URI: https://wordpress.org/plugins/woocommerce-qr-codes/
 * Description: Generate QR Codes for woocommerce products.
 * Version: 1.0.4
 * Author: Bappa Mal
 * Author URI: https://profiles.wordpress.org/bappa1995
 * Requires at least: 4.4
 * Tested up to: 4.8
 *
 * Text Domain: wc-qr-codes
 * Domain Path: /languages/
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('WooCommerceQrCodes')) :

    /**
     * Main Woocommerce QR codes class
     */
    final class WooCommerceQrCodes {

        /**
         * version
         * @var string
         */
        public $version = '1.0.4';

        /**
         * The single instance of the class.
         *
         * @var WooCommerceQrCodes
         */
        protected static $_instance = null;

        /**
         * Main class object
         * @var object
         */
        public $WCQRCodes;

        /**
         * QRcode object
         * @var object
         */
        public $QRcode;

        /**
         * QRcode settings api
         */
        public $settings_api;

        /**
         * plugin url
         * @var string 
         */
        public $plugin_url;

        /**
         * main instance of WooCommerceQrCodes class
         * @return class object
         */
        public static function instance() {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        /**
         * WooCommerceQrCodes construct
         */
        public function __construct() {
            $this->define_vars();
            $this->plugin_url = trailingslashit(plugins_url('', $plugin = __FILE__));
            $this->includes();
            $this->init();
        }

        /**
         * define var
         */
        private function define_vars() {
            $upload_dir = wp_upload_dir();
            $this->define('WCQRC_PLUGIN_FILE', __FILE__);
            $this->define('WCQRC_TEXT_DOMAIN', 'wc-qr-codes');
            $this->define('WCQRC_PLUGIN_BASENAME', plugin_basename(__FILE__));
            $this->define('WCQRC_VERSION', $this->version);
            $this->define('WCQRC_QR_IMAGE_DIR', $upload_dir['basedir'] . '/wcqrc-images/');
            $this->define('WCQRC_QR_IMAGE_URL', $upload_dir['baseurl'] . '/wcqrc-images/');
        }

        /**
         * Define constant if not already set.
         *
         * @param  string $name
         * @param  string|bool $value
         */
        private function define($name, $value) {
            if (!defined($name)) {
                define($name, $value);
            }
        }

        /**
         * Include required core files used in admin and on the frontend.
         */
        public function includes() {
            include_once( 'includes/lib/phpqrcode/qrlib.php' );
            include_once( 'includes/class-wc-qr-codes.php' );
            include_once('includes/class-wc-qr-codes-install.php');
            require_once('includes/class-wc-qr-codes-settings-api.php');
        }

        /**
         * plugin init function
         */
        private function init() {
            $this->load_text_domain();
            $this->WCQRCodes = new WCQRCodes();
            $this->QRcode = new QRcode();
            $this->settings_api = new WCQRC_Settings_API();
            register_activation_hook(__FILE__, array('WCQRCodesInstall', 'install'));
        }

        /**
         * Plugin textdomain loader
         */
        public function load_text_domain() {
            $locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
            $locale = apply_filters( 'plugin_locale', $locale, 'wc-qr-codes' );
            load_textdomain( 'wc-qr-codes', WP_LANG_DIR . '/wc-qr-codes/wc-qr-codes-' . $locale . '.mo' );
            load_plugin_textdomain( 'wc-qr-codes', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
        }

    }

    endif;

if (!class_exists('WooCommerceQrCodesDependencies')) :

    /**
     * class WooCommerceQrCodesDependencies for plugin dependencies check
     */
    final class WooCommerceQrCodesDependencies {

        private static $active_plugins;

        /**
         * load active plugin lists
         */
        public static function init() {
            self::$active_plugins = (array) get_option('active_plugins', array());
            if (is_multisite()) {
                self::$active_plugins = array_merge(self::$active_plugins, get_site_option('active_sitewide_plugins', array()));
            }
        }

        /**
         * woocommerce active check
         * @return boolean
         */
        public static function is_woocommerce_active() {
            if (!self::$active_plugins) {
                self::init();
            }
            return in_array('woocommerce/woocommerce.php', self::$active_plugins) || array_key_exists('woocommerce/woocommerce.php', self::$active_plugins);
        }

        /**
         * GD extension check
         * @return boolean
         */
        public static function gd_extension_loaded() {
            if (extension_loaded('gd')) {
                return true;
            } else {
                return false;
            }
        }

        /**
         * Display error notice for woocommerce active check
         */
        public static function woocommerce_not_install_notice() {
            ?>
            <div class="error">
                <p><?php _e('WooCommerce QR Codes requires <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a> plugins to be active!', 'wc-qr-codes'); ?></p>
            </div>
            <?php
        }

        /**
         * Display error notice for GD active check
         */
        public static function not_gd_extention_loaded_notice() {
            ?>
            <div class="error">
                <p><?php _e('WooCommerce QR Codes requires <a href="http://php.net/manual/en/book.image.php">PHP GD library</a> loaded', 'wc-qr-codes'); ?></p>
            </div>
            <?php
        }

    }

    endif;

/**
 * 
 * @return WooCommerceQrCodes
 */
function WCQRC() {
    return WooCommerceQrCodes::instance();
}

if (!function_exists('get_wc_product_qr_code_src')) {
    /**
     * Get QR code by prodyct id
     * @param int $product_id
     * @return URL | boolean
     */
    function get_wc_product_qr_code_src($product_id) {
        $upload_dir = wp_upload_dir();
        $is_qr_code_exist = get_post_meta($product_id, '_is_qr_code_exist', true);
        $product_qr_code = get_post_meta($product_id, '_product_qr_code', true);
        if (!empty($is_qr_code_exist) && !empty($product_qr_code) && file_exists($upload_dir['basedir'] . '/wcqrc-images/' . $product_qr_code)) {
            return $upload_dir['baseurl'] . '/wcqrc-images/' . $product_qr_code;
        } else {
            return false;
        }
    }

}

if (!WooCommerceQrCodesDependencies::is_woocommerce_active()) {
    add_action('admin_notices', 'WooCommerceQrCodesDependencies::woocommerce_not_install_notice');
}

if (!WooCommerceQrCodesDependencies::gd_extension_loaded()) {
    add_action('admin_notices', 'WooCommerceQrCodesDependencies::not_gd_extention_loaded_notice');
}

if (WooCommerceQrCodesDependencies::is_woocommerce_active() && WooCommerceQrCodesDependencies::gd_extension_loaded()) {
    /**
     * global WooCommerceQrCodes
     * @global type $GLOBALS['WooCommerceQrCodes']
     * @name $WooCommerceQrCodes 
     */
    $GLOBALS['WooCommerceQrCodes'] = WCQRC();
}
