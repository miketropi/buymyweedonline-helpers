<?php 
/**
 * Minicart template 
 * 
 */

?>
<div class="over-lay-custom-mini-cart"></div>
  <div class="ic-cart-sidebar-wrapper">
  <div class="ic-cart-sidebar-wrapper_header">
    <span><?php _e( 'Your Cart', 'b_helpers' ); ?></span>
    <div class="ic-cart-header-btn-close"><img src="<?php echo B_HELPERS_URI . '/images/close.webp' ?>"/></div>
  </div>
  <div class="ic-cart-sidebar-wrapper_body">
    <div class="cartcontents">
      <div class="widget_shopping_cart_content">
        <?php woocommerce_mini_cart(); ?>
      </div> 
    </div>  
  </div>
</div> 