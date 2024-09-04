<?php

/*
  Plugin Name: Sync Business
  description: A simple custom plugin to get API data
  Version: 1.1.0
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
      add_action('init', array($this, 'api_data'));
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
          'label' => __('Business Sync'),
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
          'supports' => array('title', 'editor', 'custom-fields'),
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

add_action('manage_posts_extra_tablenav', 'apid_button_to_views');

/**
 * plugin url hidden field for ajax
 * @param array $views
 * @return string
 */
function apid_button_to_views($which) {
  global $post_type;
  if ($which == 'top' && $post_type == 'apid') {
    echo '<p id="button-section"><button id="sync_data" class="button">Sync Data</button></p><input type="hidden" id="admin_url" value="' . admin_url() . '"><input type="hidden" id="plugin_url" value="' . plugins_url() . '">';
  }
}

/**
 * admin menu for GRID
 */
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
      echo get_post_meta($post_id, 'bActive', true) == '1' ? 'Yes' : 'No';
  }
}

/**
 * backend GRID (this is unused, for custom admin menu)
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

/**
 * get API data and create CPT
 * @global type $wpdb
 */
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
  global $post_type;
  if ($post_type == 'apid') {
    wp_enqueue_script('script-syncD', plugins_url('syncD.js', __FILE__));
    wp_enqueue_style('style-common', plugins_url('/style.css', __FILE__));
  }
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
    $repwith = array('<a target="_blank" href="' . get_post_permalink($post->ID) . '">' . $post->post_title . '</a>', get_post_meta($post->ID, 'bEmail', true), get_post_meta($post->ID, 'bPhone', true), get_post_meta($post->ID, 'bCat', true), $post->post_date, get_post_meta($post->ID, 'bUser', true), get_post_meta($post->ID, 'bAddress', true));
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

add_shortcode('al-businessinfo', 'apid_business_info');

/**
 * get business info shortcode
 * @global type $post
 * @return type
 */
function apid_business_info() {
  global $post;
  ob_start();
  echo '<h3 class="">Business Information</h3><p>Email: ' . get_post_meta($post->ID, 'bEmail', true) . '</p><p>Phone: ' . get_post_meta($post->ID, 'bPhone', true) . '</p><p>User: ' . get_post_meta($post->ID, 'bUser', true) . '</p>';
  $ret = ob_get_contents();
  ob_end_clean();
  return $ret;
}

add_shortcode('al-reviews-display', 'apid_reviews_info');

/**
 * get reviews from API for shortcode
 * @global type $post
 * @return type
 */
function apid_reviews_info() {
  global $post;
  wp_enqueue_style('slick', plugins_url('/slick/slick.css', __FILE__));
  wp_enqueue_style('slick-theme', plugins_url('/slick/slick-theme.css', __FILE__));
  wp_enqueue_script('jquery');
  wp_enqueue_script('slickjs', plugins_url('slick/slick.min.js', __FILE__));
  $businessID = trim(get_post_meta($post->ID, 'bID', true));

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, "https://api.prospectbox.co/ytservices/" . $businessID . "?api_key=123456");
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

  $reviews = json_decode($result, true);
  $noR = '';
  if (empty($reviews)) {
    $noR = '<p>No Reviews for this post</p>';
  }

  ob_start();
  $html = '<h3 style="color:#c15800;font-weight:700;margin-bottom-10px;">What People Say About ' . $post->post_title . '</h3>' . $noR . '<div class="slick-slider">';
  foreach ($reviews as $review) {
    $rating = '<div class="rating" style="width:100%;overflow:hidden;display:flex;justify-content:center;background:#fff;border-radius:10px 10px 0 0;padding-top:10px;">';
    for ($i = 0; $i < (int) $review['rating']; $i++) {
      $rating .= '<img width="20" src="' . plugins_url('sync/star.png') . '" alt="rating"/>';
    }
    $rating .= '</div>';
    $r = strlen($review['review']) > 50 ? substr(strip_tags($review['review']), 0, 100) . '...'
              : $review['review'];
    $html .= '<div class="slick-item" style="overflow: hidden;">' . $rating . '<div style="background:#fff;padding:5px 10px;border-radius:0 0 10px 10px;height:160px;position:relative;" class="rtext">' . $r . '<img width="20" style="position:absolute;filter:invert(1);left:45%;bottom:-15px;" src="' . plugins_url('sync/down-arrow.png') . '" alt="arrow"/></div><p style="text-align:center;margin-top:10px;">' . $review['author'] . '</p></div>';
  }
  $html .= '</div><style>div.rating img{display:block;float:left;}div.slick-slide:first-child{margin-left:0;}.slick-slide{margin:5px;}</style><script>jQuery(".slick-slider").slick({ autoplay:true,infinite: true,slidesToShow: 3,slidesToScroll: 1,arrows: false});</script>';
  echo $html;
  $ret = ob_get_contents();
  ob_end_clean();
  return $ret;
}

add_shortcode('ggle-reviews-display', 'apid_ggle_reviews_display');

function apid_ggle_reviews_display() {
  global $post;
  $address = get_post_meta($post->ID, 'bAddress', true);
  $city = get_post_meta($post->ID, 'bCity', true);
  $country = get_post_meta($post->ID, 'bCountry', true);
  if (empty($address)) {
    $map = '<p>No Address Found</p>';
  } else {
    $addressArr = explode(' ', $post->post_title);
    if (!empty($city)) {
      $cityArr = explode(' ', $city);
      foreach($cityArr as $ct){
        array_push($addressArr, $ct);
      }
    }
    if (!empty($country)) {
      array_push($addressArr, $country);
    }
    
    $place = implode('+', $addressArr);
    $place_final = str_replace('&','', $place);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://maps.google.com/maps/api/geocode/json?key=".GOOGLE_MAP_KEY."&address=".$place_final);
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
    $details = json_decode($result, true);
    
    $place_id = $details['results'][0]['place_id'];
    
    $ch1 = curl_init();
    curl_setopt($ch1, CURLOPT_URL, "https://maps.googleapis.com/maps/api/place/details/json?key=".GOOGLE_MAP_KEY."&placeid=".$place_id);
    curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch1, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch1, CURLOPT_SSL_VERIFYHOST, false);

    $headers = array();
    $headers[] = "Accept: application/json";
    curl_setopt($ch1, CURLOPT_HTTPHEADER, $headers);

    $result1 = curl_exec($ch1);

    if (curl_errno($ch1)) {
      echo 'Error:' . curl_error($ch1);
      die;
    }
    curl_close($ch1);
    $review_data = json_decode($result1, true);
    $html = '<h3>Google Reviews</h3>';
    if(isset($review_data['result']['reviews'])){
      foreach($review_data['result']['reviews'] as $review){
        $text = strlen($review['text']) > 200 ? substr($review['text'],0,200).'...' : $review['text'];
        $html .= '<div style="margin:25px 0;"><div style="overflow:hidden;"><img style="float:left;width:20%;" src="'.$review['profile_photo_url'].'" alt="'.$review['author_name'].'"/><p style="float:left;width:75%;margin-left:10px;">'.$text.'</p></div><div style="overflow:hidden;margin-top:10px;font-size:12px;text-color:#ddd;"><p style="float:left;width:20%;text-align:center;">'.$review['author_name'].'</p><p style="float:left;width:75%;margin-left:10px;">'.$review['relative_time_description'].'</p></div></div>';
      }
    }
    echo $html;
    $ret = ob_get_contents();
    ob_end_clean();
    return $ret;
  }
}

add_shortcode('al-map-display', 'apid_map_display');

function apid_map_display() {
  global $post;
  $address = get_post_meta($post->ID, 'bAddress', true);
  $city = get_post_meta($post->ID, 'bCity', true);
  $country = get_post_meta($post->ID, 'bCountry', true);
  if (empty($address)) {
    $map = '<p>No Address Found</p>';
  } else {
    $addressArr = explode(' ', $address);
    if (!empty($city)) {
      array_push($addressArr, $city);
    }
    if (!empty($country)) {
      array_push($addressArr, $country);
    }
    $place = implode('+', $addressArr);

    $map = '<iframe width="450" height="250" frameborder="0" style="border:0" referrerpolicy="no-referrer-when-downgrade" src="https://www.google.com/maps/embed/v1/place?key=' . API_KEY_GOOGLE . '&q=' . $place . '" allowfullscreen></iframe>';
  }
  ob_start();
  echo '<h3 class="">Map</h3><div id="map">' . $map . '</div>';
  $ret = ob_get_contents();
  ob_end_clean();
  return $ret;
}

add_action('wp_enqueue_scripts', 'my_theme_enqueue_styles', 11);

function my_theme_enqueue_styles() {
  wp_enqueue_style('child-style', get_stylesheet_uri());
}
