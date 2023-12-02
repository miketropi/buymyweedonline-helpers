;((w, $) => {
  'use strict';

  const { product_page_add_to_cart_ajax } = B_HELPERS_DATA;

  $(() => {
    if(product_page_add_to_cart_ajax !== "1") return;

    $('body.single-product').on('submit', 'form.cart', async function(e) {
      e.preventDefault();
      const $form = $(this);
      const action = this.action;
      const data = $form.serialize();

      $form.addClass('b-helpers__loading');

      await $.post(action, data).done(() => {
        $(document.body).trigger('wc_fragment_refresh');
        
        setTimeout(() => {
          $(document.body).trigger('added_to_cart');
          $form.removeClass('b-helpers__loading');
        }, 1000) // delay 1s
      });
    })
  })

})(window, jQuery) 