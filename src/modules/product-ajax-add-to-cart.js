;((w, $) => {
  'use strict';

  const { product_page_add_to_cart_ajax } = B_HELPERS_DATA;

  $(() => {
    if(product_page_add_to_cart_ajax !== "1") return;
    let inSubmit = false;

    // $('body.single-product').on('click', 'form.cart button[type="submit"]', function(e) {
    //   e.preventDefault();
    //   e.stopPropagation();
    //   if(inSubmit == true) return;
    //   console.log('click')
    //   $(this).closest('form').submit();
    // })

    $('body.single-product').on('submit', 'form.cart', async function(e) {
      // console.log('submit')
      e.preventDefault();
      e.stopPropagation();

      if(inSubmit == true) return;

      const $form = $(this);
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
          inSubmit = false;
        }, 1000) // delay 1s
      });
    })
  })

})(window, jQuery) 