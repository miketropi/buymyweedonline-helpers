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
    return ''; // __('We currently do not have any giveaways, thank you!', 'b_helpers');
  }

  if($rest === true) {
    return __('You unlocked all free gifts!<br />Only 1 gift per cart', 'b_helpers');
  }

  $the_rest_of_amount = wc_price($rest['the_rest_of_amount']);
  $message_type = $rest['number_unlocked'] == 0 ? 'first' : 'next';

  $dynamic_text = [
    'first' => __('more to your cart for a free gift', 'b_helpers'),
    'next' => __('more to unlock the next free gift', 'b_helpers'),
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
  $current_shipping_cost = WC()->cart->get_cart_shipping_total();
  ?>
  <?php if($current_shipping_cost && !empty($current_shipping_cost)) : ?>
  <p class="woocommerce-mini-cart__shipping shipping">
    <strong><?php _e('Shipping:', 'b_helpers') ?></strong>
    <?php echo $current_shipping_cost; ?>
  </p>
  <?php endif; ?>

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

add_action('wp_footer','b_helpers_minicart_offcanvas');

function b_helpers_get_free_shipping_minimum($zone_name = 'England') {
  if ( ! isset( $zone_name ) ) return null;

  $result = null;
  $zone = null;

  $zones = WC_Shipping_Zones::get_zones();
  foreach ( $zones as $z ) {
    if ( $z['zone_name'] == $zone_name ) {
      $zone = $z;
    }
  }

  if ( $zone ) {
    $shipping_methods_nl = $zone['shipping_methods'];
    $free_shipping_method = null;
    foreach ( $shipping_methods_nl as $method ) {
      if ( $method->id == 'free_shipping' ) {
        $free_shipping_method = $method;
        break;
      }
    }

    if ( $free_shipping_method ) {
      $result = $free_shipping_method->min_amount;
    }
  }

  return $result;
}

function b_helpers_action_woocommerce_before_mini_cart () {
  global $woocommerce;
  $free_shipping_en = b_helpers_get_free_shipping_minimum('Free Shipping');
  $count = intval($woocommerce->cart->get_cart_contents_count());
  $subtotal = $woocommerce->cart->get_cart_contents_total();

  if ( $free_shipping_en ) {
    $free_shipping_min = $free_shipping_en;
    if($count > 0) {
    ?>
    <div class="show-free-shiping-wrapper">
      <div class="title-amount-shipping">
          <div class="title">
            <?php
              if($subtotal < $free_shipping_min) {
                ?>
                <span class="text"><?php _e('Free Shipping', 'b_helpers') ?></span>
                <span class="price"><?php echo wc_price($free_shipping_min) . '+' ?></span>
                <?php
              }else{
                ?>
                <p class="congra"><?php _e('Congratulations! You have received Free Shipping!', 'b_helpers') ?></p>
                <?php
              }
            ?>
          </div>
      </div>
      <div class="inprogress-bar-free-shiping">
        <?php
        $width = $subtotal / $free_shipping_min;
        if ($width < 1) { $width_css = $width * 100; }
        else { $width_css = 100; }
        ?>
        <div class="bar-prgress-all">
          <span style="width:<?php echo $width_css . '%' ?>"></span>
        </div>
      </div>
      <?php
        if($subtotal < $free_shipping_min) {
          $number_change = $free_shipping_min - $subtotal;
          ?>
          <div class="text-more-add-pr">
            <span class="text"><?php _e('Add', 'b_helpers') ?></span>
            <span class="price"><?php echo wc_price($number_change); ?> </span>
            <span class="text"><?php _e('For Free Shipping!', 'b_helpers') ?></span>
          </div>
          <?php
        }
      ?>
    </div>
    <?php
    }
  }
}

add_action( 'woocommerce_before_mini_cart', 'b_helpers_action_woocommerce_before_mini_cart', 10, 0);

function b_helpers_display_quantity_plus() {
  echo '<button type="button" class="ic-item-quantity-btn plus __show-only-mini-cart" data-type="plus"> +</button>';
}
function b_helpers_display_quantity_minus() {
  echo '<button type="button" class="ic-item-quantity-btn minus __show-only-mini-cart" data-type="minus">-</button>';
}

add_action('woocommerce_after_quantity_input_field', 'b_helpers_display_quantity_plus');
add_action('woocommerce_before_quantity_input_field', 'b_helpers_display_quantity_minus');

function b_helpers_meta_tag_after_button_mini_cart() {
  $after_button = get_field('bh_minicart_after_button', 'option');
  if(!isset($after_button['enable']) && $after_button['enable'] != true) return;

  $icon_start = '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M10.0003 1.66669L12.5753 6.88335L18.3337 7.72502L14.167 11.7834L15.1503 17.5167L10.0003 14.8084L4.85033 17.5167L5.83366 11.7834L1.66699 7.72502L7.42533 6.88335L10.0003 1.66669Z" stroke="black" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/> </svg>';
  $icon_support = '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M2.5 15V10C2.5 8.01088 3.29018 6.10322 4.6967 4.6967C6.10322 3.29018 8.01088 2.5 10 2.5C11.9891 2.5 13.8968 3.29018 15.3033 4.6967C16.7098 6.10322 17.5 8.01088 17.5 10V15" stroke="black" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/> <path d="M17.5 15.8334C17.5 16.2754 17.3244 16.6993 17.0118 17.0119C16.6993 17.3244 16.2754 17.5 15.8333 17.5H15C14.558 17.5 14.134 17.3244 13.8215 17.0119C13.5089 16.6993 13.3333 16.2754 13.3333 15.8334V13.3334C13.3333 12.8913 13.5089 12.4674 13.8215 12.1548C14.134 11.8423 14.558 11.6667 15 11.6667H17.5V15.8334ZM2.5 15.8334C2.5 16.2754 2.67559 16.6993 2.98816 17.0119C3.30072 17.3244 3.72464 17.5 4.16667 17.5H5C5.44203 17.5 5.86595 17.3244 6.17851 17.0119C6.49107 16.6993 6.66667 16.2754 6.66667 15.8334V13.3334C6.66667 12.8913 6.49107 12.4674 6.17851 12.1548C5.86595 11.8423 5.44203 11.6667 5 11.6667H2.5V15.8334Z" stroke="black" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/> </svg>';
  ?>
  <div class="meta-tag-love-and-support">
    <div class="__love">
      <a href="<?php echo $after_button['url_1'] ?>">
        <span class="__icon"><?php echo $icon_start; ?></span>
        <?php echo $after_button['text_1'] ?>
      </a>
    </div>
    <div class="__support">
      <a href="<?php echo $after_button['url_2'] ?>">
        <span class="__icon"><?php echo $icon_support; ?></span>
        <?php echo $after_button['text_2'] ?>
      </a>
    </div>
  </div>
  <div class="meta-tag-shipping-and-payment">
    <div class="__shipping">
      <img src="<?php echo B_HELPERS_URI . '/images/shipping.png' ?>" alt="shipping">
      <?php echo $after_button['text_3'] ?>
    </div>
    <div class="__payment">
      <img src="<?php echo B_HELPERS_URI . '/images/e-transfer.png' ?>" alt="e-transfer">
      <?php echo $after_button['text_4'] ?>
    </div>
  </div>
  <?php
}

add_action('woocommerce_widget_shopping_cart_after_buttons', 'b_helpers_meta_tag_after_button_mini_cart', 30);

add_action('woocommerce_widget_shopping_cart_before_buttons', function() {
  ?>
  <div class="mini-cart-group-stick-button">
  <?php
}, 1);

add_action('woocommerce_widget_shopping_cart_after_buttons', function() {
  ?>
  </div> <!-- .mini-cart-group-stick-button -->
  <?php
}, 90);

function b_helpers_translate_text_checkout($translated) {
  if($translated == 'Checkout') {
    $translated = str_ireplace('Checkout', 'Secure Checkout', $translated);
  }
  return $translated;
}

add_filter('gettext', 'b_helpers_translate_text_checkout');
add_filter('ngettext', 'b_helpers_translate_text_checkout');

/**
 * Add total cart beside button checkout
 */
add_action('woocommerce_widget_shopping_cart_buttons', function() {
  ?>
  <span class="total-cart">
    <strong><?php _e('Total:', 'b_helpers') ?></strong>
    <?php echo wc_price(WC()->cart->total); ?>
  </span>
  <?php
}, 5);

// Fix get opts algolia cat page (NOT DELETE)
add_action('init', function() {
  $al_opts = get_field('bh_algolia_app', 'option');
  $active_age_gate_all = get_field('active_age_gate','option');
  $al_age_gate_opts = get_field('bh_age_gate_app', 'option');
});

//Hook show Bougth Together by Category Product
add_filter('woobt_get_ids' , 'woo_custom_woobt_get_ids' , 10 , 3);
function woo_custom_woobt_get_ids($ids, $product_id, $context){

  if(empty($ids) && !is_admin()){
    $terms = get_the_terms ( $product_id , 'product_cat' );
    $ids = [];

    if(!empty($terms)){
      foreach ( $terms as $term ) {
          $list_product_to_bought_together = get_field('list_product_to_bought_together', $term);
          if(!empty($list_product_to_bought_together)){
            foreach ($list_product_to_bought_together as $row) {
               $item = [];
               $item['id'] = $row['product'];
               $item['sku'] = $row['product'];
               $item['price'] = $row['new_price'];
               $item['qty'] = $row['quality'];
               $ids[] = $item;
            }
          }
      }
    }

  }

  return $ids;
}

//Change text "Add 1" to "1 left"
add_filter('wc_mnm_child_item_quantity_input_args' , 'custom_text_wc_mnm_child_item_quantity_input_args' , 10 , 2);
function custom_text_wc_mnm_child_item_quantity_input_args($input_args, $child_item){
  $checkbox_label = sprintf(
		_x( '%1$d left <span class="screen-reader-text">%2$s</span>', '[Frontend]', 'woocommerce-mix-and-match-products' ),
		$child_item->get_quantity( 'max' ),
		wp_strip_all_tags( $child_item->get_product()->get_name() )
	);
  $input_args['checkbox_label'] = $checkbox_label;
  return $input_args;
}

// add_action('woocommerce_after_mini_cart',function(){
//   if(isset($_GET['minhtest'])){
//     print_r(get_field('bh_freegift_products', 'option'));
//     echo 'test';
//     die;
//   }
// });
