;((w, $) => {
  'use strict';

  const { product_page_add_to_cart_ajax } = B_HELPERS_DATA;

  $(() => {
    if(product_page_add_to_cart_ajax !== "1") return;

    $('body.single-product').on('click', 'form.cart button[type="submit"]', function(e) {
      e.preventDefault();
      e.stopPropagation();
      const $form = $(this).closest('form.cart');
      submitHandle($form);
    })

    $('body.single-product').on('submit', 'form.cart', async function(e) {
      e.preventDefault();
      e.stopPropagation();
      return;
    })

    const submitHandle = async ($form) => {
      const action = this.action; 
      let data = $form.serialize();

      if($form.find('button[name="add-to-cart"]').length > 0) { 
        data += '&add-to-cart=' + $form.find('button[name="add-to-cart"]').val();
      } 

      $form.addClass('b-helpers__loading');

      await $.post(action, data).done((data) => {
        // console.log(data); 
    
        if($(data).find('.entry-content .woocommerce-notices-wrapper .woocommerce-error').length > 0) {
          let errorMessage = $(data).find('.entry-content .woocommerce-notices-wrapper').first().clone();
          // console.log(errorMessage);
          $('#main > article').prepend(errorMessage)
          $form.removeClass('b-helpers__loading');
          // window.location.reload();

          jQuery('html').scrollTop(0)
          return;
        }

        $(document.body).trigger('wc_fragment_refresh');
        
        setTimeout(() => {
          $(document.body).trigger('added_to_cart');
          $form.removeClass('b-helpers__loading');
        }, 1000) // delay 1s
      });
    }
  })

})(window, jQuery) 