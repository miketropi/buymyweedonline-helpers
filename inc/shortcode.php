<?php 
/**
 * Shortcode 
 */

function b_helpers_algolia_search_func($atts = []) {
  $a = shortcode_atts([
    'classes' => '',
  ], $atts );

  set_query_var('atts', $a);

  ob_start();

  return ob_get_clean();
}

add_shortcode('bh_algolia_search', 'b_helpers_algolia_search_func');