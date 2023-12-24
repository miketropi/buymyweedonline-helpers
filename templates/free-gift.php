<?php 
/**
 * Free Gift template
 */

$lock_icon = '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M12 14.5V16.5M7 10.0288C7.47142 10 8.05259 10 8.8 10H15.2C15.9474 10 16.5286 10 17 10.0288M7 10.0288C6.41168 10.0647 5.99429 10.1455 5.63803 10.327C5.07354 10.6146 4.6146 11.0735 4.32698 11.638C4 12.2798 4 13.1198 4 14.8V16.2C4 17.8802 4 18.7202 4.32698 19.362C4.6146 19.9265 5.07354 20.3854 5.63803 20.673C6.27976 21 7.11984 21 8.8 21H15.2C16.8802 21 17.7202 21 18.362 20.673C18.9265 20.3854 19.3854 19.9265 19.673 19.362C20 18.7202 20 17.8802 20 16.2V14.8C20 13.1198 20 12.2798 19.673 11.638C19.3854 11.0735 18.9265 10.6146 18.362 10.327C18.0057 10.1455 17.5883 10.0647 17 10.0288M7 10.0288V8C7 5.23858 9.23858 3 12 3C14.7614 3 17 5.23858 17 8V10.0288" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/> </svg>';
$cart_total = WC()->cart->total;
?>
<div class="free-gift">
  <div class="free-gift__inner">
    <div class="free-gift__message"><?php echo b_helpers_free_gift_message(); ?></div>
    <ul class="free-gift__products">
      <?php if($freegift_products && count($freegift_products) > 0) : 
        foreach($freegift_products as $_index => $_p) :
          if(empty($_p['select_product_for_freegift'])) continue;
          
          $_product = wc_get_product($_p['select_product_for_freegift']);
          $type = $_product->get_type();
          $unlocked = $cart_total <= $_p['unlock_amount'] ? false : true;
          $classes = ['free-gift__product-item', $unlocked ? '__unlocked' : ''];

          $product_id = ($type == 'variation') ? $_product->get_parent_id() : $_product->get_id();
          $variation_id = ($type == 'variation') ? $_product->get_id() : 0;
          ?>
          <li class="<?php echo implode(' ', $classes); ?>">
            <div class="free-gift__product">
              <div class="free-gift__product-thumbnail">
                <?php echo $_product->get_image('thumbnail'); ?>
              </div>
              <div class="free-gift__product-title">
                <span class="free-gift__unlock-amount"><?php _e('Spend', 'b_helpers') ?> <?php echo wc_price($_p['unlock_amount']) ?>+</span>
                <h4><?php echo $_p['name']; // $_product->get_title(); ?></h4>
                <div class="free-gift__product-price">
                  <?php echo $_product->get_price_html(); ?> - <span class="free-gift__free-text"><?php _e('Free', 'b_helpers') ?></span>
                </div>
              </div>
              <div class="free-gift__product-unlock-status">
                <?php 
                if($unlocked == true) {
                  ?> 
                  <a 
                    href="<?php echo $_product->add_to_cart_url(); ?>" 
                    class="free-gift__product-add-to-cart" 
                    data-product-type="<?php echo $type; ?>" 
                    data-product-variation-id="<?php echo $variation_id ?>" 
                    data-product-id="<?php echo $product_id ?>"> 
                    <?php _e('Add to Cart', 'b_helpers'); ?>
                  </a> 
                  <?php
                } else {
                  echo '<span class="free-gift__product-lock-icon">'. $lock_icon .'</span>'; 
                }
                ?>
              </div>
            </div>
          </li>
          <?php
        endforeach;
      endif; ?>
    </ul>
  </div>
</div>