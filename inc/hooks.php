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

// add_filter('style_loader_src', 'b_helpers_add_modified_time', 99999999, 1);
// add_filter('script_loader_src', 'b_helpers_add_modified_time', 99999999, 1);

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



// age gate app

add_action('wp_head','b_custom_template_age_gate');

//optimize site
{
  add_action('wp_print_styles','remove_wp_enqueue_styles',999);
  add_action('wp_print_scripts','remove_wp_enqueue_scripts',999);

  add_action( 'init', 'disable_embeds_code_init_mealprep', 9999 );
  function disable_embeds_code_init_mealprep() {
  		if ( ! is_admin() && ! isset($_GET['url']) ) {
  			 // Remove the REST API endpoint.
  			 remove_action( 'rest_api_init', 'wp_oembed_register_route' );

  			 // Turn off oEmbed auto discovery.
  			 add_filter( 'embed_oembed_discover', '__return_false' );

  			 // Don't filter oEmbed results.
  			 remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );

  			 // Remove oEmbed discovery links.
  			 remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

  			 // Remove oEmbed-specific JavaScript from the front-end and back-end.
  			 remove_action( 'wp_head', 'wp_oembed_add_host_js' );
  			 add_filter( 'tiny_mce_plugins', 'disable_embeds_tiny_mce_plugin_mealprep' );

  			 // Remove all embeds rewrite rules.
  			 add_filter( 'rewrite_rules_array', 'disable_embeds_rewrites_mealprep' );

  			 // Remove filter of the oEmbed result before any HTTP requests are made.
  			 remove_filter( 'pre_oembed_result', 'wp_filter_pre_oembed_result', 10 );
   	 }
  }

  function disable_embeds_tiny_mce_plugin_mealprep($plugins) {
  	return array_diff($plugins, array('wpembed'));
  }

  function disable_embeds_rewrites_mealprep($rules) {
  	foreach($rules as $rule => $rewrite) {
  			if(false !== strpos($rewrite, 'embed=true')) {
  					unset($rules[$rule]);
  			}
  	}
  	return $rules;
  }

  //Remove skip link
  add_action( 'after_setup_theme', function() {
      remove_action( 'generate_before_header', 'generate_do_skip_to_content_link', 2 );
  }, 50 );
}
