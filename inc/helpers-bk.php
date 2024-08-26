<?php
/**
 * Helpers
 */

function b_helpers_load_template($name, $require_once = false) {
  load_template( B_HELPERS_DIR . '/templates/' . $name . '.php', $require_once );
}

function b_helpers_get_woo_products_choices() {

  $choices = [];

  if( !is_admin() || !isset($_GET['page'])) return $choices;

  if(isset($_GET['page']) && $_GET['page'] != 'buymyweedonline-helpers') return $choices;

  $args = [
    'post_type'       => 'product',
    'posts_per_page'  => -1,
    'post_status'     => 'publish'
  ];

  $_posts = get_posts($args);

  if(!$_posts || count($_posts) <= 0) return $choices;
  $choices['Simple Products'] = [];

  foreach($_posts as $index => $p) {
    $_product = wc_get_product($p->ID);

    if($_product->is_type('simple')) {
      $choices['Simple Products'][$p->ID] = $p->post_title . ' (#'. $p->ID .')';
    }

    if($_product->is_type('variable')) {
      $children_ids = $_product->get_children();
      $group_name = $p->post_title . ' (#'. $p->ID .')';
      $choices[$group_name] = [];
      // $choices[$group_name][$p->ID] = $p->post_title . ' (#'. $p->ID .')';
      foreach($children_ids as $child_id) {
        $choices[$group_name][$child_id] = get_the_title($child_id) . ' (#'. $child_id .')';
      }
    }

  }

  return $choices;
}
/* fix sale badge */
add_filter('woocommerce_product_is_on_sale', 'bt_woocommerce_product_is_on_sale', 999, 2);
function bt_woocommerce_product_is_on_sale( $on_sale, $product ){
  if ( $product->is_type( 'variable' ) ) {
    global $plugin_public;
    remove_filter( 'woocommerce_product_get_price', array($plugin_public, 'wdpad_format_sale_price_only__premium_only'), 10);
    remove_filter( 'woocommerce_product_variation_get_price',array($plugin_public, 'wdpad_format_sale_price_only__premium_only'), 10 );
    remove_filter( 'woocommerce_variation_prices_price',array($plugin_public, 'wdpad_format_sale_price_only__premium_only'), 10);
    $prices  = $product->get_variation_prices();
    $on_sale = $prices['regular_price'] !== $prices['sale_price'] && $prices['sale_price'] === $prices['price'];

    add_filter( 'woocommerce_product_get_price', array($plugin_public, 'wdpad_format_sale_price_only__premium_only'), 10, 2);
    add_filter( 'woocommerce_product_variation_get_price', array($plugin_public, 'wdpad_format_sale_price_only__premium_only'), 10, 2 );
    add_filter( 'woocommerce_variation_prices_price', array($plugin_public, 'wdpad_format_sale_price_only__premium_only'), 10, 2 );
  }
  return $on_sale;
}
add_action('woocommerce_single_product_summary', 'bt_update_variation_info', 10);
function bt_update_variation_info(){
  remove_action('woocommerce_single_product_summary', 'bbloomer_echo_variation_info', 11);
  global $product;

    // Check if the product is a variable product
    if ($product->is_type('variable')) {
        // Output an empty container for variation info
        echo '<div class="var_info"><span class="price">Price:  </span> <span class="variation-info"></span></div>';

        // Enqueue JavaScript to handle variation change
        wc_enqueue_js("
            jQuery(document).on('found_variation', 'form.cart', function( event, variation ) {
                jQuery('.var_info .variation-info').html(variation.price_html);
            });
        ");
    }
}
// custom woo template
add_filter( 'woocommerce_locate_template', 'bt_intercept_wc_template', 10, 3 );
/**
 * Filter the cart template path to use cart.php in this plugin instead of the one in WooCommerce.
 *
 * @param string $template      Default template file path.
 * @param string $template_name Template file slug.
 * @param string $template_path Template file name.
 *
 * @return string The new Template file path.
 */
function bt_intercept_wc_template( $template, $template_name, $template_path ) {

	if ( 'variable.php' === basename( $template ) ) {
		$template = B_HELPERS_DIR . '/templates/woocommerce/add-to-cart/variable.php';
	}

	return $template;

}


// get available variants of products
function bt_get_variation_data_by_attribute_name( $available_variations, $attribute_name ) {
  $assigned = array();
  foreach ( $available_variations as $variation ) {
      $attrs = $variation[ 'attributes' ];
      $value = $attrs[ $attribute_name ];
      if ( ! isset( $assigned[ $attribute_name ][ sanitize_title($value) ] ) && ! empty( $value ) ) {
          $assigned[ $attribute_name ][ ($value) ] = array(
              'image_id'     => $variation[ 'variation_image_id' ],
              'variation_id' => $variation[ 'variation_id' ],
              'type'         => empty( $variation[ 'variation_image_id' ] ) ? 'button' : 'image',
          );
      }
  }

  return $assigned;
}
// template swatch image
add_filter('woo_variation_swatches_image_attribute_template', 'bt_woo_variation_swatches_image_attribute_template', 10, 4);
function bt_woo_variation_swatches_image_attribute_template($template, $data, $attribute_type, $variation_data){
  if ( is_product() ){
    $attribute_name = $data['attribute_name'];
    $option_name = $data['option_name'];
    $option_slug = $data['option_slug'];
    if(!$variation_data){
      $product = $data['args']['product'];
      $available_variations = $product->get_available_variations();
      $variation_data = bt_get_variation_data_by_attribute_name($available_variations, $attribute_name);
    }
    $variation_id = isset($variation_data[$attribute_name][sanitize_title($option_name)]) ? $variation_data[$attribute_name][sanitize_title($option_name)]['variation_id'] : $variation_data[$attribute_name][$option_name]['variation_id'];
    $template .= "<span class='option_name'>".$data['option_name']."</span>";
    if($variation_id){
      $variation_obj = wc_get_product($variation_id);
      if($variation_obj){
        $template .= "<span class='option_name'>".$data['option_name']."<label>".$variation_obj->get_price_html()."</label></span>";
      }
    }
  }
  return $template;
}
// template swatch button
add_filter('woo_variation_swatches_button_attribute_template', 'bt_woo_variation_swatches_button_attribute_template', 10, 4);
function bt_woo_variation_swatches_button_attribute_template($template, $data, $attribute_type, $variation_data){
  if ( is_product() ){
    $attribute_name = $data['attribute_name'];
    $option_name = $data['option_slug'];
    if(!$variation_data){
      $product = $data['args']['product'];
      $available_variations = $product->get_available_variations();
      $variation_data = bt_get_variation_data_by_attribute_name($available_variations, $attribute_name);
    }
    $variation_id = isset($variation_data[$attribute_name][sanitize_title($option_name)]) ? $variation_data[$attribute_name][sanitize_title($option_name)]['variation_id'] : $variation_data[$attribute_name][$option_name]['variation_id'];
    if($variation_id){
      $variation_obj = wc_get_product($variation_id);
      if($variation_obj){
        $template = '<span class="variable-item-span variable-item-span-button">%s<label>'.$variation_obj->get_price_html().'</label></span>';
      }
    }
  }
  return $template;
}
// alogolia best seller
add_filter('algolia_post_product_shared_attributes', 'bt_algolia_post_product_shared_attributes', 10, 2);
function bt_algolia_post_product_shared_attributes($shared_attributes, $post){
  $shared_attributes['total_sales'] = (int)get_post_meta( $post->ID, 'total_sales', true );
  return $shared_attributes;
}
// fix query
add_filter('generate_elements_custom_args', 'bt_generate_elements_custom_args');
function bt_generate_elements_custom_args($args){
  $args['suppress_filters'] = true;
   return $args;
}



function b_custom_template_age_gate() {
  b_helpers_load_template('age-gate');
}

//Optimize site
function remove_wp_enqueue_styles(){
    $styles = array();

    //Home page
    if(is_home() || is_front_page()){
        $styles = array(
          'cwginstock_bootstrap',
          'cwginstock_frontend_css',
          'bellows',
          'bellows-font-awesome',
          'bellows-vanilla',
          'delicious-recipe-styles',
          'delicious-recipes-pro-new',
          'delicious-recipes-pro-public',
          'delicious-recipes-pro',
          'jquery-rateyo',
          'light-gallery',
          'owl-carousel',
          'toastr',
          'fgf-frontend-css',
          'lightcase',
          'pwb-styles-frontend',
          'woobt-frontend',
          'woo-stickers-by-webline',
          'berocket_aapf_widget-style',
          'select2',
          'woocommerce-dynamic-pricing-and-discount',
          'wc-mnm-checkout-blocks',
          'wc-mnm-frontend',
          'wpcsb-frontend',
          'ywpar_frontend',
          'delicious-recipes-single',
          'affwp-forms',
          'metorik-css',
          'generate-woocommerce-mobile'
    		);
    }

    if(is_product_category() || is_shop()){
        $styles = array(
          'cwginstock_bootstrap',
          'cwginstock_frontend_css',
          'bellows',
          'bellows-font-awesome',
          'bellows-vanilla',
          'delicious-recipe-styles',
          'delicious-recipes-pro-new',
          'delicious-recipes-pro-public',
          'delicious-recipes-pro',
          'jquery-rateyo',
          'light-gallery',
          'owl-carousel',
          'toastr',
          'fgf-frontend-css',
          'lightcase',
          'pwb-styles-frontend',
          'woobt-frontend',
          'woo-stickers-by-webline',
          //'berocket_aapf_widget-style',
          'select2',
          'woocommerce-dynamic-pricing-and-discount',
          'wc-mnm-checkout-blocks',
          'wc-mnm-frontend',
          'wpcsb-frontend',
          'ywpar_frontend',
          'delicious-recipes-single',
          'affwp-forms',
          'metorik-css',
          'generate-woocommerce-mobile',
          'wp-block-library'
        );
    }

    if(is_singular('product')){
        $styles = array(
          'cwginstock_bootstrap',
          // 'cwginstock_frontend_css',
          'bellows',
          'bellows-font-awesome',
          'bellows-vanilla',
          'delicious-recipe-styles',
          'delicious-recipes-pro-new',
          'delicious-recipes-pro-public',
          'delicious-recipes-pro',
          'jquery-rateyo',
          'light-gallery',
          'owl-carousel',
          'toastr',
          'fgf-frontend-css',
          'lightcase',
          //'pwb-styles-frontend',
          //'woobt-frontend',
          'woo-stickers-by-webline',
          //'berocket_aapf_widget-style',
          'select2',
          'woocommerce-dynamic-pricing-and-discount',
          'wc-mnm-checkout-blocks',
          //'wc-mnm-frontend',
          //'wpcsb-frontend'
          'ywpar_frontend',
          'delicious-recipes-single',
          'select2',
          'affwp-forms',
          'metorik-css',
          'generate-woocommerce-mobile',
          'wp-block-library',
          'buttons',
          'editor-buttons',
          // 'generate-blog-images',
          // 'generate-offside',
          // 'generate-navigation-branding',
          // 'generate-woocommerce'
        );
    }

    foreach ($styles as $style) {
      wp_dequeue_style($style);
      wp_deregister_style($style);
    }
}

function remove_wp_enqueue_scripts(){

  $scripts = array();

  //Home page
  if(is_home() || is_front_page()){
    $scripts = array(
      'dr-pro-usr-dashboard',
      'delicious-recipes-single',
      'delicious-recipes-pro',
      'cwginstock_js',
      'math-min',
      'delicious-recipes-infiniteScroll',
      'jquery-rateyo',
      'v4-shims',
      'pintrest',
      'tidio-live-chat'
    );
  }

  //Home page
  if(is_singular('product') || is_product_category() || is_shop()){
    $scripts = array(
      'dr-pro-usr-dashboard',
      'delicious-recipes-single',
      'delicious-recipes-pro',
      'math-min',
      'delicious-recipes-infiniteScroll',
      'jquery-rateyo',
      'v4-shims',
      'pintrest'
    );
  }

  foreach ($scripts as $script) {
    wp_dequeue_script($script);
    wp_deregister_script($script);
  }

}

add_action('wp_head' , 'add_css_fix_bg_white' );
function add_css_fix_bg_white(){
  ?>
  <style media="screen">
    html.async-hide { opacity: 1 !important}
  </style>
  <?php
}
/* End Optimize Site */


/* Custom template price of woo */
add_filter('wcapf_get_template_location', 'custom_wcapf_get_template_location_price', 10 , 2);
function custom_wcapf_get_template_location_price($located, $template){
  if($template == 'range.php'){
    $located = B_HELPERS_DIR . '/templates/price-woo.php';
  }
  return $located;
}
/* Custom template price of woo */

/* add recapcha to register and account forms */
add_action('woocommerce_register_form' , 'add_recapcha_form');
function add_recapcha_form(){
  ob_start();
  ?>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <div class="g-recaptcha" data-sitekey="6Ld2i-8iAAAAAPek8DQi1qGgWbs6e30kVULh6tuQ"></div>
  <?php
  echo ob_get_clean();
}

//custom function to override default sort by category
function be_custom_default_catalog_orderby() {

  //choose categories where default sorting will be changed
  if (is_product_category( array( 'cheap-weed-canada' ) ) ) {
  	return 'price'; // sort by latest
  }else{
    return 'popularity'; // sort by popularity as the default
  } // end if statement

} //end function

add_filter( 'woocommerce_default_catalog_orderby', 'be_custom_default_catalog_orderby' ); //add the filter


function  be_filter_canonical_brands( $canonical ) {

  if ( is_tax( 'pwb-brand' , 'craft-cannabis' ) ) {
    $canonical = str_replace('brand' , 'product-category', $canonical);
  }
  return $canonical;

}

add_filter( 'wpseo_canonical', 'be_filter_canonical_brands' );

add_filter( 'wpseo_next_rel_link', 'be_change_wpseo_next_prev' );
add_filter( 'wpseo_prev_rel_link', 'be_change_wpseo_next_prev' );

function be_change_wpseo_next_prev( $link ) {

  if ( is_tax( 'pwb-brand' , 'craft-cannabis' ) ) {
    $link = str_replace('brand' , 'product-category', $link);
  }

  return $link;
}

/* Google Sheet API */

  /**
   * Google Sheet API
   */
  use Google\Client;
  use Google\Service\Sheets;
  use GuzzleHttp\Exception\RequestException;

  class BeGoogleSheetAPI
  {

    public $spreadsheetId = '1FCgRf1CO_20RJXuJ8m_sJ4NmMy08hGpkJ7THaZTS8bc';
    public $range         = 'BMWO!F2:01000';
    public $klaviyo_key   = 'pk_b0031f8c0a159566fb0b374fa578db2972';

    function __construct()
    {
      //Hooks
      add_filter('cron_schedules', array( $this , 'add_ten_minute_cron_schedule') );
      add_action('wp', array($this,'setup_five_minute_cron'));
      add_action('be_send_link_payment_interac_to_customer' , array($this, 'sendLinkPaymentInterac' ));
      add_action('be_update_status_order_after_payment_interac' , array($this,'updateStatusOrderAfterPaymentInterac'));
      add_action('init', array($this,'testapi'));
    }

    public function testapi(){
         if($_GET['apitest']){
           $email = 'thanhminh1602@gmail.com';
           echo $profile_id = $this->getProfileIdFromEmail($email);
           die;
         }
    }

    //Add new cron schedules
    public function add_ten_minute_cron_schedule($schedules) {
        $schedules['every_ten_minutes'] = array(
            'interval' => 600,
            'display' => __('Every Ten Minutes')
        );
        $schedules['every_fifteen_minutes'] = array(
            'interval' => 900,
            'display' => __('Every Fifteen Minutes')
        );
        return $schedules;
    }

    //Run cronjob
    public function setup_five_minute_cron() {
        //Send link Interac
        if (!wp_next_scheduled('be_send_link_payment_interac_to_customer')) {
            wp_schedule_event(time(), 'every_ten_minutes', 'be_send_link_payment_interac_to_customer');
        }

        //Update Status Order
        if (!wp_next_scheduled('be_update_status_order_after_payment_interac')) {
            wp_schedule_event(time(), 'every_fifteen_minutes', 'be_update_status_order_after_payment_interac');
        }

    }

    //Cronjob 1: Send Link Payment Interac to Customer
    public function sendLinkPaymentInterac(){

        $lists = $this->getListOrdersCustomers();
        $orderIDSent = get_option('listOrderSent');
        if(empty($orderIDSent)) $orderIDSent = array();

        foreach ($lists as $key => $row) {
            if(isset($row[7]) && isset($row[6])){

                //data
                $name         = $row[2];
                $link_payment = $row[7];
                $emailto      = $row[5];
                $orderID      = $row[3];
                $status       = strtolower($row[6]);

                //check send email
                if($status == 'pending' && !in_array($orderID, $orderIDSent)){
                  $htmlEmailTemplate = $this->emailSendPaymentInterac($name,$link_payment);
                  $headers = array('Content-Type: text/html; charset=UTF-8');
                  $is_sent = wp_mail($emailto,'Complete Your Payment Easily with BMWO!',$htmlEmailTemplate,$headers);
                  if($is_sent){
                    array_push( $orderIDSent, $orderID );
                    update_option('listOrderSent',$orderIDSent);
                  }else{
                    error_log('Error: Can\'t sent email to customer ' . $emailto . ' - OrderID: '. $orderID);
                  }
                }
            }
        }
    }

    //Cronjob 2: Update Status of Orders follow Google Sheet
    public function updateStatusOrderAfterPaymentInterac(){
        $lists = $this->getListOrdersCustomers();
        $orderIDUpdated = get_option('listOrderUpdated');
        if(empty($orderIDUpdated)) $orderIDUpdated = array();
        foreach ($lists as $key => $row) {
            if(isset($row[7]) && isset($row[6])){

                //data
                $name         = $row[2];
                $link_payment = $row[7];
                $emailto      = $row[5];
                $orderID      = $row[3];
                $status       = strtolower($row[6]);

                //check update status
                if($status == 'paid' && !in_array($orderID, $orderIDUpdated)){
                  $order = new WC_Order( $orderID );
                  if($order->update_status('completed')){
                    array_push( $orderIDUpdated, $orderID );
                    update_option('listOrderUpdated',$orderIDUpdated);
                  }else{
                    error_log('Error: Update status to order ID ' . $orderID . ' not success!');
                  }
                }
            }
        }
    }

    //Send email payment to customer
    public function emailSendPaymentInterac($name,$link_payment){
      ob_start();
      ?>
        <!DOCTYPE html>
        <html>
        <body>

        <div class="email-container" style="font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; background-color: #f9f9f9; border-radius: 8px;">
            <div class="header" style="text-align: center; color: #28a745; font-size: 24px; margin-bottom: 20px;">Complete Your Payment Easily with BMWO!</div>

            <div class="content" style="font-size: 16px; color: #333; line-height: 1.5;">
                <p style="margin-bottom: 15px;">Hello <?php echo $name; ?>,</p>

                <p style="margin-bottom: 15px;">Thank you for your recent order with BMWO. To complete your purchase, please use our fast and efficient payment method by clicking the link below:</p>

                <a href="<?php echo $link_payment ?>" class="cta-button" style="display: inline-block; padding: 10px 20px; margin: 20px 0; font-size: 16px; color: white; background-color: #28a745; text-align: center; text-decoration: none; border-radius: 5px;">Click Here to Pay Now</a>

                <p style="margin-bottom: 15px;">We highly recommend using this method for a quicker and more convenient experience. If you prefer to pay manually, you can still do so.</p>

                <p style="margin-bottom: 15px;">If you have any questions or need assistance, our customer support team is here to help. You can reach us via <a href="mailto:info@buymyweedonline.cc">info@buymyweedonline.cc</a>.</p>

                <p class="footer" style="margin-bottom: 15px;">Thank you for choosing BMWO.</p>

                <p class="footer" style="margin-bottom: 15px;margin-top: 20px; font-size: 14px; color: #555;">Best regards,<br>
                The Buy My Weed Online Customer Care Team <br>
                <a href="https://buymyweedonline.cc">Buymyweedonline.cc</a>
            </div>
        </div>

        </body>
        </html>
      <?php
      return ob_get_clean();
    }

    //Get data google sheet
    public function getListOrdersCustomers(){
      // Get the data
      $data = $this->getSheetData($this->spreadsheetId, $this->range);
      return $data;
    }

    //Config
    public function getClient() {
        $client = new Client();
        $client->setApplicationName('Google Sheets API PHP Quickstart');
        $client->setScopes([Google\Service\Sheets::SPREADSHEETS_READONLY]);
        $client->setAuthConfig( B_HELPERS_DIR . '/src/config/bmwo-428807-662c5acf8457.json');
        $client->setAccessType('offline');
        return $client;
    }

    // Function to get data from a Google Sheet
    public function getSheetData($sheetID, $r) {
        $client = $this->getClient();
        $service = new Sheets($client);

        // Retrieve data from the specified range
        $response = $service->spreadsheets_values->get($sheetID, $r);
        $values = $response->getValues();

        return $values;
    }

    function getProfileIdFromEmail($email){

        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', 'https://a.klaviyo.com/api/profiles/?filter=equals(email,"'.$email.'")', [
            'headers' => [
              'Authorization' => 'Klaviyo-API-Key '.$this->klaviyo_key,
              'accept' => 'application/json',
              'revision' => '2024-07-15',
            ],
        ]);
        $response_body = json_decode($response->getBody(), true);
        $profiles = $response_body['data'];
        $profile_id = 0;
        if(count($profiles) > 0){
            $profile_id = $profiles[0]['id'];
        }
        return $profile_id;

    }

    function createCustomerKlaviyo($profiles){

        $post_data = array(
            'data' => array(
                'type' => "profile",
                'attributes' => $profiles
            )
        );

        $client = new \GuzzleHttp\Client();

        $response = $client->request('POST', 'https://a.klaviyo.com/api/profiles/', [
            'body' => json_encode($post_data),
            'headers' => [
                'Authorization' => 'Klaviyo-API-Key '.$this->klaviyo_key,
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'revision' => '2024-06-15',
            ]
        ]);

        $response_body = json_decode($response->getBody(), true);
        //If the Klaviyo API returns a code anything other than OK, log it!
        $profile_id = 0;
        if(isset($response_body['errors'])) {
            if($response_body['errors'][0]['code'] == 'duplicate_profile'){
                $profile_id = $response_body['errors'][0]['meta']['duplicate_profile_id'];
            }else{
                // $this->log_error( __METHOD__ . '(): Could not create user' );
                // $this->log_error( __METHOD__ . '(): response => ' . print_r( $response, true ) );
            }
        }else{
            $profile_id = $response_body['data']['id'];
        }
        return $profile_id;
    }

    function updateProfileOnKlaviyo($profile_id, $properties){
        $post_data = array(
            'data' => array(
                'type' => "profile",
                'id' => $profile_id,
                'attributes' => array(
                    'properties' => $properties
                )
            )
        );

        $client = new \GuzzleHttp\Client();

        $response = $client->request('PATCH', 'https://a.klaviyo.com/api/profiles/'.$profile_id.'/', [
            'body' => json_encode($post_data),
            'headers' => [
                'Authorization' => 'Klaviyo-API-Key '.$this->klaviyo_key,
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'revision' => '2024-06-15',
            ]
        ]);
    }

    function updatePropertiesCustomer($customerData){
        $Customer = new Customer();
        $Customer->updateCustomer($customerData);
        // klaviyo
        $profiles = array(
            "email"  => $requestBody['email'],
            "first_name"  => $requestBody['first_name'],
            "last_name"  => $requestBody['last_name']
        );
        $profile_id = $Customer->getProfileIdFromEmail($requestBody['email']);
        if(!$profile_id){
            $profile_id = $Customer->createCustomerKlaviyo($profiles);
        }
        $properties = array(
            'prescription_expiry' => $prescription_expiry,
            'renewal_date' => $renewal_date
        );
        $Customer->updateProfileOnKlaviyo($profile_id, $properties);
    }

    public function getListSegments(){
        $client = new \GuzzleHttp\Client();
        try {
          $response = $client->request('GET', 'https://a.klaviyo.com/api/v2/list/YhRHvp/members', [
              'headers' => [
                  'api-key' =>  $this->klaviyo_key,
                  'content-type' => 'application/json'
              ]
          ]);
          if ($response->getStatusCode() === 200) {
            $segments = json_decode($response->getBody(), true);

            print_r($segments);
          } else {
              echo "Failed to retrieve segments. HTTP Code: " . $response->getStatusCode();
          }
      } catch (RequestException $e) {
        echo 'Error: ' . $e->getMessage();
        if ($e->hasResponse()) {
            echo ' Response: ' . $e->getResponse()->getBody();
        }
      }
      die;
    }

  }new BeGoogleSheetAPI();

/* Google Sheet API */
