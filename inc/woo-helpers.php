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

function b_helpers_get_the_rest_of_amount() {
  $freegift_products = get_field('bh_freegift_products', 'option');
  $unlock_amount_arr = array_map(function($item) {
    return (int) $item['unlock_amount'];
  }, $freegift_products);

  sort($unlock_amount_arr);
  $cart_total = WC()->cart->total;
  
  $number_unlocked = 0;
  $the_rest_of_amount = 0;

  foreach($unlock_amount_arr as $__index => $__item) {
    if($__item > $cart_total) {
      $number_unlocked = $__index;
      $the_rest_of_amount = $__item - $cart_total;
      break;
    }
  }

  // passed all cases
  if($number_unlocked == 0 && $the_rest_of_amount == 0) {
    return true;
  }

  return [
    'the_rest_of_amount' => $the_rest_of_amount,
    'number_unlocked' => $number_unlocked
  ];
}