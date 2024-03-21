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

  //Render css single product
  add_action('wp_head' , 'be_optimize_render_css_single_product', 999999 );
  function be_optimize_render_css_single_product(){

    if(is_singular('product')):
      ?>
      <style media="screen">
        .wpcsb-wrapper.wpcsb-active{
          display: none;
        }
        .products_ingredients_cbd_thc span.strain-value {
            font-size: 16px !important;
            color: #000;
            position: relative;
            top: 3px;
            font-weight: 800;
        }
        .products_ingredients_cbd_thc span.strain-name {
            font-size: 12px !important;
            color: #000;
        }
        .products_ingredients_cbd_thc canvas {
            position: absolute;
            margin-left: -55px !important;
            margin-top: -43px !important;
            width: 85px !important;
            height: 85px !important;
        }
        @media (max-width: 768px){
          .do-quantity-buttons form .quantity:not(.buttons-added):not(.hidden):after, .do-quantity-buttons form .quantity:not(.buttons-added):not(.hidden):before, .woocommerce form .quantity.buttons-added .minus, .woocommerce form .quantity.buttons-added .plus, .woocommerce form .quantity.buttons-added .qty {
              width: 30px !important;
          }
          .wpcsb-wrapper.wpcsb-active{
            display: block;
          }
        }
      </style>
    <?php
    endif;

    if(is_product_category()){
      ?>
      <style media="screen">
        .woocommerce ul.products li.product .price, .woocommerce div.product p.price {
          color: #222222 !important;
        }
      </style>
      <?php
    }

  }

}


//Custom template recipe
add_filter('wp_delicious_get_template' , 'custom_wp_delicious_get_template', 10 , 2 );
function custom_wp_delicious_get_template($template , $template_name){
  if($template_name == "recipes-list.php"){
    $template = B_HELPERS_DIR . 'templates/recipe-list.php';
  }
  return $template;
}

//Create Strain Info post type
function bmwo_custom_post_product() {
  $labels = array(
    'name'               => _x( 'Strain Info', 'buymyweedonline' ),
    'singular_name'      => _x( 'Strain Info', 'buymyweedonline' ),
    'add_new'            => _x( 'Add New', 'buymyweedonline' ),
    'add_new_item'       => __( 'Add New Strain Info' ),
    'edit_item'          => __( 'Edit Strain Info' ),
    'new_item'           => __( 'New Strain Info' ),
    'all_items'          => __( 'All Strain Info' ),
    'view_item'          => __( 'View Strain Info' ),
    'search_items'       => __( 'Search Strain Info' ),
    'not_found'          => __( 'No Strain Info found' ),
    'not_found_in_trash' => __( 'No Strain Info found in the Trash' ),
    'parent_item_colon'  => ’,
    'menu_name'          => 'Strain Info'
  );
  $args = array(
    'labels'        => $labels,
    'public'        => true,
    'menu_position' => 5,
    'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
    'has_archive'   => true,
    'show_in_rest' => true,
    'rewrite' => array('slug' => 'strain-info')
  );
  register_post_type( 'strain-info', $args );
}
add_action( 'init', 'bmwo_custom_post_product' );

/*
* Custom body class
*/

add_filter( 'body_class', 'bmwo_custom_class' );
function bmwo_custom_class( $classes ) {
	if ( is_singular('strain-info') ) {
        global $post;
        $classes[] = 'page-id-'.$post->ID;
    }
	return $classes;
}

/**
 * Add a sidebar for category pages.
 */
function bmwo_theme_slug_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Category Products Sidebar', 'textdomain' ),
		'id'            => 'sidebar-category-product',
		'description'   => __( 'Widgets in this area will be shown on category product pages.', 'bmwo' ),
		'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget'  => '</li>',
		'before_title'  => '<h2 class="widgettitle">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'bmwo_theme_slug_widgets_init' );
