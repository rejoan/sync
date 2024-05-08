<?php

/*
  Plugin Name: syncData
  description: A simple custom plugin to get API data
  Version: 1.0.0
  Author: Rejoanul Alam
 */

if (!class_exists('syncData')) {

  class syncData {

    /**
     * Constructor
     */
    public function __construct() {
      $this->setup_actions();
    }

    /**
     * Setting up Hooks
     */
    public function setup_actions() {
      // Add Custom JS to admin panel
      add_action('wp_enqueue_scripts', array($this, 'plugin_css_jsscripts'));
      add_action('init', array($this, 'api_data'));
    }

    /**
     * Enqueue Scripts
     */
    public function plugin_css_jsscripts() {
      if (is_page(array('random-business-list'))) {
        wp_enqueue_style('style-common', plugins_url('/style.css', __FILE__));
      }
    }

    /**
     * Custom Post Type
     */
    public function api_data() {
      $labels = array(
          'archives' => __('Item Archives'),
          'attributes' => __('Item Attributes'),
          'parent_item_colon' => __('Parent Item:'),
          'all_items' => __('All Items'),
          'search_items' => __('Search'),
          'edit_item' => 'Edit Item',
          'update_item' => 'Update Item'
      );
      $args = array(
          'label' => __('API Data'),
          'description' => __('API data through Sync'),
          'labels' => $labels,
          'supports' => array('title', 'custom-fields'),
          'public' => true,
          'show_in_menu' => true,
          'menu_position' => 5,
          'menu_icon' => 'dashicons-format-aside',
          'show_in_admin_bar' => true,
          'show_in_nav_menus' => true,
          'can_export' => true,
          'has_archive' => true,
          'query_var' => true,
          'publicly_queryable' => true,
          'capabilities' => array(
              'create_posts' => false,
          ),
          'supports' => array('title','editor','custom-fields'),
          'map_meta_cap' => true,
      );
      register_post_type('apid', $args);
    }

    /**
     * Remove Published Tab From Game
     */
    public function remove__views($views) {
      unset($views['all']);
      unset($views['publish']);
      return $views;
    }
  }

  // instantiate the plugin class
  $syncData = new syncData();
}

add_filter('views_edit-apid', 'add_button_to_views');

function add_button_to_views($views) {
  $views['my-button'] = '<button id="sync_data" class="button">Sync Data</button><input type="hidden" id="admin_url" value="' . admin_url() . '"><input type="hidden" id="plugin_url" value="' . plugins_url() . '">';
  return $views;
}

function register_sync_menu() {
  add_menu_page('Sync API data', 'Sync Data', 'manage_options', 'sync_api', 'api_sync', 'dashicons-editor-table', 5);
}

//add_action('admin_menu', 'register_sync_menu');
add_filter('manage_apid_posts_columns', 'set_custom_apid_columns');

function set_custom_apid_columns($columns) {
  unset($columns['date']);
  $columns['bEmail'] = 'Email';
  $columns['bPhone'] = 'Phone';
  $columns['bAddress'] = 'Address';
  $columns['bCity'] = 'City';
  $columns['bActive'] = __('Active', 'your_text_domain');
  $columns['date'] = 'Date';
  return $columns;
}

/**
 * Display Values of Custom fields to Vaccine Card Image Custom Post Type
 */
add_action('manage_posts_custom_column', 'action_apid_custom_columns_content', 10, 2);

function action_apid_custom_columns_content($column_id, $post_id) {
  //run a switch statement for all of the custom columns created
  switch ($column_id) {
    case 'id':
      echo $post_id;
      break;
    case 'bEmail':
      echo get_post_meta($post_id, 'bEmail', true);
      break;
    case 'bUser':
      echo get_post_meta($post_id, 'bUser', true);
      break;
    case 'bAddress':
      echo get_post_meta($post_id, 'bAddress', true);
      break;
    case 'bPhone':
      echo get_post_meta($post_id, 'bPhone', true);
      break;
    case 'bCity':
      echo get_post_meta($post_id, 'bCity', true);
      break;
    case 'bActive':
      echo get_post_meta($post_id, 'bActive', true) == '1'? 'Yes':'No';
  }
}

/**
 * backend GRID
 * @global type $wpdb
 */
function api_sync() {
  $no = 20;
  $paged = isset($_GET['paged']) ? (int) trim($_GET['paged']) : 1;
  $offset = ( $paged - 1 ) * $no;

  global $wpdb;

  $query = 'SELECT meta_value FROM ' . $wpdb->prefix . 'usermeta WHERE meta_key = "apiDT" LIMIT ' . $offset . ', ' . $no;
  $user_query = $wpdb->get_results($query);
  $total_sql = 'SELECT meta_value FROM ' . $wpdb->prefix . 'usermeta WHERE meta_key = "apiDT"';
  $total_query = $wpdb->get_results($total_sql);

  $total_user = count($total_query);
  $total_pages = ceil($total_user / $no);
  $current_screen = get_current_screen();
  $current_page = admin_url('admin.php?page=' . $current_screen->parent_base);

  $html = '<div class="wrap">
  <input type="hidden" id="admin_url" value="' . admin_url() . '">
  <input type="hidden" id="plugin_url" value="' . plugins_url() . '">
  <h1 class="wp-heading-inline">Business Information</h1>
  <h2 class="screen-reader-text">Users list</h2>
  <p id="button-section"><button id="sync_data" class="button">Sync Data</button></p>
  <table class="wp-list-table widefat fixed striped table-view-list users">
  <thead>
  <tr>
  <th class="manage-column column-role">Business Name</th>
  <th class="manage-column column-role">Email</th>
  <th class="manage-column column-name">Phone</th>
  <th class="manage-column column-name">Category</th>
  <th class="manage-column column-name">User</th>
  <th class="manage-column column-role">Address</th>
  <th class="manage-column column-name">Active</th>
  <th class="manage-column column-role">Inserted at</th>
  </tr>
  </thead>
  <tbody id="the-list">';
  foreach ($user_query as $user) {
    $meta_value = json_decode($user->meta_value, true);
    $active = $meta_value['bActive'] == '1' ? 'Yes' : 'No';
    $html .= '<tr id="user-1">
          <td class="name column-name">' . $meta_value['bName'] . '</td>
          <td class="name column-name">' . $meta_value['bEmail'] . '</td>
          <td class="email column-email">' . $meta_value['bPhone'] . '</td>
		  <td class="name column-name">' . $meta_value['bCat'] . '</td>
		  <td class="name column-name">' . $meta_value['bUser'] . '</td>
		  <td class="name column-name">' . $meta_value['bAddress'] . '</td>
		  <td class="name column-name">' . $active . '</td>
          <td class="name column-name">' . date('Y-m-d H:i:s', $meta_value['inserted_at']) . '</td>
        </tr>';
  }
  $html .= '</tbody></table><div class="tablenav bottom">

	<div style="width:100%;" id="pagination_div" class="alignleft actions bulkactions">
		' . paginate_links(array(
              'base' => $current_page . '%_%',
              'format' => '',
              'total' => $total_pages,
              'prev_text' => '< Prev',
              'next_text' => 'Next >',
              'format' => '&paged=%#%',
              'current' => $paged,
              'type' => 'list'
          )) . '
	</div>
			
</div></div><style>#pagination_div ul li{float:left;}#pagination_div ul li a{padding:8px 12px;background:#ddd;text-decoration:none;box-shadow:none;}#pagination_div ul li span{padding:8px;}</style>';
  echo $html;
}

add_action('wp_ajax_sync_external', 'sync_external');
add_action('wp_ajax_nopriv_sync_external', 'sync_external');

function sync_external() {
  if (!isset($_POST['action']) && ($_POST['action'] != 'sync_external')) {
    exit('The form is not valid');
  }

  $ch = curl_init();

  curl_setopt($ch, CURLOPT_URL, "https://api.prospectbox.co/ytservices?site=&api_key=123456");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

  $headers = array();
  $headers[] = "Accept: application/json";
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $result = curl_exec($ch);

  if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
    die;
  }
  curl_close($ch);
  $businesses = json_decode($result, true);
  $sliced = $businesses;
  global $wpdb;
  $query = 'SELECT * FROM ' . $wpdb->prefix . 'posts WHERE post_type = "apid" ORDER BY ID DESC LIMIT 1';
  $row = $wpdb->get_row($query);
  if (!empty($row)) {
    $query1 = 'SELECT * FROM ' . $wpdb->prefix . 'postmeta WHERE post_id= ' . $row->ID . ' AND meta_key = "bID" ORDER BY meta_id DESC LIMIT 1';
    $row1 = $wpdb->get_row($query1);
    if (!empty($row1)) {
      $key = array_search($row1->meta_value, array_column($businesses, 'id'));
      $offset = $key + 1;
      $sliced = array_slice($businesses, $offset);
    }
  }


  if (empty($sliced)) {
    echo json_encode(array('code' => 'empty'));
    die;
  }

  foreach ($sliced as $business) {
    $rowData = array(
        'bID' => $business['id'],
        'bEmail' => $business['email'],
        'bPhone' => $business['phone'],
        'bCat' => $business['category'],
        'bUser' => $business['username'],
        'bUrl' => $business['url'],
        'bAddress' => $business['address'],
        'bCity' => $business['city'],
        'bState' => $business['state'],
        'bZip' => $business['zip'],
        'bCountry' => $business['country'],
        'bActive' => $business['active'],
        'bRating' => $business['rating'],
        'bReviews' => $business['reviews'],
        'bFeatured' => $business['featured']
    );
    $post_id = wp_insert_post(
            array(
                'post_type' => 'apid',
                'post_title' => $business['name'],
                'post_content' => $business['description'],
                'post_status' => 'publish'
            )
    );
    foreach ($rowData as $mkey => $metaV) {
      add_post_meta($post_id, $mkey, $metaV);
    }
  }
  echo json_encode(array('code' => 'done'));
  die;
}

add_action('admin_enqueue_scripts', 'add_backend_assets');

function add_backend_assets() {
  wp_enqueue_script('script-syncD', plugins_url('syncD.js', __FILE__));
}

add_shortcode('random-business-list', 'random_business_list');

/**
 * front end shortcode display function
 * @global type $wpdb
 * @param array $atts
 */
function random_business_list($atts) {
  if (!isset($atts['num']) || empty($atts['num']) || !ctype_digit($atts['num'])) {
    $atts['num'] = 20;
  }
  if (!isset($atts['view']) || empty($atts['view']) || !ctype_digit($atts['view'])) {
    $atts['view'] = '1';
  }

  if (is_dir(plugin_dir_path(__FILE__) . 'templates/template' . trim($atts['view']))
          === false) {
    echo '<h2 align="center">Template not exist</h2>';
    return;
  }


  $view = trim($atts['view']);
  $preHTML = file_get_contents(plugin_dir_path(__FILE__) . 'templates/template' . $view . '/preHTML.php');
  $postHTML = file_get_contents(plugin_dir_path(__FILE__) . 'templates/template' . $view . '/postHTML.php');

  $posts = get_posts([
            'post_type' => 'apid',
            'post_status' => 'publish',
            'numberposts' => trim($atts['num'])
          ]);
          

  $thebox = file_get_contents(plugin_dir_path(__FILE__) . 'templates/template' . trim($atts['view']) . '/main.php');

  $html = $preHTML;

  foreach ($posts as $post) {
    $rep = array("##bname##", "##bemail##", "##bphone##", "##bcat##", "##bdate##", "##buser##", "##baddress##");
    $repwith = array('<a target="_blank" href="'.get_post_permalink($post->ID).'">'.$post->post_title.'</a>', get_post_meta($post->ID,'bEmail',true), get_post_meta($post->ID,'bPhone',true), get_post_meta($post->ID,'bCat',true), $post->post_date, get_post_meta($post->ID,'bUser',true), get_post_meta($post->ID,'bAddress',true));
    $thisbox = str_replace($rep, $repwith, $thebox);

    $html .= $thisbox;
  }
  $html .= $postHTML;
  echo $html;
}

function empty_page_template($page_template) {
  if (is_page('random-business-list')) {
    $page_template = dirname(__FILE__) . '/em-page-template.php';
  }
  return $page_template;
}

//add_filter('page_template', 'empty_page_template');

add_action('init', 'do_output_buffer');

function do_output_buffer() {
  ob_start();
}
