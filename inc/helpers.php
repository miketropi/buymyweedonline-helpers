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
add_action('woocommerce_single_product_summary', 'bt_update_variation_info', 10);
function bt_update_variation_info(){
  remove_action('woocommerce_single_product_summary', 'bbloomer_echo_variation_info', 11);
  global $product;

    // Check if the product is a variable product
    if ($product->is_type('variable')) {
        // Output an empty container for variation info
        echo '<div class="var_info"><span class="price">Price:  </span> <span class="variation-info"></span></div>';

        // Enqueue JavaScript to handle variation change
        wc_enqueue_js("
            jQuery(document).on('found_variation', 'form.cart', function( event, variation ) {
                jQuery('.var_info .variation-info').html(variation.price_html);
            });
        ");
    }
}
// custom woo template
add_filter( 'woocommerce_locate_template', 'bt_intercept_wc_template', 10, 3 );
/**
 * Filter the cart template path to use cart.php in this plugin instead of the one in WooCommerce.
 *
 * @param string $template      Default template file path.
 * @param string $template_name Template file slug.
 * @param string $template_path Template file name.
 *
 * @return string The new Template file path.
 */
function bt_intercept_wc_template( $template, $template_name, $template_path ) {

	if ( 'variable.php' === basename( $template ) ) {
		$template = B_HELPERS_DIR . '/templates/woocommerce/add-to-cart/variable.php';
	}

	return $template;

}


// get available variants of products
function bt_get_variation_data_by_attribute_name( $available_variations, $attribute_name ) {
  $assigned = array();
  foreach ( $available_variations as $variation ) {
      $attrs = $variation[ 'attributes' ];
      $value = $attrs[ $attribute_name ];
      if ( ! isset( $assigned[ $attribute_name ][ sanitize_title($value) ] ) && ! empty( $value ) ) {
          $assigned[ $attribute_name ][ ($value) ] = array(
              'image_id'     => $variation[ 'variation_image_id' ],
              'variation_id' => $variation[ 'variation_id' ],
              'type'         => empty( $variation[ 'variation_image_id' ] ) ? 'button' : 'image',
          );
      }
  }

  return $assigned;
}
// template swatch image
add_filter('woo_variation_swatches_image_attribute_template', 'bt_woo_variation_swatches_image_attribute_template', 10, 4);
function bt_woo_variation_swatches_image_attribute_template($template, $data, $attribute_type, $variation_data){
  if ( is_product() ){
    $attribute_name = $data['attribute_name'];
    $option_name = $data['option_name'];
    $option_slug = $data['option_slug'];
    if(!$variation_data){
      $product = $data['args']['product'];
      $available_variations = $product->get_available_variations();
      $variation_data = bt_get_variation_data_by_attribute_name($available_variations, $attribute_name);
    }
    $variation_id = isset($variation_data[$attribute_name][sanitize_title($option_name)]) ? $variation_data[$attribute_name][sanitize_title($option_name)]['variation_id'] : $variation_data[$attribute_name][$option_name]['variation_id'];
    $template .= "<span class='option_name'>".$data['option_name']."</span>";
    if($variation_id){
      $variation_obj = wc_get_product($variation_id);
      if($variation_obj){
        $template .= "<span class='option_name'>".$data['option_name']."<label>".$variation_obj->get_price_html()."</label></span>";
      }
    }
  }
  return $template;
}
// template swatch button
add_filter('woo_variation_swatches_button_attribute_template', 'bt_woo_variation_swatches_button_attribute_template', 10, 4);
function bt_woo_variation_swatches_button_attribute_template($template, $data, $attribute_type, $variation_data){
  if ( is_product() ){
    $attribute_name = $data['attribute_name'];
    $option_name = $data['option_slug'];
    if(!$variation_data){
      $product = $data['args']['product'];
      $available_variations = $product->get_available_variations();
      $variation_data = bt_get_variation_data_by_attribute_name($available_variations, $attribute_name);
    }
    $variation_id = isset($variation_data[$attribute_name][sanitize_title($option_name)]) ? $variation_data[$attribute_name][sanitize_title($option_name)]['variation_id'] : $variation_data[$attribute_name][$option_name]['variation_id'];
    if($variation_id){
      $variation_obj = wc_get_product($variation_id);
      if($variation_obj){
        $template = '<span class="variable-item-span variable-item-span-button">%s<label>'.$variation_obj->get_price_html().'</label></span>';
      }
    }
  }
  return $template;
}
// alogolia best seller
add_filter('algolia_post_product_shared_attributes', 'bt_algolia_post_product_shared_attributes', 10, 2);
function bt_algolia_post_product_shared_attributes($shared_attributes, $post){
  $shared_attributes['total_sales'] = (int)get_post_meta( $post->ID, 'total_sales', true );
  return $shared_attributes;
}
// fix query
add_filter('generate_elements_custom_args', 'bt_generate_elements_custom_args');
function bt_generate_elements_custom_args($args){
  $args['suppress_filters'] = true;
   return $args;
}



function b_custom_template_age_gate() {
  b_helpers_load_template('age-gate');
}

//Optimize site
function remove_wp_enqueue_styles(){
    $styles = array();

    //Home page
    if(is_home() || is_front_page()){
        $styles = array(
          'cwginstock_bootstrap',
          'cwginstock_frontend_css',
          'bellows',
          'bellows-font-awesome',
          'bellows-vanilla',
          'delicious-recipe-styles',
          'delicious-recipes-pro-new',
          'delicious-recipes-pro-public',
          'delicious-recipes-pro',
          'jquery-rateyo',
          'light-gallery',
          'owl-carousel',
          'toastr'.
          'fgf-frontend-css',
          'lightcase',
          'pwb-styles-frontend',
          'woobt-frontend',
          'woo-stickers-by-webline',
          'berocket_aapf_widget-style',
          'select2',
          'woocommerce-dynamic-pricing-and-discount',
          'wc-mnm-checkout-blocks',
          'wc-mnm-frontend',
          'wpcsb-frontend'
    		);
    }

    if(is_product_category()){
        $styles = array(
          'cwginstock_bootstrap',
          'cwginstock_frontend_css',
          'bellows',
          'bellows-font-awesome',
          'bellows-vanilla',
          'delicious-recipe-styles',
          'delicious-recipes-pro-new',
          'delicious-recipes-pro-public',
          'delicious-recipes-pro',
          'jquery-rateyo',
          'light-gallery',
          'owl-carousel',
          'toastr'.
          'fgf-frontend-css',
          'lightcase',
          'pwb-styles-frontend',
          'woobt-frontend',
          'woo-stickers-by-webline',
          //'berocket_aapf_widget-style',
          'select2',
          'woocommerce-dynamic-pricing-and-discount',
          'wc-mnm-checkout-blocks',
          'wc-mnm-frontend',
          'wpcsb-frontend'
        );
    }

    if(is_singular('product')){
        $styles = array(
          // 'cwginstock_bootstrap',
          // 'cwginstock_frontend_css',
          'bellows',
          'bellows-font-awesome',
          'bellows-vanilla',
          'delicious-recipe-styles',
          'delicious-recipes-pro-new',
          'delicious-recipes-pro-public',
          'delicious-recipes-pro',
          'jquery-rateyo',
          'light-gallery',
          'owl-carousel',
          'toastr'.
          //'fgf-frontend-css',
          'lightcase',
          //'pwb-styles-frontend',
          //'woobt-frontend',
          'woo-stickers-by-webline',
          //'berocket_aapf_widget-style',
          //'select2',
          //'woocommerce-dynamic-pricing-and-discount',
          //'wc-mnm-checkout-blocks',
          //'wc-mnm-frontend',
          'wpcsb-frontend'
        );
    }

    foreach ($styles as $style) {
      wp_dequeue_style($style);
      wp_deregister_style($style);
    }
}

add_action('wp_head' , 'add_css_fix_bg_white' );
function add_css_fix_bg_white(){
  ?>
  <style media="screen">
    html.async-hide { opacity: 1 !important}
  </style>
  <?php
}
