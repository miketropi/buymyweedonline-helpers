<?php
/**
 * Shortcode Desktop
 */

function b_helpers_algolia_search_func($atts = []) {
  $a = shortcode_atts([
    'classes' => '',
    'display' => 'desktop',// both, mobile, desktop
    'page_404'=>'false' 
  ], $atts );

  set_query_var('atts', $a);

  ob_start();
  b_helpers_load_template('algolia-search');
  return ob_get_clean();
}

add_shortcode('bh_algolia_search', 'b_helpers_algolia_search_func');

/**
 * Shortcode Mobile
 */

function b_helpers_algolia_search_func_mb($atts = []) {
  $a = shortcode_atts([
    'classes' => '',
    'display' => 'desktop',// both, mobile, desktop
    'page_404'=>'false' 
  ], $atts );

  set_query_var('atts', $a);

  if($atts['page_404']=='true') {
    if(is_404()) {
      return;
    }
  }
  ob_start();
  ?>
  <div id="ALGOLIA_SEARCH_MB" class="algolia-search algolia-search-container">
    <div class="algolia-search__inner">
      <div id="searchbox_MB"></div>
    </div>
    <div class="algolia-search__result-entry">
      <div class="col-1">
        <div class="result-entry-item">
          <h4><?php _e('Products', 'b_helpers') ?></h4>
          <div id="ALGOLIA_SEARCH_RESULT_PRODUCT_MB"></div> <!-- render result by js -->
        </div>

        <div class="result-entry-item">
          <h4><?php _e('Collections & Brands', 'b_helpers') ?></h4>
          <div id="ALGOLIA_SEARCH_RESULT_CAT_MB"></div> <!-- render result by js -->
        </div>
      </div>
      <div class="col-2">
        <div class="result-entry-item">
          <h4><?php _e('Pages', 'b_helpers') ?></h4>
          <div id="ALGOLIA_SEARCH_RESULT_PAGE_MB"></div> <!-- render result by js -->
        </div>

        <div class="result-entry-item">
          <h4><?php _e('Articles', 'b_helpers') ?></h4>
          <div id="ALGOLIA_SEARCH_RESULT_POST_MB"></div> <!-- render result by js -->
        </div>
      </div>
    </div>
  </div><!-- .algolia-search -->
  <?php
  return ob_get_clean();
}

add_shortcode('bh_algolia_search_mb', 'b_helpers_algolia_search_func_mb');


