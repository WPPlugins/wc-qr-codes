<?php

if (!defined('ABSPATH')) {
    exit;
}

class WCQRCodesInstall {

    public static function install() {
        $upload_dir = wp_upload_dir();
        wp_mkdir_p($upload_dir['basedir'] . '/wcqrc-images');
    }

}
