<?php 
/**
 *  WooCommerce Helper
 */

function b_helpers_free_gift() {
  $freegift_enable = get_field('bh_freegift_enable', 'option');
  if($freegift_enable != true) return;

  $freegift_products = get_field('bh_freegift_products', 'option');
  set_query_var('freegift_products', $freegift_products);

  b_helpers_load_template('free-gift');
}

add_action('woocommerce_after_mini_cart', 'b_helpers_free_gift');

function b_helpers_free_gift_message() {
  $rest = b_helpers_get_the_rest_of_amount();

  if($rest === false) {
    return __('We currently do not have any giveaways, thank you!', 'b_helpers');
  }

  if($rest === true) {
    return __('You are unlocked all Free gift!<br />Only 1 gift per cart', 'b_helpers');
  }

  $the_rest_of_amount = wc_price($rest['the_rest_of_amount']);
  $message_type = $rest['number_unlocked'] == 0 ? 'first' : 'next';   
 
  $dynamic_text = [
    'first' => __('more to your cart a Free gift', 'b_helpers'),
    'next' => __('more to unlock the next Free gift', 'b_helpers'), 
  ];

  $text = sprintf(__('Add %s %s!<br />Only 1 gift per cart.', 'b_helpers'), $the_rest_of_amount, $dynamic_text[$message_type]);
  return apply_filters('B_HELPERS:FREE_GIFT_MESSAGE', $text, $the_rest_of_amount, $message_type);
}

// add_action('wp_head', function() {
//   global $woocommerce;  
//   echo 'dev' . WC()->cart->total;
//   echo 'dev' . WC()->cart->get_total_discount(); 
//   echo 'dev' . WC()->cart->get_fee_total(); 
//   echo 'dev' . WC()->cart->get_cart_discount_total(); 
//   echo 'dev' . WC()->cart->get_cart_discount_tax_total(); 
// });

add_action('woocommerce_widget_shopping_cart_total', function() {
  $fee = WC()->cart->get_fee_total();
  ?> 
  <?php if($fee && $fee != 0) { ?>
  <p class="woocommerce-mini-cart__fee fee">
		<strong><?php _e('Discount:', 'b_helpers') ?></strong> 
    <?php echo wc_price($fee); ?> 
  </p>
  <?php  } ?>
  <p class="woocommerce-mini-cart__total total __total">
		<strong><?php _e('Total:', 'b_helpers') ?></strong> 
    <?php echo wc_price(WC()->cart->total); ?> 
  </p>
  <?php  
}, 15);

function b_helpers_get_the_rest_of_amount() { 
  $freegift_products = get_field('bh_freegift_products', 'option');

  if(!$freegift_products || count($freegift_products) <= 0) {
    return false;
  }

  $unlock_amount_arr = array_map(function($item) { 
    return (float) $item['unlock_amount']; 
  }, $freegift_products);  

  sort($unlock_amount_arr);
  $cart_total = (float) WC()->cart->total;
  
  $number_unlocked = 0;
  $the_rest_of_amount = 0;

  foreach($unlock_amount_arr as $__index => $__item) {
    if($__item > $cart_total) {
      $number_unlocked = $__index;
      $the_rest_of_amount = $__item - $cart_total; 
      break; 
    }
  }
  // wp_send_json( [$the_rest_of_amount, $cart_total] );
  // passed all cases
  if($number_unlocked == 0 && $the_rest_of_amount == 0) {
    return true;
  }

  return [
    'the_rest_of_amount' => $the_rest_of_amount,
    'number_unlocked' => $number_unlocked
  ];
}

function b_helpers_get_product_in_list_free_gift($id) {
  $freegift_products = get_field('bh_freegift_products', 'option');
  if(!$freegift_products || count($freegift_products) <= 0) return false;
  
  $found_key = array_search($id, array_column($freegift_products, 'select_product_for_freegift'));
  if(!isset($freegift_products[$found_key])) return false;
  return $freegift_products[$found_key];
}

/**
 * 
 */
function b_helpers_add_to_cart_gift_validation($passed_validation, $product_id, $quantity, $variation_id) {
  return $passed_validation;
}

add_filter('B_HELPERS:ADD_TO_CART_GIFT_VALIDATION', 'b_helpers_add_to_cart_gift_validation', 20, 4); 

function b_helpers_clear_all_free_gift_product_in_cart() {
  $cart = WC()->cart;
  foreach($cart->get_cart() as $cart_item_key => $cart_item) {
    if(isset($cart_item['__FREE_GIFT__'])) {
      WC()->cart->remove_cart_item( $cart_item_key );
    }
  }
} 

/**
 * 
 */
function b_helpers_woocommerce_ajax_add_to_cart_free_gift() {
  $product_id = absint($_POST['product_id']);
  $quantity = empty($_POST['quantity']) ? 1 : wc_stock_amount($_POST['quantity']);
  $variation_id = absint($_POST['variation_id']);
  $passed_validation = apply_filters('B_HELPERS:ADD_TO_CART_GIFT_VALIDATION', true, $product_id, $quantity, $variation_id);
  $product_status = get_post_status($product_id);

  $pid = $variation_id ? $variation_id : $product_id;
  $findProductInListFreeGift = b_helpers_get_product_in_list_free_gift($pid);

  // clear all free gift products after
  b_helpers_clear_all_free_gift_product_in_cart();

  if (
    $findProductInListFreeGift != false && 
    $passed_validation && 
    'publish' === $product_status && 
    WC()->cart->add_to_cart($product_id, $quantity, $variation_id, [], [
    '__FREE_GIFT__' => [
      'custom_product_price' => 0, // free
      'custom_product_name' => $findProductInListFreeGift['name'],
      'unlock_amount' => $findProductInListFreeGift['unlock_amount'],
      'select_product_for_freegift' => $findProductInListFreeGift['select_product_for_freegift']
    ]
  ])) {
    
    do_action('woocommerce_ajax_added_to_cart', $product_id);
    
    if ('yes' === get_option('woocommerce_cart_redirect_after_add')) {
      wc_add_to_cart_message([$product_id => $quantity], true);
    }

    WC_AJAX::get_refreshed_fragments();
  } else {
    $data = [
      'error' => true,
      'product_url' => apply_filters('woocommerce_cart_redirect_after_error', get_permalink($product_id), $product_id)
    ];

    echo wp_send_json($data);
  }

  wp_die();
}

add_action('wp_ajax_b_helpers_woocommerce_ajax_add_to_cart_free_gift', 'b_helpers_woocommerce_ajax_add_to_cart_free_gift');
add_action('wp_ajax_nopriv_b_helpers_woocommerce_ajax_add_to_cart_free_gift', 'b_helpers_woocommerce_ajax_add_to_cart_free_gift');

/**
 * Custom mini cart product item price display
 */
add_action('woocommerce_cart_item_price', function($price, $cart_item, $cart_item_key) {
  if(isset($cart_item['__FREE_GIFT__'])) {
    return wc_price((int) $cart_item['__FREE_GIFT__']['custom_product_price']);
  }

  return $price;
}, 20, 3);

/**
 * calculate totals 
 */
add_action( 'woocommerce_before_calculate_totals', function($cart_object) {
  foreach ( $cart_object->cart_contents as $cart_item_key => $item ) {
    if(isset($item['__FREE_GIFT__'])) {
      $item['data']->set_price((int) $item['__FREE_GIFT__']['custom_product_price']);
      $item['data']->set_name($item['__FREE_GIFT__']['custom_product_name']);
    }
  }
}, 1);

add_filter('woocommerce_mini_cart_item_class', function($classes, $cart_item, $cart_item_key) {
  if(isset($cart_item['__FREE_GIFT__'])) {
    $classes .= ' __free-gift-item';
  }
  return $classes;
}, 20, 3);

/**
 * Free Gift only 1 qtt (not update)
 */
add_filter('woocommerce_update_cart_validation', function($passed, $cart_item_key, $values, $quantity) {
  $cart = WC()->cart;

  foreach($cart->get_cart() as $_cart_item_key => $cart_item) {
    if($cart_item_key == $_cart_item_key && isset($cart_item['__FREE_GIFT__'])) {
      return false;
    }
  }

  return $passed;
}, 20, 4);

function b_helpers_find_and_remove_free_gift_invalite($cart_updated) {
  if($cart_updated) {
    WC()->cart->calculate_totals();
    $cart = WC()->cart;
    $cart_total = WC()->cart->total;
    // wp_send_json( [$cart_total] );
    /**
     * find and remove free gift invalite
     */
    foreach($cart->get_cart() as $cart_item_key => $cart_item) {
      if(isset($cart_item['__FREE_GIFT__'])) {
        if((int) $cart_item['__FREE_GIFT__']['unlock_amount'] > $cart_total) {
          WC()->cart->remove_cart_item( $cart_item_key );
        }
      }
    }

    return $passed;
  }

  return $cart_updated;
}

add_filter('woocommerce_cart_item_removed', 'b_helpers_find_and_remove_free_gift_invalite', 20);
add_filter('woocommerce_update_cart_action_cart_updated', 'b_helpers_find_and_remove_free_gift_invalite', 20);

function b_helpers_mini_cart_nonce(){
  wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce', false);
}

add_action('woocommerce_before_mini_cart_contents','b_helpers_mini_cart_nonce' );

function b_helpers_minicart_offcanvas() {
  $minicart_enable = get_field('bh_minicart_enable', 'option');
  if($minicart_enable != true) return;

  b_helpers_load_template('minicart');
}

add_action('wp_head','b_helpers_minicart_offcanvas'); 