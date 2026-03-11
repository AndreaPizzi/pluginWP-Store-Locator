<?php
/**
 * Plugin Name: Publifarm | Store Locator
 * Description: Custom Store Locator con CPT e integrazione Google Maps
 * Author: Pizzigalli Andrea | Publifarm S.p.A. Società Benefit  
 * Version: 1.0
 */


if ( ! defined( 'ABSPATH' ) ) exit;

define('SL_VERSION', '1.0');
define('SL_PATH', plugin_dir_path(__FILE__));
define('SL_URL', plugin_dir_url(__FILE__));

require_once SL_PATH . 'includes/class-store-locator.php';

function sl_init_plugin() {
    return new SL_Store_Locator();
}

$GLOBALS['store_locator'] = sl_init_plugin();