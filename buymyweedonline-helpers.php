<?php
/**
 * Buymyweedonline Helpers.
 *
 * Plugin Name: Buymyweedonline Helpers
 * Version:     1.0.0
 * Plugin URI:  #
 * Description: #
 * Author:      Beplus
 * Author URI:  #
 * Text Domain: b-helpers
 */

{
  /**
   * Define
   */
  define('B_HELPERS_VERSION', '1.0.0');
  define('B_HELPERS_URI', plugin_dir_url(__FILE__));
  define('B_HELPERS_DIR', plugin_dir_path(__FILE__));
}

{
  /**
   * Inc
   */
  require(B_HELPERS_DIR . '/inc/static.php');
  require(B_HELPERS_DIR . '/inc/helpers.php');
  require(B_HELPERS_DIR . '/inc/hooks.php');
  require(B_HELPERS_DIR . '/inc/ajax.php');
  require(B_HELPERS_DIR . '/inc/shortcode.php');
  require(B_HELPERS_DIR . '/inc/template-tags.php');
  require(B_HELPERS_DIR . '/inc/woo-helpers.php');
  //require B_HELPERS_DIR . '/vendor/autoload.php';
}

{
  /**
   * Boot
   */
}

// register_deactivation_hook( __FILE__, 'buymyweedonline_helpers_deactivate' );
// function buymyweedonline_helpers_deactivate(){
//    $timestamp1 = wp_next_scheduled('be_send_link_payment_interac_to_customer');
//    wp_unschedule_event($timestamp1, 'be_send_link_payment_interac_to_customer');
//
//    $timestamp2 = wp_next_scheduled('be_update_status_order_after_payment_interac');
//    wp_unschedule_event($timestamp2, 'be_update_status_order_after_payment_interac');
// }
