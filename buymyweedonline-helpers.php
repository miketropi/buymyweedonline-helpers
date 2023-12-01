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
}

{
  /**
   * Boot
   */ 
}