<?php 
class SL_ACF {

    public function __construct() {
        add_action('acf/init', [$this, 'register_fields']);
        add_action('acf/init', [$this, 'register_options_page']);
        
    }

    public function register_fields() {

        if (!function_exists('acf_add_local_field_group')) return;

        acf_add_local_field_group([
            'key' => 'group_store',
            'title' => 'Store Details',
            'fields' => [

                [
                    'key' => 'field_lat',
                    'label' => 'Latitudine',
                    'name' => 'latitudine',
                    'type' => 'number',
                    'step' => '0.000001'
                ],

                [
                    'key' => 'field_lng',
                    'label' => 'Longitudine',
                    'name' => 'longitudine',
                    'type' => 'number',
                    'step' => '0.000001'
                ],

                [
                    'key' => 'field_address',
                    'label' => 'Indirizzo',
                    'name' => 'indirizzo',
                    'type' => 'text'
                ],

                [
                    'key' => 'field_city',
                    'label' => 'Città',
                    'name' => 'citta',
                    'type' => 'text'
                ],

                [
                    'key' => 'field_province',
                    'label' => 'Provincia',
                    'name' => 'provincia',
                    'type' => 'text'
                ],

                [
                    'key' => 'field_country',
                    'label' => 'Stato',
                    'name' => 'stato',
                    'type' => 'text'
                ],

            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'store',
                    ],
                ],
            ],
        ]);

        acf_add_local_field_group([
            'key' => 'group_store_settings',
            'title' => 'Store Locator Settings | Shortocode: [ publifarm_store_locator ] ',
            'fields' => [

                [
                    'key' => 'field_google_api',
                    'label' => 'Google Maps API Key',
                    'name' => 'google_maps_api_key',
                    'type' => 'text'
                ],

                [
                    'key' => 'field_marker_placeholder',
                    'label' => 'Marker Placeholder',
                    'name' => 'marker_placeholder',
                    'type' => 'image',
                    'return_format' => 'url'
                ],
                [
                    'key' => 'field_map_style',
                    'label' => 'Google Map Style (JSON)',
                    'name' => 'google_map_style',
                    'type' => 'textarea',
                    'instructions' => 'Incolla qui il JSON di SnazzyMaps',
                    'rows' => 10,
                ],

            ],
            'location' => [
                [
                    [
                        'param' => 'options_page',
                        'operator' => '==',
                        'value' => 'store-locator-settings',
                    ],
                ],
            ],
        ]);

        acf_add_local_field_group([
            'key' => 'group_tipologia_marker',
            'title' => 'Marker Tipologia',
            'fields' => [
                [
                    'key' => 'field_tipologia_marker',
                    'label' => 'Marker Icon',
                    'name' => 'marker_icon',
                    'type' => 'image',
                    'return_format' => 'url'
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'taxonomy',
                        'operator' => '==',
                        'value' => 'tipologia',
                    ],
                ],
            ],
        ]);
    }

    public function register_options_page() {

        if( function_exists('acf_add_options_page') ) {

            acf_add_options_page([
                'page_title' => 'Store Locator Settings',
                'menu_title' => 'Store Locator ',
                'menu_slug'  => 'store-locator-settings',
                'capability' => 'manage_options',
                'redirect'   => false
            ]);
        }
    }
}