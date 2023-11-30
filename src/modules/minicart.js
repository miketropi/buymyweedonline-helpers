/**
 * Mini cart
 */

;((w, $) => {
  'use strict';

  const updateQtyCartItem = () => {
    $('body').on('change', '.ic-cart-sidebar-wrapper input[name=quantity]', async function(e) {
      e.preventDefault();

      const $input = $(this);
      const value = this.value;
      const $li = $input.closest('li.woocommerce-mini-cart-item');
      const cartItemKey = $li.data('key');
      const cartNonce = $input.closest('ul.woocommerce-mini-cart').find('#woocommerce-cart-nonce').val();

      $li.addClass('b-helpers__loading')

      // form data
      const formData = new FormData();
      formData.append('update_cart', 'Update Cart');
      formData.append(`cart[${ cartItemKey }][qty]`, value)
      formData.append(`woocommerce-cart-nonce`, cartNonce)
      formData.append(`_wp_http_referer`, '/cart/')

      await $.ajax({
        type: 'POST',
        url: '/cart',
        processData: false,
        contentType: false,
        data: formData,
        success: (res) => {
          // console.log(res);
          $(document.body).trigger('wc_fragment_refresh');
        },
        error: (e) => {
          console.log(e);
        }
      })

      $li.removeClass('b-helpers__loading')
    })
  }

  const init = () => {
    updateQtyCartItem();
  }

  $(init);
})(window, jQuery)