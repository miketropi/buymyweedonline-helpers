<?php 
/**
 * Static
 */

function b_helpers_enqueue_scripts() {
  wp_enqueue_style('b_helpers_css', B_HELPERS_URI . '/dist/css/buymyweedonline-helpers.bundle.css', false, B_HELPERS_VERSION);
  wp_enqueue_script('b_helpers_js', B_HELPERS_URI . '/dist/buymyweedonline-helpers.bundle.js', ['jquery'], B_HELPERS_VERSION, true);


  $algolia_app = get_field('bh_algolia_app', 'option');
  wp_localize_script('b_helpers_js', 'B_HELPERS_DATA', [
    'ajax_url' => admin_url('admin-ajax.php'),
    'algolia_app' => [
      'APP_ID' => $algolia_app['application_id'],
      'API_KEY' => $algolia_app['search_only_api_key']
    ],
    'lang' => []
  ]);
}

add_action('wp_enqueue_scripts', 'b_helpers_enqueue_scripts');

function b_helpers_admin_enqueue_scripts() {
  wp_enqueue_style('b_helpers_admin_css', B_HELPERS_URI . '/dist/css/admin.buymyweedonline-helpers.bundle.css', false, B_HELPERS_VERSION);
  wp_enqueue_script('b_helpers_admin_js', B_HELPERS_URI . '/dist/admin.buymyweedonline-helpers.bundle.js', ['jquery'], B_HELPERS_VERSION, true);

  wp_localize_script('b_helpers_admin_js', 'B_HELPERS_DATA', [
    'ajax_url' => admin_url('admin-ajax.php'),
    'lang' => []
  ]);
}

add_action('admin_enqueue_scripts', 'b_helpers_admin_enqueue_scripts');