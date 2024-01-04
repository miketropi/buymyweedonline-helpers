<?php
/**
 * Algolia search
 */
$render_search = false;
// if($atts['display'] == 'both'){
//   $render_search = true;
// }elseif($atts['display'] != 'both'){
//   if($atts['display'] == 'mobile' && wp_is_mobile()){
//     $render_search = true;
//   }
//   if($atts['display'] == 'desktop' && !wp_is_mobile()){
//     $render_search = true;
//   }
// }
// if(!$render_search) return;

if($atts['page_404']=='true') {
  if(is_404()) {
    return;
  }
}

?>
<div id="ALGOLIA_SEARCH" class="algolia-search algolia-search-container">
  <div class="algolia-search__inner">
    <div id="searchbox"></div>
    <!-- <input
      class="algolia-search__text-field"
      type="text" name="algolia-search-field"
      value=""
      placeholder="<?php _e('Search...', 'b_helpers') ?>"> -->
  </div>
  <div class="algolia-search__result-entry">
    <div class="col-1">
      <div class="result-entry-item">
        <h4><?php _e('Products', 'b_helpers') ?></h4>
        <div id="ALGOLIA_SEARCH_RESULT_PRODUCT"></div> <!-- render result by js -->
      </div>

      <div class="result-entry-item">
        <h4><?php _e('Collections & Brands', 'b_helpers') ?></h4>
        <div id="ALGOLIA_SEARCH_RESULT_CAT"></div> <!-- render result by js -->
      </div>
    </div>
    <div class="col-2">
      <div class="result-entry-item">
        <h4><?php _e('Pages', 'b_helpers') ?></h4>
        <div id="ALGOLIA_SEARCH_RESULT_PAGE"></div> <!-- render result by js -->
      </div>

      <div class="result-entry-item">
        <h4><?php _e('Articles', 'b_helpers') ?></h4>
        <div id="ALGOLIA_SEARCH_RESULT_POST"></div> <!-- render result by js -->
      </div>
    </div>
  </div>
</div><!-- .algolia-search -->
