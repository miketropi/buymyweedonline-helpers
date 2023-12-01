<?php 
/**
 * Algolia search
 */

?>
<div class="algolia-search algolia-search-container">
  <div class="algolia-search__inner">
    <input 
      class="algolia-search__text-field" 
      type="text" name="algolia-search-field" 
      value="" 
      placeholder="<?php _e('Search...', 'b_helpers') ?>">
  </div>
  <div class="algolia-search__result-entry">
    <div>
      <div id="ALGOLIA_SEARCH_RESULT_PRODUCT"></div>
      <div id="ALGOLIA_SEARCH_RESULT_CAT"></div>
    </div>
    <div>
      <div id="ALGOLIA_SEARCH_RESULT_PAGE"></div>
      <div id="ALGOLIA_SEARCH_RESULT_POST"></div>
    </div>
  </div>
</div><!-- .algolia-search -->