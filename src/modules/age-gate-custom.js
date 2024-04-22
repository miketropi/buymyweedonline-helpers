/**
 * Age Gate
 */

;((w, $) => {
    'use strict';

    var age_gate_main_action = function () {
        if ($('.wrapper-age-gate-custom').length<=0) {
            return;
        }
        var time_length_init = Number($('.remember-length-age-gate').val());
        if (time_length_init<=0) {
            localStorage.time_expired=undefined;
        }
        var time_current_load_page_init = Date.now();
        var time_expired_init = Number(localStorage.getItem("time_expired"));
        var time_not_remember = localStorage.getItem('not_time_remember');
        if (time_expired_init) {
            if (time_current_load_page_init>time_expired_init) {
                $('.wrapper-age-gate-custom').css('display','flex');
                $('body').addClass('age-gate-active');
            }else {
                $('.wrapper-age-gate-custom').hide();
                $('body').removeClass('age-gate-active');
            }
        } else {
            if (time_not_remember=='1') {
                $('.wrapper-age-gate-custom').hide();
                $('body').removeClass('age-gate-active');
            } else {
                $('.wrapper-age-gate-custom').css('display','flex');
                $('body').addClass('age-gate-active');
            }
        }

        $('body').on('click','.button-confirm-age-gate .confirm-no',function(e) {
            e.preventDefault();
            var link_redirect = $(this).data('redirect');
            if (link_redirect) {
                // $('.inamate-loading-age-gate').css('display','flex');
                setTimeout(() => {
                    // $('.inamate-loading-age-gate').css('display','none');
                    $('.text-age-gate-error').show();
                    window.location.href = link_redirect;
                }, 1000);
            } else {
                // $('.inamate-loading-age-gate').css('display','flex');
                setTimeout(() => {
                    // $('.inamate-loading-age-gate').css('display','none');
                    $('.text-age-gate-error').show();
                }, 1000);
            }
        })

        $('body').on('click','.button-confirm-age-gate .confirm-yes',function(e) {
            e.preventDefault();
            var time_remember = Number($('.remember-length-age-gate').val());
            if (typeof(Storage) !== 'undefined') {
                if (time_remember>0) {
                    // $('.inamate-loading-age-gate').css('display','flex');
                    setTimeout(() => {
                        // $('.inamate-loading-age-gate').css('display','none');
                        $('.wrapper-age-gate-custom').hide();
                        $('body').removeClass('age-gate-active');
                        var time_expired = Date.now() + time_remember*24*60*60*1000;
                        localStorage.setItem("time_expired", time_expired);
                    }, 1000);

                } else {
                    // $('.inamate-loading-age-gate').css('display','flex');
                    setTimeout(() => {
                        // $('.inamate-loading-age-gate').css('display','none');
                        $('.wrapper-age-gate-custom').hide();
                        $('body').removeClass('age-gate-active');
                        localStorage.setItem('not_time_remember', '1');
                    }, 1000);
                }
            } else {
                alert('Your browser does not support Storage');
            }
        })
    }


    $(document).ready(function() {
        age_gate_main_action();
    })
    $(window).on('resize',function() {

    });
    $( window ).on( 'scroll', function() {

    });

    $(window).on('load', function() {
        var is_trigger = false;
        $('.wcapf-filter-title').on('click',function(e){
            //console.log('test!!');
            if(!is_trigger){
              //wcapf
              //e.preventDefault();
              var filter_n = $(this).closest('.wcapf-filter');
              var id_current = filter_n.find('.wcapf-filter-inner').attr('id');
              var option     = filter_n.find('.wcapf-filter-inner');

              $('.wcapf-filter').each(function(){
                  var filter = $(this);
                  var option = filter.find('.wcapf-filter-inner');
                  var button = filter.find('.wcapf-filter-title');
                  var id     = filter.find('.wcapf-filter-inner').attr('id');
                  //option
                  if(option.css('display') == 'block' && id_current != id){
                    is_trigger = true;
                    button.click();
                  }
              });

              is_trigger = false;
            }

        });

        $(document).on('change','input[name="_woo-price"]',function(){
           var val = $(this).val();
           // if(val != 'custom'){
           //   var price = val.split('-');
           //   var min_price = price[0];
           //   var max_price = price[1];
           //   $(document).find('.wcapf-range-slider').data('min-value',min_price);
           //   $(document).find('.wcapf-range-slider').data('max-value',max_price);
           //   $(document).find('.max-value').trigger('input');
           // }

           if(val === 'custom'){
             $(document).find('.wcapf-filter-price .range-values').css('display','flex');
           }else{
             $(document).find('.wcapf-filter-price .range-values').css('display','none');
           }
        });

        $(document).on('wcapf_after_updating_products',function($res){
            //$(document).find('.wcapf-form .wcapf-filter-inner').hide();
            $(document).find('.wcapf-filter').each(function(){
                var filter = $(this);
                var option = filter.find('.wcapf-filter-inner');
                var button = filter.find('.wcapf-filter-title');
                var id     = filter.find('.wcapf-filter-inner').attr('id');
                //option
                if(option.css('display') == 'block'){
                  button.click();
                }
            });
        })

    });

    $(window).unload(function() {
        localStorage.not_time_remember=undefined;
    });

})(window, jQuery)
