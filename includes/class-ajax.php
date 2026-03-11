<?php 
class SL_AJAX {

    public function __construct() {
        add_action('wp_ajax_sl_get_stores', [$this, 'get_stores']);
        add_action('wp_ajax_nopriv_sl_get_stores', [$this, 'get_stores']);

        add_action('wp_ajax_sl_get_states', [$this, 'get_states']);
        add_action('wp_ajax_nopriv_sl_get_states', [$this, 'get_states']);

        add_action('wp_ajax_sl_get_cities', [$this, 'get_cities']);
        add_action('wp_ajax_nopriv_sl_get_cities', [$this, 'get_cities']);

        add_action('wp_ajax_sl_get_tipologie', [$this, 'get_tipologie']);
        add_action('wp_ajax_nopriv_sl_get_tipologie', [$this, 'get_tipologie']);

        add_action('wp_ajax_sl_get_initial_data', [$this, 'get_initial_data']);
        add_action('wp_ajax_nopriv_sl_get_initial_data', [$this, 'get_initial_data']);
    }

    public function get_stores() {

        $state = sanitize_text_field($_POST['state'] ?? '');
        $city  = sanitize_text_field($_POST['city'] ?? '');
        $tipologia = sanitize_text_field($_POST['tipologia'] ?? '');

        $meta_query = [];

        if ($state) {
            $meta_query[] = [
                'key' => 'stato',
                'value' => $state,
                'compare' => '='
            ];
        }

        if ($city) {
            $meta_query[] = [
                'key' => 'citta',
                'value' => $city,
                'compare' => '='
            ];
        }

        $tax_query = [];

        if ($tipologia) {
            $tax_query[] = [
                'taxonomy' => 'tipologia',
                'field' => 'slug',
                'terms' => $tipologia,
            ];
        }

        $query = new WP_Query([
            'post_type' => 'store',
            'posts_per_page' => -1,
            'meta_query' => $meta_query,
            'tax_query'  => $tax_query
        ]);

        $stores = [];

        while ($query->have_posts()) {
            $query->the_post();

            $terms = get_the_terms(get_the_ID(), 'tipologia');
            $tipologia_slug = '';
            $tipologia_icon = '';

            if ($terms && !is_wp_error($terms)) {

                $term = $terms[0];

                $tipologia_slug = $term->slug;

                $tipologia_icon = get_field('marker_icon', 'tipologia_' . $term->term_id);
            }

            $stores[] = [
                'title' => get_the_title(),
                'lat' => get_field('latitudine'),
                'lng' => get_field('longitudine'),
                'address' => get_field('indirizzo'),
                'city' => get_field('citta'),
                'state' => get_field('stato'),
                'tipologia' => $tipologia,
                'marker_icon' => $tipologia_icon
            ];
        }

        wp_reset_postdata();

        wp_send_json_success($stores);
    }

    public function get_states() {

        global $wpdb;

        $results = $wpdb->get_col("
            SELECT DISTINCT meta_value 
            FROM {$wpdb->postmeta}
            WHERE meta_key = 'stato'
            ORDER BY meta_value ASC
        ");

        wp_send_json_success($results);
    }

    public function get_cities() {

        $state = sanitize_text_field($_POST['state'] ?? '');

        if (!$state) {
            wp_send_json_success([]);
        }

        $query = new WP_Query([
            'post_type'      => 'store',
            'posts_per_page' => -1,
            'meta_query'     => [
                [
                    'key'     => 'stato',
                    'value'   => $state,
                    'compare' => '='
                ]
            ],
            'fields' => 'ids'
        ]);

        $cities = [];

        if ($query->have_posts()) {
            foreach ($query->posts as $post_id) {
                $city = get_field('citta', $post_id);
                if ($city) {
                    $cities[] = $city;
                }
            }
        }

        wp_reset_postdata();

        $cities = array_unique($cities);
        sort($cities);

        wp_send_json_success(array_values($cities));
    }

    public function get_tipologie() {

        $terms = get_terms([
            'taxonomy' => 'tipologia',
            'hide_empty' => true,
        ]);

        if (is_wp_error($terms)) {
            wp_send_json_error();
        }

        $data = [];

        foreach ($terms as $term) {

            $icon = get_field('marker_icon', 'tipologia_' . $term->term_id);

            $data[] = [
                'slug' => $term->slug,
                'name' => $term->name,
                'marker_icon' => $icon
            ];

        }

        wp_send_json_success($data);
    }

    public function get_initial_data() {

        global $wpdb;

        /*
        ------------------------
        1️⃣ STATES
        ------------------------
        */

        $states = $wpdb->get_col("
            SELECT DISTINCT meta_value 
            FROM {$wpdb->postmeta}
            WHERE meta_key = 'stato'
            ORDER BY meta_value ASC
        ");

        /*
        ------------------------
        2️⃣ TIPOLOGIE
        ------------------------
        */

        $terms = get_terms([
            'taxonomy' => 'tipologia',
            'hide_empty' => true,
        ]);

        $tipologie = [];

        if (!is_wp_error($terms)) {

            foreach ($terms as $term) {

                $icon = get_field('marker_icon', 'tipologia_' . $term->term_id);

                $tipologie[] = [
                    'slug' => $term->slug,
                    'name' => $term->name,
                    'marker_icon' => $icon
                ];
            }
        }

        /*
        ------------------------
        3️⃣ STORES
        ------------------------
        */

        $query = new WP_Query([
            'post_type' => 'store',
            'posts_per_page' => -1
        ]);

        $stores = [];

        while ($query->have_posts()) {
            $query->the_post();

            $terms = get_the_terms(get_the_ID(), 'tipologia');

            $tipologia_slug = '';
            $tipologia_icon = '';

            if ($terms && !is_wp_error($terms)) {

                $term = $terms[0];
                $tipologia_slug = $term->slug;
                $tipologia_icon = get_field('marker_icon', 'tipologia_' . $term->term_id);
            }

            $stores[] = [
                'title' => get_the_title(),
                'lat' => get_field('latitudine'),
                'lng' => get_field('longitudine'),
                'address' => get_field('indirizzo'),
                'city' => get_field('citta'),
                'state' => get_field('stato'),
                'tipologia' => $tipologia_slug,
                'marker_icon' => $tipologia_icon
            ];
        }

        wp_reset_postdata();

        wp_send_json_success([
            'states' => $states,
            'tipologie' => $tipologie,
            'stores' => $stores
        ]);
    }
}