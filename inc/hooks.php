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
    'parent_item_colon'  => __( 'Parent Strain Info' ),
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

add_action( 'init' , 'update_taxonomy_brand_for_all_products' );
function update_taxonomy_brand_for_all_products(){
  if(isset($_GET['update_brand']) && $_GET['update_brand']){

     //Values
     $pro_id = isset($_GET['id']) ? $_GET['id'] : 0;
     $limit = 100;
     $paged = isset($_GET['paged']) ? $_GET['paged'] : 1;

     //Query
     if($pro_id){
        $args = array(
          'post_type' => 'product',
          'post_status' => 'publish',
          'p' => $pro_id
        );
     }else{
       $args = array(
         'post_type' => 'product',
         'post_status' => 'publish',
         'posts_per_page' => -1,
         //'paged' => $paged
       );
     }

     $products = get_posts($args);
     $count = 1;
     $brands = array('Craft Cannabis');
     foreach ($products as $key => $product) {
        $p_id = $product->ID;
        $brands = get_the_terms( $p_id , 'pwb-brand' );
        if(empty($brands)){
          echo $count. ': ' .$p_id . '<br>';
          wp_set_object_terms($p_id, 'Craft Cannabis' ,'pwb-brand', true);
          $count++;
        }
        // echo "<pre>";
        // print_r($brands);
        // echo "</pre>";
     }

     die;
  }
}

add_action( 'init' , 'update_taxonomy_for_all_products' );
function update_taxonomy_for_all_products(){
  if(isset($_GET['update_tax']) && $_GET['update_tax']){

     //Values
     $pro_id = isset($_GET['id']) ? $_GET['id'] : 0;
     $limit = 100;
     $paged = isset($_GET['paged']) ? $_GET['paged'] : 1;

     //Terms  Effects
     $parent_cats = [];
     $relation_effect_terms = array(
       'Positive Mood' => '"Happy", "Uplifted", "Uplifted Mood", "Mental Euphoria", "Happines", "Upliftment", "Motivation", "Boost of Happiness", "happy relaxation calm body buzz sleepy", "Body Buzz"',
       'Relaxation &amp; Calm' => '"Relax", "Relaxation", "Relaxation Upliftment", "relax tranquil euphoric energetic buzz", "Relaxed High"',
       'Social &amp; Sociability' => '"Sociable", "conversation enhancer"',
       'Physical Relaxation' => '"Relaxed", "Sleepy", "Body High", "Relax", "Relaxation", "Relaxation Upliftment", "relax tranquil euphoric energetic buzz", "Relaxed high"',
       'Euphoria &amp; Pleasure' => '"Euphoria", "Euphoric", "Euphoric Happy", "High Pleasure", "Psychoactive", "Increased Euphoria"',
       'Creativity &amp; Focus' => '"Creative", "Focus", "Focused", "Creativity", "Energy Creativity", "Increased Creativity", "Creative Uplifted", "Enhanced Cognitive Physical Functioning"',
       'Energy &amp; Productivity' => '"Energizing", "Energy", "Energetic", "Boost Energy", "Energized", "High Energy", "Motivation", "concentration enhancer"',
       'Medical &amp; Therapeutic' => '"anti-inflammatory", "good for chronic pain", "pain reliever", "good for pain", "Chronic Pain", "Alleviating Symptoms Of Epilepsy", "Reduces anxiety depression", "inflammation"',
       'Appetite &amp; Digestive Health' => '"Hungry", "Increase Appetite", "Increased Appetite", "Good for Appetite", "Appetite loss"',
       'Pain Relief' => '"anti-inflammatory", "good for chronic pain", "pain reliever", "good for pain", "Chronic Pain"",Pain Relief", "Relief Pain"',
       'Mental Health' => '"Reduces anxiety depression", "Stress management Induce appetite", "Anxiety", "stress anxiety reduction", "Stress relief", "Stress"',
       //'Digestive &amp; Appetite' => '"Hungry", "Increase Appetite", "Increased Appetite", "Good for appetite", "Appetite loss"',
       'Neurological Benefits' => '"Alleviating Symptoms Of Epilepsy"',
       'Inflammation &amp; Immunity' => '"inflammation", "Anti-inflammatory", "good for inflammatory pain"',
       'Sleep Aid' => '"Insomnia", "Sleep management", "Induces sleepiness", "Sedative Lazy", "Sleepiness", "Sedative"',
       'Skin Health' => '"Moisturize skin", "Cold Therapy"',
       'Pet Health' => '"Enhance your pet’s health vitality"',
       'General Wellness' => '"Boost Health", "Everyday wellness", "Comfort support", "Overall calm", "Well-being"'
     );

     $relation_flavour_terms = array(
       'Fruit' => '"Grape", "Fruity", "Orange", "Lemon", "Mango", "Grapefruit", "fresh berries", "lime", "granny smith apple", "Strawberry", "Tangerine", "Peach", "Watermelon", "Kiwi", "Apple", "Blueberry", "Berry", "Blackberry"',
       'Citrus' => '"Lemon", "Grapefruit", "Orange", "Citrus", "Lime", "Lemon ginger", "Citrus Spice", "Tangerine", "Lemon Aid"',
       'Sweet &amp; Sugary' => '"Sweet", "Vanilla", "Honey", "Sugary", "Sweet vanilla", "Sweet fruity notes and hints of fresh berries", "Sweet Fruity Strawberries", "Sweet Sherbet", "Sweet Sour Citrus"',
       'Spicy &amp; Herbal' => '"Spicy herbal notes", "Spicy", "Herbal", "Peppery", "Mint", "Menthol", "Peppery Sage"',
       'Woody &amp; Earthy' => '"Woody", "Pine", "Earthy", "Wood", "Earthy undertones", "Sweet Earthy", "Earthy Notes", "Earthy Berries"',
       'Tropical' => '"Tropical", "Mango", "Pineapple", "tropical fruity flavors", "Tropical citrus sour berries galore"',
       'Dessert' => '"Cake Batter", "Cookie", "Marshmallow", "Cheesecake", "Banana", "Chocolate Mint", "Cream", "Vanilla Pine"',
       'Miscellaneous' => '"Wedding Cake", "Pungent spicy", "Chemical", "Diesel", "Rubber", "Bubble Gum", "Skunky", "Coffee", "Creamy Vanilla"'
     );

     $list_terms = array('woo-effects','woo-flavours');
     foreach ($list_terms as $name_term ) {
       $data_terms = get_terms( array('taxonomy'   => $name_term ,'hide_empty' => false));
       foreach ($data_terms as $data_term) {
          $parent_cats[$data_term->name] = $data_term->term_id;
       }
     }

     //Query
     if($pro_id){
        $args = array(
          'post_type' => 'product',
          'post_status' => 'publish',
          'p' => $pro_id
        );
     }else{
       $args = array(
         'post_type' => 'product',
         'post_status' => 'publish',
         'posts_per_page' => $limit,
         'paged' => $paged
       );
     }

     $products = get_posts($args);

     foreach ($products as $product) {
       // code...
       //echo $product->ID . '<br>';
       $p_id = $product->ID;
       $strains = $effects = $flavours = $cbds = $thcs = array();
       $product_specs = get_field('product_specs',$p_id);
       $strain_key = $effect_key = $effect_key = $flavour_key = $cbd_key = $thc_key = array();
       $terms = get_the_terms( $p_id , 'product_cat' );

       foreach ($terms as $term) {
         if($term->name == 'Balanced Hybrids'){
           $strains[] = 'Hybrid';
         }
         if($term->name == 'Indica Cannabis Strains'){
           $strains[] = 'Indica Dominant';
         }
         if($term->name == 'Sativa Cannabis Strains'){
           $strains[] = 'Sativa Dominant';
         }
       }

       foreach ($product_specs as $key => $value) {

          $name = trim(strtolower($value));
          //Strains
          // if($name == 'strain lineage'){
          //   $strain_key[] = str_replace('name','value',$key);
          //   $strain_key[] = str_replace('specs_name','_specs_value',$key);
          // }
          // if(in_array($key,$strain_key)){
          //   $strains = explode(',',$value);
          // }

          //Effects
          if($name == 'effects' || $name == 'effect'){
            $effect_key[] = str_replace('name','value',$key);
            $effect_key[] = str_replace('specs_name','_specs_value',$key);
          }
          if(in_array($key,$effect_key)){
            $effects = explode(',',$value);
          }

          //Flavours
          if($name == 'flavours' || $name == 'flavour' || $name == 'flavors' || $name == 'flavor'){
            $flavour_key[] = str_replace('name','value',$key);
            $flavour_key[] = str_replace('specs_name','_specs_value',$key);
          }
          if(in_array($key,$flavour_key)){
            $flavours = explode(',',$value);
          }

          //CBD
          if(strpos(trim($name),'cbd')){
            $cbd_key[] = str_replace('name','value',$key);
            $cbd_key[] = str_replace('specs_name','_specs_value',$key);
          }
          if(in_array($key,$cbd_key)){
            $cbds[] = trim($value);
          }

          //THC
          if($name =='strain thc %'){
            $thc_key[] = str_replace('name','value',$key);
            $thc_key[] = str_replace('specs_name','_specs_value',$key);
          }
          if(in_array($key,$thc_key)){
            $thcs[] = trim($value);
          }

       }

       if(!empty($strains)){
         wp_set_object_terms($p_id, array() ,'woo-strains', false); //reset
         foreach ($strains as $key => $value) {
           $value = trim(str_replace('and','',$value));
           $strains[$key] = trim(str_replace('And','',$value));
         }
         wp_set_object_terms($p_id, $strains ,'woo-strains', true); //add
       }

       if(!empty($effects)){
         //Reset option product
         wp_set_object_terms($p_id, array() ,'woo-effects', false); //reset
         foreach ($effects as $key => $value) {
           $value = trim(str_replace('and','',$value));
           $value = trim(str_replace('.','',$value));
           $value = trim(str_replace('  ',' ',$value));
           $effects[$key] = trim(str_replace('And','',$value));
           //echo $value . '<br>';
         }
         $list_ids = wp_set_object_terms($p_id, $effects ,'woo-effects', true); //add

         //Update rules child category
         foreach ($effects as $effect) {
            $term = term_exists( $effect , 'woo-effects' );
            $effect_check = trim(strtolower($effect));
            $parent = 0;
            foreach ($relation_effect_terms as $n => $relation_term) {
              $list_child = strtolower($relation_term);
              if(strpos($list_child , '"'.$effect_check.'"') !== false && isset($parent_cats[$n])){
                $parent = $parent_cats[$n];
                $list_ids[] = $parent;
                // if($effect == 'Creative Uplifted')
                //   echo $list_child . '<br>';
                $parent_infor = get_term_by('id', $parent , 'woo-effects' );
                if(!empty($parent_infor) && $parent_infor->parent > 0){
                  $list_ids[] = $parent_infor->parent;
                }
              }
            }
            // if(!$parent){
            //   foreach ($list_ids as $key => $id) {
            //       if($id == $term['term_id']) unset($list_ids[$key]);
            //   }
            // }
            //echo $term['term_id'] . ' - ' . $parent . '<br>';
            wp_update_term( $term['term_id'], 'woo-effects', array(
              'parent' => $parent
            ) );
         }
         //print_r($list_ids);
         $list_ids = array_filter( array_map( 'intval', (array) $list_ids ) );
         wp_set_object_terms( $p_id, $list_ids , 'woo-effects' );

       }

       if(!empty($flavours)){
         //Reset option product
         wp_set_object_terms($p_id, array() ,'woo-flavours', false); //reset
         foreach ($flavours as $key => $value) {
           $value = trim(str_replace('and','',$value));
           $value = trim(str_replace('.','',$value));
           $value = trim(str_replace('  ',' ',$value));
           $flavours[$key] = trim(str_replace('And','',$value));
         }
         $list_ids = wp_set_object_terms($p_id, $flavours ,'woo-flavours', true); //add

         //Update rules child category
         foreach ($flavours as $flavour) {

            $term = term_exists( $flavour , 'woo-flavours' );
            $flavour_check = trim(strtolower($flavour));
            $parent = 0;

            foreach ($relation_flavour_terms as $n => $relation_term) {
              $list_child = strtolower($relation_term);
              if(strpos($list_child , '"'.$flavour_check.'"') !== false && isset($parent_cats[$n])){
                $parent = $parent_cats[$n];
                $list_ids[] = $parent;
                $parent_infor = get_term_by('id', $parent , 'woo-flavours' );
                if(!empty($parent_infor) && $parent_infor->parent > 0){
                  $list_ids[] = $parent_infor->parent;
                }
              }
            }

            // if(!$parent){
            //   foreach ($list_ids as $key => $id) {
            //       if($id == $term['term_id']) unset($list_ids[$key]);
            //   }
            // }
            //echo $term['term_id'] . ' - ' . $parent . '<br>';
            wp_update_term( $term['term_id'], 'woo-flavours', array(
              'parent' => $parent
            ));

         }
         $list_ids = array_filter( array_map( 'intval', (array) $list_ids ) );
         wp_set_object_terms( $p_id, $list_ids , 'woo-flavours' );

       }

       if(!empty($cbds)){
         wp_set_object_terms($p_id, array() ,'woo-cbds', false); //reset
         foreach ($cbds as $key => $value) {
           $value = trim(str_replace('and','',$value));
           $cbds[$key] = trim(str_replace('And','',$value));
         }
         wp_set_object_terms($p_id, $cbds ,'woo-cbds', true); //add
       }
       if(!empty($thcs)){
         wp_set_object_terms($p_id, array() ,'thc', false); //reset
         $thc_terms = [];
         foreach ($thcs as $key => $thc) {
           $items = explode('-',$thc);
           foreach ($items as $item) {
             $item = trim(str_replace('%','',$item));
             if($item <= 15){
               $thc_terms[] = '0% – 15%';
             }

             if($item >= 15 && $item <= 20){
               $thc_terms[] = '15% – 20%';
             }

             if($item >= 20 && $item <= 25){
               $thc_terms[] = '20% – 25%';
             }

             if($item >= 25 && $item <= 30){
               $thc_terms[] = '25% – 30%';
             }

             if($item >= 30){
               $thc_terms[] = '30%+';
             }

           }
         }
         wp_set_object_terms($p_id, $thc_terms ,'thc', true); //add
       }


     }

    echo 'ok!'; die;
  }
}

add_filter('wpseo_title' , 'the_title_custom' );
function the_title_custom($title){
  if ( is_post_type_archive( 'strain-info' ) ) {
        return 'Explore Premium Cannabis Strains | BuyMyWeedOnline.cc';
   }
   return $title;
}

add_filter('wpseo_opengraph_title' , 'wpseo_opengraph_title_custom' );
function wpseo_opengraph_title_custom($title){
  // if ( is_post_type_archive( 'strain-info' ) ) {
  //       return 'Explore Premium Cannabis Strains';
  //  }
   return $title;
}

add_filter('wpseo_metadesc' , 'wpseo_metadesc_custom' );
function wpseo_metadesc_custom($decs){
  // if ( is_post_type_archive( 'strain-info' ) ) {
  //       return 'Discover a diverse selection of top-quality weed strains at BuyMyWeedOnline.cc. Find the perfect cannabis strains for your needs today!';
  //  }
   if(is_singular('recipe')){
     return false;
   }
   return $decs;
}

//Custom choose filters
add_filter('cs_filter_lable' , 'cs_filter_lable', 10);
function cs_filter_lable($label){
  $lb = str_replace('&nbsp;','',$label);
  $lb = str_replace('$','',$lb);
  $check_lb = explode('–',$lb);
  if(!empty($check_lb) && count($check_lb) > 1 && $check_lb[0] == '400.00' && $check_lb[1] == '999,999.00'){
    return "$400+";
  }
  return $label;
}


//Widget
function p_sidebar_registration() {
    $shared_args = array(
		'before_title'  => '<h2 class="widget-title subheading heading-size-3">',
		'after_title'   => '</h2>',
		'before_widget' => '<div class="widget %2$s"><div class="widget-content">',
		'after_widget'  => '</div></div>',
	);
    // Footer top bar.
    register_sidebar(
        array_merge(
            $shared_args,
            array(
                'name'        => __( 'Footer Top Bar', 'p' ),
                'id'          => 'footer-top-bar',
                'description' => __( 'Widgets in this area will be displayed in the second column in the footer.', 'p' ),
            )
        )
    );
    // Footer #6.
    register_sidebar(
        array_merge(
            $shared_args,
            array(
                'name'        => __( 'Footer Widget 6', 'p' ),
                'id'          => 'footer-6',
                'description' => __( 'Widgets in this area will be displayed in the second column in the footer.', 'p' ),
            )
        )
    );
}

add_action( 'widgets_init', 'p_sidebar_registration' );

add_action('generate_footer', 'fr_myplugin_footer');

function fr_myplugin_footer() { ?>
    <div id="footer-widgets" class="site footer-widgets">
				<div class="footer-widgets-container grid-container">
    				<div class="top-footer-widgets">
    					<?php if ( is_active_sidebar( 'footer-top-bar' ) ) { ?>
                            <div class="footer-widget-top">
                                <?php dynamic_sidebar( 'footer-top-bar' ); ?>
                            </div>
                        <?php } ?>
    				</div>
					<div class="inside-footer-widgets">
						<?php if ( is_active_sidebar( 'footer-1' ) ) { ?>
                            <div class="footer-widget-1">
                                <?php dynamic_sidebar( 'footer-1' ); ?>
                            </div>
                        <?php } ?>

                        <?php if ( is_active_sidebar( 'footer-2' ) ) { ?>
                            <div class="footer-widget-2">
                                <?php dynamic_sidebar( 'footer-2' ); ?>
                            </div>
                        <?php } ?>

                        <?php if ( is_active_sidebar( 'footer-3' ) ) { ?>
                            <div class="footer-widget-3">
                                <?php dynamic_sidebar( 'footer-3' ); ?>
                            </div>
                        <?php } ?>

                        <?php if ( is_active_sidebar( 'footer-4' ) ) { ?>
                            <div class="footer-widget-4">
                                <?php dynamic_sidebar( 'footer-4' ); ?>
                            </div>
                        <?php } ?>

                        <?php if ( is_active_sidebar( 'footer-5' ) ) { ?>
                            <div class="footer-widget-5">
                                <?php dynamic_sidebar( 'footer-5' ); ?>
                            </div>
                        <?php } ?>

                        <?php if ( is_active_sidebar( 'footer-6' ) ) { ?>
                            <div class="footer-widget-6">
                                <?php dynamic_sidebar( 'footer-6' ); ?>
                            </div>
                        <?php } ?>

					</div>
				</div>
		</div>
<?php }

//Notify Loop Item
add_action('woocommerce_after_shop_loop_item', 'add_notify_popup_button_in_catalog_page', 9999 );
function add_notify_popup_button_in_catalog_page(){
    global $product;
		if ($product) {
			$get_option = get_option('cwginstocksettings');
			$visibility_backorder = isset($get_option['show_on_backorders']) && '1' == $get_option['show_on_backorders'] ? true : false;
			$display_popup = isset($get_option['show_subscribe_button_catalog']) && '1' == $get_option['show_subscribe_button_catalog'] ? true : false;
			$id = $product->get_id();
			$product = wc_get_product($id);
			$variation = array();
			//$is_not_variation = $product && $product->is_type('variation') || $product->is_type('variable') ? false : true;

			$check_guest_visibility = isset($get_option['hide_form_guests']) && '' != $get_option['hide_form_guests'] && !is_user_logged_in() ? false : true;
			$check_member_visibility = isset($get_option['hide_form_members']) && '' != $get_option['hide_form_members'] && is_user_logged_in() ? false : true;
			$product_id = $product->get_id();
			$variation_class = '';
			$variation_id = 0;

      if ($display_popup && ( !$variation && !$product->is_in_stock() || ( ( !$variation && ( ( $product->managing_stock() && $product->backorders_allowed() && $product->is_on_backorder(1) ) || $product->is_on_backorder(1) ) && $visibility_backorder ) ) )) {
        /**
         * Trigger the 'cwginstock_custom_form' action hook to display a custom form for product availability
         *
         * @since 1.0.0
         */
        do_action('cwginstock_custom_form', $product, $variation);
      }
		}
}

add_action('cwg_instock_before_subscribe_form' , 'custom_template_send_user_notify');
function custom_template_send_user_notify(){
  //print_r($_POST);
  if(isset($_POST['product_id'])):
    ?>
    <h3 class="cwg-product-title"><?php echo get_the_title($_POST['product_id']) ?></h3>
    <?php
  endif;
}


add_filter( 'woocommerce_product_get_rating_html', 'be_show_rating_count_on_product_archive', 9999 , 3 );
function be_show_rating_count_on_product_archive( $html, $rating, $count ) {
	global $product;

  if(is_singular('product')) return $html;

  if(!empty($product)){
  	$rating_count = $product->get_rating_count();
  	if ( $rating_count > 0 ) {
  		$html .= "<div class='rating-count'>(" . $product->get_rating_count() . ")</div>";
  	}
  }else{
    if($count > 0){
      $html .= "<div class='rating-count'>(" . $count . ")</div>";
    }
  }
	return $html;
}

//Redirect
function be_redirect_specific_page() {
  if (is_404()) {
    $slug = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    if($slug == '/strain-info/pink-kush-3/'){
      wp_redirect(home_url('/strain-info/pink-kush-strain/'), 301);
      exit;
    }
  }
}
add_action('template_redirect', 'be_redirect_specific_page');

//Remove tidio Chat on home page
add_filter('option_tidio-async-load','custom_option_tidiochat_async');
function custom_option_tidiochat_async($value){
  return false;
}

//Hook customizer schema Product page
add_filter('wpseo_schema_product' , 'be_custom_wpseo_schema_product', 10 , 2);
function be_custom_wpseo_schema_product($data){

  //Non-Variant
  if(isset($data['hasVariant'])){
    $data_variants = [];
    foreach ($data['hasVariant'] as $variant) {
        $sku = explode('-',$variant['sku']);
        $variant_id = !empty($sku) ? $sku[1] : '';
        $variation_obj = new WC_Product_variation($variant_id);
        $stock_quantity = $variation_obj->get_stock_quantity();
        $status_Stock = 'InStock';
        if($stock_quantity < 1) $status_Stock = 'OutOfStock';
        if(isset($variant['offers'])){
          if(!isset($variant['offers']['availability'])) $variant['offers']['availability'] = 'http://schema.org/' . $status_Stock;
          if(!isset($variant['offers']['priceValidUntil'])) $variant['offers']['priceValidUntil'] = current_datetime()->format('Y-m-d');
        }
        $data_variants[] = $variant;
    }
    $data['hasVariant'] = $data_variants;
  }else{
    if(isset($data['offers'])){
      $data_variants = [];
      $product_id = $data['sku'];
      $product = wc_get_product($product_id);
      $stock_quantity = get_post_meta($product_id, '_stock', true);
      $status_Stock = 'InStock';
      if(!$product->is_in_stock()) $status_Stock = 'OutOfStock';
      foreach ($data['offers'] as $offer) {
          if(isset($offer['@type']) && $offer['@type'] == 'Offer'){
            if(!isset($offer['availability'])) $offer['availability'] = 'http://schema.org/' . $status_Stock;
            if(!isset($offer['priceValidUntil'])) $offer['priceValidUntil'] = current_datetime()->format('Y-m-d');
          }
          $data_variants[] = $offer;
      }
      $data['offers'] = $data_variants;
    }
  }

  return $data;
}


add_filter('woocommerce_structured_data_product' , 'custom_data_review_single_product' , 9 , 2);
function custom_data_review_single_product($markup, $product){
  if ( $product->get_rating_count() && wc_review_ratings_enabled() ) {
		$markup['aggregateRating'] = array(
			'@type'       => 'AggregateRating',
			'ratingValue' => $product->get_average_rating(),
			'reviewCount' => $product->get_review_count(),
		);

		// Markup 5 most recent rating/review.
		$comments = get_comments(
			array(
				//'number'      => '',
				'post_id'     => $product->get_id(),
				'status'      => 'approve',
				'post_status' => 'publish',
				'post_type'   => 'product',
				'parent'      => 0,
				'meta_query'  => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => 'rating',
						'type'    => 'NUMERIC',
						'compare' => '>',
						'value'   => 0,
					),
				),
			)
		);

		if ( $comments ) {
			$markup['review'] = array();
			foreach ( $comments as $comment ) {
				$markup['review'][] = array(
					'@type'         => 'Review',
					'reviewRating'  => array(
						'@type'       => 'Rating',
						'bestRating'  => '5',
						'ratingValue' => get_comment_meta( $comment->comment_ID, 'rating', true ),
						'worstRating' => '1',
					),
					'author'        => array(
						'@type' => 'Person',
						'name'  => get_comment_author( $comment ),
					),
					'reviewBody'    => get_comment_text( $comment ),
					'datePublished' => get_comment_date( 'c', $comment ),
				);
			}
		}
	}
  return $markup;
}


//add shippingDetails
add_filter( 'woocommerce_structured_data_product', 'custom_woocommerce_product_shipping_schema', 10, 2 );
function custom_woocommerce_product_shipping_schema( $markup, $product ) {

    // Get shipping rate dynamically
    $shipping_rate = custom_get_shipping_rate();

    // Shipping details
    $shipping_details = array(
          '@type' => 'OfferShippingDetails',
          'shippingDestination' => array(
              '@type' => 'DefinedRegion',
              'name' => 'Worldwide', // Adjust based on your shipping zones
              "addressCountry" => "CA"
          ),
          'deliveryTime' => array(
              '@type' => 'ShippingDeliveryTime',
              'handlingTime' => array(
                  '@type' => 'QuantitativeValue',
                  'minValue' => 1,
                  'maxValue' => 2,
                  'unitCode' => 'd' // days
              ),
              'transitTime' => array(
                  '@type' => 'QuantitativeValue',
                  'minValue' => 3,
                  'maxValue' => 7,
                  'unitCode' => 'd' // days
              )
          ),
          'shippingRate' => array(
              '@type' => 'MonetaryAmount',
              'value' => $shipping_rate,
              'currency' => get_woocommerce_currency() // Get the store's currency
          )
    );

    if ( isset( $markup['offers'] ) ) {
          $markup['offers'][0]['shippingDetails'] = $shipping_details;
    }

    return $markup;
}

// Function to get the dynamic shipping rate
function custom_get_shipping_rate() {
    $shipping_cost = '0.00';

    // Get available shipping methods for the current customer or session
    $packages = WC()->shipping->get_packages();
    foreach ( $packages as $package ) {
        foreach ( $package['rates'] as $rate ) {
            $shipping_cost = $rate->cost;
            break; // For simplicity, we use the first shipping method
        }
    }

    return $shipping_cost;
}


// add MerchantReturnPolicy
add_filter( 'woocommerce_structured_data_product', 'custom_woocommerce_product_return_policy_schema', 10, 2 );
function custom_woocommerce_product_return_policy_schema( $markup, $product ) {

    // Define the return policy details
    $return_policy = array(
        '@type' => 'MerchantReturnPolicy',
        'name' => '7-10 Business Days', // Name of your return policy
        'returnPolicyCategory' => 'http://schema.org/MerchantReturnFiniteReturnWindow', // Could be Refundable or Refundable
        'merchantReturnDays' => 10, // Number of days for return
        'returnMethod' => 'http://schema.org/ReturnInStore', // Return method: ReturnByMail, ReturnInStore, etc.
        'returnFees' => 'http://schema.org/FreeReturn', // If returns are free, use FreeReturn. Otherwise, specify return fees
        'applicableCountry' => 'CA'
    );

    if ( isset( $markup['offers'] ) ) {
        $markup['offers'][0]['hasMerchantReturnPolicy'] = $return_policy;
    }

    return $markup;
}
