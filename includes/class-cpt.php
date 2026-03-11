<?php 
class SL_CPT {

    public function __construct() {
        add_action('init', [$this, 'register_cpt']);
        add_action('init', [$this, 'register_taxonomy']);
    }

    public function register_cpt() {

        register_post_type('store', [
            'labels' => [
                'name' => 'Stores',
                'singular_name' => 'Store'
            ],
            'public' => true,
            'menu_icon' => 'dashicons-location-alt',
            'supports' => ['title', 'editor', 'thumbnail'],
            'has_archive' => true,
            'rewrite' => ['slug' => 'stores'],
            'show_in_rest' => true,
        ]);
    }

    public function register_taxonomy() {

            register_taxonomy('tipologia', 'store', [
                'labels' => [
                    'name' => 'Tipologie',
                    'singular_name' => 'Tipologia',
                ],
                'public' => true,
                'hierarchical' => false,
                'show_in_rest' => true,
            ]);
        }
}