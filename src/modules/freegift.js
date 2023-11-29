/**
 * Free Gift
 */

;((w, $) => {
  'use strict';

  const addToCartGift = () => {
    $('body').on('click', '.free-gift__product-add-to-cart', function(e) {
      e.preventDefault();
      const $thisbutton = $(this);
      const {productType, variationId, productId} = this.dataset;

      const data = {
        action: 'b_helpers_woocommerce_ajax_add_to_cart_free_gift',
        product_id: parseInt(productId),
        quantity: 1,
        variation_id: parseInt((variationId ? variationId : 0)),
      };

      $(document.body).trigger('adding_to_cart', [$thisbutton, data]);

      $.ajax({
        type: 'post',
        url: wc_add_to_cart_params.ajax_url,
        data: data,
        beforeSend: function (response) {
          $thisbutton.removeClass('added').addClass('loading');
        },
        complete: function (response) {
          $thisbutton.addClass('added').removeClass('loading');
        },
        success: function (response) {
          if (response.error && response.product_url) {
            // window.location = response.product_url;
            console.log(response)
            return;
          } else {
            $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $thisbutton]);
          }
        },
      });
    })
  }

  const init = () => {
    addToCartGift();
  }

  $(init)
})(window, jQuery)