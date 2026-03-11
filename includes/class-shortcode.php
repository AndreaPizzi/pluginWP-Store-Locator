<?php 

class SL_Shortcode {

    public function __construct() {
        add_shortcode('publifarm_store_locator', [$this, 'render']);
    }

    public function render() {

        do_action('sl_enqueue_assets');
        
        ob_start();
        include SL_PATH . 'templates/store-locator-template.php';
        return ob_get_clean();
    }
}