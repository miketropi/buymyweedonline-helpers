<?php
/**
 * Helpers
 */

function b_helpers_load_template($name, $require_once = false) {
  load_template( B_HELPERS_DIR . '/templates/' . $name . '.php', $require_once );
}

function b_helpers_get_woo_products_choices() {
  $choices = [];
  $args = [
    'post_type'       => 'product',
    'posts_per_page'  => -1,
    'post_status'     => 'publish'
  ];

  $_posts = get_posts($args);

  if(!$_posts || count($_posts) <= 0) return $choices;
  $choices['Simple Products'] = [];
  foreach($_posts as $index => $p) {
    $_product = wc_get_product($p->ID);

    if($_product->is_type('simple')) {
      $choices['Simple Products'][$p->ID] = $p->post_title . ' (#'. $p->ID .')';
    }
  }

  foreach($_posts as $index => $p) {
    $_product = wc_get_product($p->ID);

    if($_product->is_type('variable')) {
      $children_ids = $_product->get_children();
      $group_name = $p->post_title . ' (#'. $p->ID .')';
      $choices[$group_name] = [];
      // $choices[$group_name][$p->ID] = $p->post_title . ' (#'. $p->ID .')';
      foreach($children_ids as $child_id) {
        $choices[$group_name][$child_id] = get_the_title($child_id) . ' (#'. $child_id .')';
      }
    }
  }
  
  return $choices;
}
/* fix sale badge */
add_filter('woocommerce_product_is_on_sale', 'bt_woocommerce_product_is_on_sale', 999, 2);
function bt_woocommerce_product_is_on_sale( $on_sale, $product ){
  if ( $product->is_type( 'variable' ) ) {
    global $plugin_public;
    remove_filter( 'woocommerce_product_get_price', array($plugin_public, 'wdpad_format_sale_price_only__premium_only'), 10);
    remove_filter( 'woocommerce_product_variation_get_price',array($plugin_public, 'wdpad_format_sale_price_only__premium_only'), 10 );
    remove_filter( 'woocommerce_variation_prices_price',array($plugin_public, 'wdpad_format_sale_price_only__premium_only'), 10);
    $prices  = $product->get_variation_prices();
    $on_sale = $prices['regular_price'] !== $prices['sale_price'] && $prices['sale_price'] === $prices['price'];

    add_filter( 'woocommerce_product_get_price', array($plugin_public, 'wdpad_format_sale_price_only__premium_only'), 10, 2);
    add_filter( 'woocommerce_product_variation_get_price', array($plugin_public, 'wdpad_format_sale_price_only__premium_only'), 10, 2 );
    add_filter( 'woocommerce_variation_prices_price', array($plugin_public, 'wdpad_format_sale_price_only__premium_only'), 10, 2 );
  }
  return $on_sale;
}
