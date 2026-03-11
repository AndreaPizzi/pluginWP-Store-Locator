<?php
class SL_Assets {

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue']);
    }

    public function enqueue() {
       // if (!is_singular() && !is_page()) return;

        // global $post;

        // if (!isset($post->post_content) || !has_shortcode($post->post_content, 'store_locator')) {
        //     return;
        // }

        $api_key = get_field('google_maps_api_key', 'option');

        if ($api_key) {
            wp_enqueue_script(
                'google-maps',
                'https://maps.googleapis.com/maps/api/js?key=' . $api_key,
                [],
                null,
                true
            );
        }

        wp_enqueue_style(
            'store-locator-css',
            SL_URL . 'assets/css/store-locator.css',
            [],
            SL_VERSION
        );

        wp_enqueue_script(
            'store-locator-js',
            SL_URL . 'assets/js/store-locator.js',
            ['jquery', 'google-maps'],
            SL_VERSION,
            true
        );

        wp_localize_script('store-locator-js', 'SL_Config', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'default_marker' => get_field('marker_placeholder', 'option')

        ]);
    }
}