<?php 
class SL_Store_Locator {

    public function __construct() {

        require_once SL_PATH . 'includes/class-cpt.php';
        require_once SL_PATH . 'includes/class-acf.php';
        require_once SL_PATH . 'includes/class-ajax.php';
        require_once SL_PATH . 'includes/class-shortcode.php';
        require_once SL_PATH . 'includes/class-assets.php';

        new SL_CPT();
        new SL_ACF();
        new SL_AJAX();
        new SL_Shortcode();
        new SL_Assets();

        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
    }

    public function activate() {
        flush_rewrite_rules();
    }

    public function deactivate() {
        flush_rewrite_rules();
    }
}