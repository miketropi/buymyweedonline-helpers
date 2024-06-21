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


function be_display_all_product_reviews($atts) {
    ob_start();

    $atts = shortcode_atts(array(
        'reviews_per_page' => 24 // Default number of reviews per page
    ), $atts, 'all_product_reviews');

    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

    $args = array(
        'post_type' => 'product',
        'status'    => 'approve',
        'number'    => $atts['reviews_per_page'],
        'offset'    => ($paged - 1) * $atts['reviews_per_page']
    );

    $comments = get_comments($args);
    // Calculate average rating
    $all_reviews = get_comments(array(
         'post_type' => 'product',
         'status'    => 'approve',
         'number'    => 0 // Get all comments
    ));
    $total_comments = count($all_reviews);
    $total_pages = ceil($total_comments / $atts['reviews_per_page']);
    $total_rating = 0;

    foreach ($all_reviews as $review) {
        $rating = intval(get_comment_meta($review->comment_ID, 'rating', true));
        $total_rating += $rating;
    }

    $average_rating = $total_comments ? number_format($total_rating / $total_comments) : 0;
    if ($comments) {
        echo '<div class="average-total-reviews"><img src="/wp-content/plugins/review-slider-for-woocommerce/public/partials/imgs/stars_'.$average_rating.'_yellow.png" alt="rating '.$average_rating.'" />  Based on '.$total_comments.' Reviews</div>';
        echo '<div class="all-product-reviews">';
        foreach ($comments as $comment) {

            //Product
            $product_id = $comment->comment_post_ID;
            $product = get_post( $product_id );
            $thumbnail_id = get_post_meta( $product_id, '_thumbnail_id', TRUE );
            $thumbnail_url = wp_get_attachment_image_src( $thumbnail_id, 'thumbnail' );
            $thumbnail_url = $thumbnail_url[0];

            //Rating
            $comment_id = $comment->comment_ID;
            $rating = get_comment_meta( $comment_id, 'rating', TRUE );
            if( $rating > 5 ) {
                $rating = 5;
            }

            if ( $rating == NULL || $rating == '' || $rating == '0' ) {
                $rating = '1';
            }

            //Date
            $comment_date = get_comment_date( 'd/m/Y', $comment_id );

            echo '<div class="single-review">';
            echo '<p class="review-date">'.$comment_date.'</p>';
            echo '<p class="review-rating"><img src="/wp-content/plugins/review-slider-for-woocommerce/public/partials/imgs/stars_'.$rating.'_yellow.png" alt="rating '.$rating.'" /></p>';
            echo '<p id="less-more-cmt-'.$comment_id.'" class="review-content">' . wp_trim_words( $comment->comment_content , 24, '... <a href="#show-comment-'.$comment_id.'" class="read-more-btn" title="Read more">Read more</a>' ). '</p>';
            ?>
            <div id="show-comment-<?php echo $comment_id ?>" class="review-quote">
              <?php
                  if ( $comment->comment_content != NULL) {
                      ?>
                      <span class="quote"><?php echo nl2br( $comment->comment_content ); ?></span> <a href="#less-more-cmt-<?php echo $comment_id ?>" class="lest-more-btn">Less more</a>
                      <?php
                  }
              ?>
            </div>
            <?php
              echo '<a href="'.get_permalink($product_id).'" class="product-review">';
                    if ( $thumbnail_url != NULL ) {
                        ?>
                        <img class="thumbnail-product" src="<?php echo $thumbnail_url; ?>" alt="<?php echo $product->post_title; ?>" />
                        <?php
                    } else {
                        echo wc_placeholder_img( 'thumbnail' );
                    }
                    echo '<span>'.$product->post_title.'</span>';
              echo '</a>';
             ?>
            <div class="review-author <?php echo ( $comment->comment_author != NULL) ? 'a-verified' : 'a-not-verifed'; ?>">
                <span><?php echo ( $comment->comment_author != NULL) ? $comment->comment_author : 'Anonymous'; ?></span>
            </div>
            <?php
            echo '</div>';
        }
        echo '</div>';

        // Pagination
        $pagination_args = array(
            'base' => @add_query_arg('paged', '%#%'),
            'format' => '?paged=%#%',
            'current' => max(1, $paged),
            'total' => $total_pages,
            'prev_text' => __('«'),
            'next_text' => __('»')
        );
        echo '<div class="be-pagination">';
        echo paginate_links($pagination_args);
        echo '</div>';
        ?>
        <script type="text/javascript">
            // A $( document ).ready() block.
            jQuery( document ).ready(function() {
              jQuery('.read-more-btn,.lest-more-btn').click(function(){
                 var href = jQuery(this).attr('href');
                 jQuery(this).parent().hide();
                 jQuery(href).show();
              });
            });
        </script>
        <?php
    } else {
        echo 'No reviews found.';
    }

    return ob_get_clean();
}
add_shortcode('all_product_reviews', 'be_display_all_product_reviews');
