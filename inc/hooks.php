<?php
/**
 * Hooks
 */

/**
 * ACF field hooks
 */
add_filter('acf/load_field/name=select_product_for_freegift', 'b_helpers_acf_field_choices_for_freegift_products');

function b_helpers_acf_field_choices_for_freegift_products($field) {
  // Reset choices
  $field['choices'] = b_helpers_get_woo_products_choices();
  return $field;
}

function b_helpers_add_modified_time( $src ) {
  $clean_src = remove_query_arg('ver', $src);
  $path      = wp_parse_url($src, PHP_URL_PATH);

  if ( $modified_time = @filemtime(untrailingslashit(ABSPATH) . $path) ) {
    $src = add_query_arg('ver', $modified_time, $clean_src);
  } else {
    $src = add_query_arg('ver', time(), $clean_src);
  }
  return $src;
}

add_filter('style_loader_src', 'b_helpers_add_modified_time', 99999999, 1);
add_filter('script_loader_src', 'b_helpers_add_modified_time', 99999999, 1);

function b_helpers_algolia_search_hit_wp_template() {
  ?>
  <script type="text/html" id="tmpl-ALGOLIA_SEARCH_RESULT_PRODUCT">
    <div class="algolia-result-item">
      <a class="__thumb" href="{{{ data.permalink }}}">
        <img src="{{{ data.images.thumbnail.url }}}" alt="{{{ data.post_title }}}" />
      </a>
      <div class="__entry">
        <h4><a href="{{{ data.permalink }}}">{{{ data.post_title }}}</a></h4>
        <div class="__meta-tag">
          <# if (data.taxonomies['pwb-brand'] != undefined && data.taxonomies['pwb-brand'] != '') { #>
          <span><?php _e('Brand', 'b_helpers') ?>: {{{ data?.taxonomies['pwb-brand']?.join(', ') }}}</span>
          <# } #>
        </div>
      </div>
    </div>
  </script> <!-- #tmpl-ALGOLIA_SEARCH_RESULT_PRODUCT -->

  <script type="text/html" id="tmpl-ALGOLIA_SEARCH_RESULT_CAT">
    <a class="__thumb" href="{{{ data.permalink }}}">{{{ data.name }}}</p></a>
  </script> <!-- #tmpl-ALGOLIA_SEARCH_RESULT_CAT -->

  <script type="text/html" id="tmpl-ALGOLIA_SEARCH_RESULT_PAGE">
    <a class="__thumb" href="{{{ data.permalink }}}">{{{ data.post_title }}}</p></a>
  </script> <!-- #tmpl-ALGOLIA_SEARCH_RESULT_PAGE -->

  <script type="text/html" id="tmpl-ALGOLIA_SEARCH_RESULT_POST">
    <a class="__thumb" href="{{{ data.permalink }}}">{{{ data.post_title }}}</p></a>
  </script> <!-- #tmpl-ALGOLIA_SEARCH_RESULT_POST -->
  <?php
}

add_action('wp_footer', 'b_helpers_algolia_search_hit_wp_template');
