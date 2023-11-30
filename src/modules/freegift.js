/**
 * Free Gift
 */

;((w, $) => {
  'use strict';

  const addToCartGift = () => {
    $('body').on('click', '.free-gift__product-add-to-cart', async function(e) {
      e.preventDefault();
      const $thisbutton = $(this);
      const $li = $thisbutton.closest('li.free-gift__product-item');
      const {productType, productVariationId, productId} = this.dataset;

      $li.addClass('b-helpers__loading');

      const data = {
        action: 'b_helpers_woocommerce_ajax_add_to_cart_free_gift',
        product_id: parseInt(productId),
        quantity: 1,
        variation_id: parseInt((productVariationId ? productVariationId : 0)),
      };

      $(document.body).trigger('adding_to_cart', [$thisbutton, data]);

      await $.ajax({
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
            window.location = response.product_url;
            // console.log(response)
            return;
          } else {
            $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $thisbutton]);
          }
        },
      });

      $li.removeClass('b-helpers__loading');
    })
  }

  const init = () => {
    addToCartGift();
  }

  $(init)
})(window, jQuery)