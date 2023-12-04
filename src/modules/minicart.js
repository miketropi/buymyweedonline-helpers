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

  const toggleMiniCart = () => {
    $(document.body).on( 'added_to_cart',  () => {
      $(document.body).addClass('active-mini-cart');
    });
    

    $(document.body).on('click','.over-lay-custom-mini-cart', (e) => {
      $(document.body).removeClass('active-mini-cart');
    })

    $(document.body).on('click', '.inside-top-bar .et-cart-info', (e) => {
      e.preventDefault();
      $(document.body).addClass('active-mini-cart');  
    });

    // $('a.shopping-cart').on('click touchstart', e => {
    //   e.preventDefault();
    //   $(document.body).addClass('active-mini-cart'); 
    // })
    
    $(document.body).on('click', '.ic-cart-header-btn-close', (e) => {
      $(document.body).removeClass('active-mini-cart');
    });
  }

  const btnQty = () => {
    $(document.body).on('click', '.woocommerce-mini-cart .minus', function(e) {
      e.preventDefault();
      const qtyInput = $(this).parent().find('input[name=quantity]');
      let min = 1;

      const currentValue = parseInt(qtyInput.val());
      let newValue = currentValue - 1;
      newValue = (newValue <= min) ? min : newValue

      qtyInput.val(newValue).trigger('change');
    })

    $(document.body).on('click', '.woocommerce-mini-cart .plus', function(e) {
      e.preventDefault();
      const qtyInput = $(this).parent().find('input[name=quantity]');
      // const min = parseInt(qtyInput.attr('min'));
      const max = parseInt(qtyInput.attr('max'));

      const currentValue = parseInt(qtyInput.val());
      let newValue = currentValue + 1;
      newValue = (max && newValue >= max) ? max : newValue;

      qtyInput.val(newValue).trigger('change');
    })
  }

  const init = () => {
    toggleMiniCart();
    updateQtyCartItem();
    btnQty();
  }

  $(init);
  $(w).on('load', () => {
    $(document.body).trigger('wc_fragment_refresh');
  })
})(window, jQuery)