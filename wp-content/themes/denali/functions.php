<?php
/**
 * Denali - Premium WP-Property Theme functions and definitions
 *
 * Premium theme for WP-Property real estate management plugin.
 *
 * For more information on hooks, actions, and filters, see http://TwinCitiesTech.com/Denali
 *
 * @package Denali - Premium WP-Property Theme
 * @since Denali 1.0
 */


// denali_theme::init loads all other hooks and filters
add_action('init', array('denali_theme', 'init'));

// Ran before init so cannot be called by init function
add_action('after_setup_theme',  array('denali_theme', 'denali_theme_setup'));

// Ajax action. Delete option 'denali_theme_clear_cache_notice'.
// Option is used only when W3 Total Cache is activated.
add_action('wp_ajax_denali_delete_option_clearcache', array('denali_theme', 'delete_option_clearcache'));
add_action('wp_ajax_denali_actions', array('denali_theme', 'ajax_actions'));

define('Denali_Version', '2.1');

/**
 * Main class for Denali theme options
 *
 * @since Denali 1.0
 */
class denali_theme {

    /**
     * Run on init hook, loads all other hooks and filters
     *
     * @todo Add all functions to class
     * @since Denali 1.0
     */
   static function init() {


    $denali_theme_settings = stripslashes_deep(get_option('denali_theme_settings'));

    add_action('template_redirect', array('denali_theme', 'template_redirect'));
    add_action('wp_ajax_denali_contact_form_submit', array('denali_theme', 'process_ajax_contact_form'));
    add_action('wp_ajax_nopriv_denali_contact_form_submit', array('denali_theme', 'process_ajax_contact_form'));

    add_action('wpp_publish_box_options', array('denali_theme', 'allow_comments'));

    add_action('admin_menu', array('denali_theme', 'admin_menu'));
    add_action('admin_init', array('denali_theme', 'admin_init'));

    add_action('admin_bar_menu', array('denali_theme', 'admin_bar_menu'), 70);

    add_action('admin_print_scripts-edit-comments.php', array('denali_theme', 'comment_page_css'), 0, 2);
    
    /* Add filters/actions on Inquiry/Comment rendering and adding */
    add_filter('pre_render_inquiry_form', array('denali_theme', 'pre_render_inquiry_form'));
    add_action('comment_post', array('denali_theme', 'pre_send_admin_inquiry_notification'), 0, 2);
    add_action('wp_insert_comment', array('denali_theme', 'wp_insert_comment'), 0, 2);
    
    add_action('manage_edit-comments_columns', array('denali_theme', 'add_inquiry_columns'));
    add_action('manage_comments_custom_column', array('denali_theme', 'manage_comments_custom_column'), 0, 2);

    add_filter('comment_form_defaults', array('denali_theme', 'comment_form_defaults'));

    add_action('wpp_js_on_property_overview_display', array('denali_theme', 'js_on_property_overview_display'), 0, 2);
    add_action('wpp_insert_property_comment', array('denali_theme', 'send_agent_inquiry_notification'), 0, 2);

    if(function_exists('curl_init')) {
      add_filter('site_transient_update_themes', array('denali_theme', 'check_denali_updates'));
    }

    // Set up menus for theme
    register_nav_menus(
      array(
        'header-menu' => __( 'Header Menu' ),
        'header-sub-menu' => __( 'Header Sub-Menu' ),
        'footer-menu' => __( 'Footer Menu' ),
        'bottom_of_page_menu' => __( 'Bottom of Page Menu' )
      )
    );

    // Load defaults on theme activation
    denali_theme::do_on_activation();

    // Add 'Clear W3 Total Cache' notice
    add_action('admin_notices', array('denali_theme', 'show_clear_W3_total_cache_notice'));


    if(!is_admin()) {

      $protocol = (is_ssl() ? 'https://' : 'http://');

      // Deregister WP jQuery (old version)
      wp_deregister_script( 'jquery' );
      wp_deregister_script( 'google-maps' );

      // Enqueue new scripts

      // jQuery is loaded in header because there are inilne jQuery calls
      wp_enqueue_script( 'jquery', $protocol . 'ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js', '', '');
      wp_enqueue_script( 'google-maps', $protocol . 'maps.google.com/maps/api/js?sensor=true&#038;ver=3.0.1', '', '', true);
      wp_enqueue_script( 'google-masonry',  get_bloginfo('template_url') . '/js/jquery.masonry.min.js', '', '', true);
      wp_enqueue_script( 'equalheights',  get_bloginfo('template_url') . '/js/jquery.equalheights.js', '', '', true);
      wp_enqueue_script( 'denali-global-js',  get_bloginfo('template_url') . '/js/denali-global-js.js', '', '', true);

       if ( is_singular() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
      }

      // Do this so denali styles supersede WPP styles

      // Load child theme style.css if exists
      if(file_exists( STYLESHEETPATH . '/style.css') ) {
        wp_enqueue_style('denali-style', get_bloginfo('stylesheet_directory') . '/style.css',array('wp-property-frontend'),Denali_Version,'screen');
      } else {
        wp_enqueue_style('denali-style', get_bloginfo('template_url') . '/style.css',array('wp-property-frontend'),Denali_Version,'screen');
      }

      if(file_exists( STYLESHEETPATH . '/print.css') ) {
        wp_enqueue_style('denali-print', get_bloginfo('stylesheet_directory') . '/print.css','',Denali_Version,'print');
      } elseif ( file_exists( TEMPLATEPATH . '/print.css') ) {
        wp_enqueue_style('denali-print', get_bloginfo('template_url') . '/print.css','',Denali_Version,'print');
      }



       // Load a custom color scheme if set
      if (!empty($denali_theme_settings['color_scheme'])) {
        if(file_exists( STYLESHEETPATH . "/{$denali_theme_settings['color_scheme']}")) {
          wp_enqueue_style('denali-colors', get_bloginfo('stylesheet_directory') . "/{$denali_theme_settings['color_scheme']}",array('denali-style'),'1.04','screen');
        } elseif(file_exists( TEMPLATEPATH . "/{$denali_theme_settings['color_scheme']}") ) {
          wp_enqueue_style('denali-colors', get_bloginfo('template_url') . "/{$denali_theme_settings['color_scheme']}",array('denali-style'),'1.04','screen');
        }
      }



      // Load scripts and styles for IE
      if(isset($is_IE) && $is_IE) {
        wp_enqueue_style('niceforms-default', get_bloginfo('template_url') . '/css/niceforms-default.css','',Denali_Version,'screen');
        wp_enqueue_script( 'html5', get_bloginfo('template_url') . '/js/html5.js');
        $wp_styles->add_data( 'niceforms-default', 'conditional', 'lt IE 7' );
      }

    }

        // Adds shortcodes to Text Widget
    if($denali_theme_settings['use_shortcodes_in_text_widget'] == 'true')
      add_filter('widget_text', 'do_shortcode');

      add_filter( 'wp_page_menu_args', array('denali_theme','add_home_to_menu_page_selection' ));

      add_image_size('agent_image', 120,120,true);
      add_filter('wpp_agent_widget_image_size', create_function('','return "agent_image"; '));
    }


   static function js_on_property_overview_display($template = false, $type = false) {

    if($template != 'grid') {
      return;
    }


   if(!$type): ?>
    jQuery(window).bind('load', function() {
      jQuery('.wpp_grid_view.wpp_property_view_result .property_div').equalHeights();
    });
   <?php else: ?>
    var wpp_grid_view_images = jQuery('.wpp_grid_view.wpp_property_view_result .property_div img');
    var wpp_grid_view_images_counter = 0;
    jQuery.each(wpp_grid_view_images, function(i, item) {
        jQuery(item).load(function() {
            wpp_grid_view_images_counter ++;
            if (wpp_grid_view_images_counter == wpp_grid_view_images.length) {
              jQuery('.wpp_grid_view.wpp_property_view_result .property_div').equalHeights();
            }
        })
    });
   <?php endif;
   }


  /**
    * Add "Theme Options" link to admin bar.
    *
    * @since Denali 2.1
    */
   static function admin_bar_menu($wp_admin_bar) {

    if ( ! current_user_can('switch_themes') && ! current_user_can( 'edit_theme_options' ) ) {
      return;
    }

    $wp_admin_bar->add_menu( array( 'parent' => 'appearance', 'id' => 'theme-options', 'title' => __('Theme Options'), 'href' => admin_url('themes.php?page=functions.php') ) );
    $wp_admin_bar->add_menu( array( 'parent' => 'appearance', 'id' => 'setup-assistant', 'title' => __('Setup Assistant'), 'href' => admin_url('themes.php?page=functions.php&action=first_time_setup') ) );

   }


  /**
    * Denali-specific ajax actions
    *
    * @since Denali 1.0
    */
   static function ajax_actions() {

    if(!wp_verify_nonce($_REQUEST['_wpnonce'], 'denali_actions'))
      return;

     switch($_REQUEST['denali_action']) {

      case 'delete_logo':

        $denali_theme_settings = stripslashes_deep(get_option('denali_theme_settings'));
        unset($denali_theme_settings['logo']);
        update_option('denali_theme_settings', $denali_theme_settings);
        echo json_encode(array('success' => 'true'));

      break;

     }


    die();
  }

     /**
     * Add checkbox to property editing page to allow comments
     *
      * @since Denali 1.0
     */
   static function add_home_to_menu_page_selection($args) {
    $args['show_home'] = true;
    return $args;
  }


/**
  * Add checkbox to property editing page to allow comments
  *
  * @since Denali 1.0
  */
  static function allow_comments() {
    global $post;
    echo "<li> " . UD_UI::checkbox("name=comment_status&value=open&label=".__('Allow comments', 'wpp') ."&value=open", ($post->comment_status == 'open' ? true : false)) . "</li>";
  }

/**
  * Handle updating Denali
  *
  * @since Denali 1.9.1
  */
 static function handle_upgrade(){

  $installed_version = get_option('denali_version');

  if(!$installed_version || version_compare(Denali_Version, $installed_version, '<')) {

    // Upgrade needed

    // If no version (pre 1.9.1)
    if(!$installed_version) {



    }

  }

 }


 /**
  * Load default settings on theme activation
  *
  * @since Denali 1.0
  */
 static function do_on_activation(){
  global $pagenow, $wp_properties, $denali_theme_settings;
  $denali_theme_settings = stripslashes_deep(get_option('denali_theme_settings'));

  if ( is_admin() && 'themes.php' == $pagenow && isset( $_GET['activated'] ) ) {

    // Update version
    update_option('denali_version', Denali_Version);

    denali_theme::handle_upgrade();

    // Load default settings
    if(empty($denali_theme_settings)) {
      $first_time_setup = true;
      denali_theme::load_defaults();
    }

    $wp_properties['image_sizes']['overview_thumbnail']['width'] = 200;
    $wp_properties['image_sizes']['overview_thumbnail']['height'] = 140;

    $wp_properties['configuration][property_overview][thumbnail_size'] = 'overview_thumbnail';
    $wp_properties['configuration][feature_settings][slideshow][glob][image_size'] = 'home_page_slideshow';

    $wp_properties['image_sizes']['property_slideshow']['width'] = 921;
    $wp_properties['image_sizes']['property_slideshow']['height'] = 250;

    $wp_properties['image_sizes']['home_page_slideshow']['width'] = 650;
    $wp_properties['image_sizes']['home_page_slideshow']['height'] = 250;

    $wp_properties['image_sizes']['home_page_thumb']['width'] = 175;
    $wp_properties['image_sizes']['home_page_thumb']['height'] = 150;

    $wp_properties['image_sizes']['sidebar_gallery']['width'] = 270;
    $wp_properties['image_sizes']['sidebar_gallery']['height'] = 180;

    $wp_properties['image_sizes']['square_thumb']['width'] = 90;
    $wp_properties['image_sizes']['square_thumb']['height'] = 90;

    $wp_properties['configuration']['feature_settings']['slideshow']['glob']['dimensions'] = '680x250';

    $wp_properties['configuration']['feature_settings']['slideshow']['glob']['thumb_width'] = 300;
    $wp_properties['configuration']['feature_settings']['slideshow']['property']['dimensions'] = '921x180';
    $wp_properties['configuration']['feature_settings']['slideshow']['thumb_width'] = 350;
    update_option('wpp_settings', $wp_properties);
    $wp_properties_db = stripslashes_deep(get_option('wpp_settings'));
    $wp_properties = array_merge($wp_properties, $wp_properties_db);


    if(current_theme_supports('custom_background')) {
      if(get_theme_mod('background_image_thumb') == 'BACKGROUND_IMAGE'
        || get_theme_mod('background_image_thumb') == '') {
        // Set settings for default background
        set_theme_mod('background_repeat', false);
        set_theme_mod('background_position_x', 'center');
        set_theme_mod('background_position_y', 'top');
      }
    }

    // Get latest premium features
    if(class_exists('WPP_F')) {
      WPP_F::feature_check(true);
    }

    if($first_time_setup) {
      //** Run first time setup if not settings found for Denali */
      wp_redirect(admin_url('themes.php?page=functions.php&action=first_time_setup'));
    }

  }
  return $wp_properties;
  }


  /**
   * Loads defaults on activation if no settings exist.
   *
   * @todo May make sense to remove function and have setup assistant handle it all.
   *
   * @since Denali 1.71
   *
   */
  static function load_defaults() {
    global $wpdb, $denali_theme_settings, $wp_properties;

    // Default pages
    $denali_theme_settings['options_explore'] = 'pages';
    $denali_theme_settings['use_shortcodes_in_text_widget'] = 'true';

    // Default data
    $denali_theme_settings['email'] = get_option('admin_email');
    $denali_theme_settings['email_from'] = get_option('admin_email');
    $denali_theme_settings['phone'] = $wp_properties['configuration']['phone_number'];


    /*
    Removed. Now handled by easy setup.
    $sidebar_widgets = get_option('sidebars_widgets');

    if(is_array($sidebar_widgets['global_property_search']) && !in_array('searchpropertieswidget-2', $sidebar_widgets['global_property_search']))
      $sidebar_widgets['global_property_search'][] = 'searchpropertieswidget-2';

    if(is_array($sidebar_widgets['home_sidebar']) && !in_array('pages-2', $sidebar_widgets['home_sidebar']))
      $sidebar_widgets['home_sidebar'][] = 'pages-2';

    if(is_array($sidebar_widgets['right_sidebar']) && !in_array('pages-2', $sidebar_widgets['right_sidebar']))
      $sidebar_widgets['right_sidebar'][] = 'pages-2';

    update_option('sidebars_widgets', $sidebar_widgets);
    */

    // Save changes
    update_option('denali_theme_settings', $denali_theme_settings);
  }


  /**
    * Handles back-end theme configurations
    *
    * @since Denali 1.0
    *
    */
  static function admin_menu() {

    $settings_page = add_theme_page("Theme Options", "Theme Options", 'edit_theme_options', basename(__FILE__), array('denali_theme','options_page'));
    add_action('admin_print_scripts-' . $settings_page, create_function('', "wp_enqueue_script('jquery-ui-tabs');wp_enqueue_script('jquery-cookie');"));

  }



  /**
    * Primarystatic function for handling front-end actions
    *
    * @since Denali 1.0
    */
 static function template_redirect() {
  global $wp_styles, $is_IE, $denali_theme_settings, $wp_query;
  $denali_theme_settings = stripslashes_deep(get_option('denali_theme_settings'));


    // Show message if WP-Property not active or site is in maintanance mode
    if($denali_theme_settings['maintanance_mode'] == 'true' || !class_exists('WPP_Core') ) {
      if(file_exists(STYLESHEETPATH . '/maintanance.php')) {
        include STYLESHEETPATH . '/maintanance.php';
        die();
      } else {
        include 'maintanance.php';
        die();
      }
    }

    if(is_posts_page()) {
      if(file_exists(STYLESHEETPATH . '/posts_page.php')) {
        load_template(STYLESHEETPATH . '/posts_page.php');
        die();
      } else {
        load_template(TEMPLATEPATH . '/posts_page.php');
        die();
      }
    }

    if(is_front_page()) {
      if(file_exists(STYLESHEETPATH . '/index.php')) {
        load_template(STYLESHEETPATH . '/index.php');
        die();
      } else {
        load_template(TEMPLATEPATH . '/index.php');
        die();
      }
    }


     }


    /**
     * Setup a default homage page
     *
     * Called by setup assistant.
     *
     * @since Denali 1.1
     */
    static function setup_default_home_page() {
      global $wpdb, $user_ID;

        //** Check if this page actually exists */
      $post_id = $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE post_name = 'home'");

      if(!$post_id) {
        $post_id = '';
      } else {
        return $post_id;
      }

      $home_page_content[] = "<h2>Welcome to " . get_bloginfo('blogname') . "!</h2>";
      $home_page_content[] = "[property_search]";
      $home_page_content[] = "[property_overview per_page=5 pagination=off template=grid]";

      $property_page = array(
        'post_title' => __('Home', 'wpp'),
        'post_content' => implode("\n", $home_page_content),
        'post_name' => 'home',
        'post_type' => 'page',
        'post_status' => 'publish',
        'post_id' => $post_id,
        'post_author' =>  $user_ID
      );

      $post_id = wp_insert_post($property_page);

      return $post_id;

    }

    /**
     * Checks colors folder for available color scheemes
     *
     * Rerturns thumb URL if it exists.
     *
     * @since Denali 1.1
     */
   static function get_color_schemes() {

      $default_headers = array(
      'name' => __('Color Palette','denali_theme'),
      'description' => __('Description','denali_theme'),
      'author' => __('Author','denali_theme'),
      'version' => __('Version','denali_theme'),
      'tags' => __('Tags','denali_theme')
      );


    // Scan /colors/ folder for CSS files
    if ($handle = opendir(TEMPLATEPATH . '/')) {

      while (false !== ($file = readdir($handle))) {

        if ($file != "." && $file != ".." && strpos($file, 'skin-') === 0) {

          // Skip non-css files

          if(substr($file, strrpos($file, '.') + 1) != 'css') {
            continue;
          }

          $file_data = @get_file_data(TEMPLATEPATH . '/' . $file, $default_headers, 'denali_color_css' );

          if(!empty($file_data)) {

            $files[$file] = $file_data;

            //** Load scheme thumbnail if it exists */
             if(file_exists(TEMPLATEPATH . '/' . str_replace('.css', '.jpg', $file))) {
              $files[$file]['thumb'] = get_bloginfo('template_url')  . '/' . str_replace('.css', '.jpg', $file);
            }
          }

        }
      }
    }

    //** Look for color schemes in styleshet directory (if in child theme) */
    if ($handle = opendir(STYLESHEETPATH . '/')) {

      while (false !== ($file = readdir($handle))) {

        if ($file != "." && $file != ".." && strpos($file, 'skin-') === 0) {

          // Skip non-css files

          if(substr($file, strrpos($file, '.') + 1) != 'css')
            continue;

           $file_data = @get_file_data(STYLESHEETPATH . '/' . $file, $default_headers, 'denali_color_css' );

          if(!empty($file_data)){

            $files[$file] = $file_data;

            //** Load scheme thumbnail if it exists */
            if(file_exists(TEMPLATEPATH . '/' . str_replace('.css', '.jpg', $file))) {
              $files[$file]['thumb'] = get_bloginfo('template_url')  . '/' . str_replace('.css', '.jpg', $file);
            }
          }

        }
      }
    }

    $files = apply_filters('denali_extra_css_files', $files);

    if(!is_array($files))
      return false;

    return $files;
  }



    /**
     * Handles contact form ajax
     *
     * @since Denali 1.0
     */
   static function process_ajax_contact_form() {

        if(wp_verify_nonce($_REQUEST['nonce'], 'denali_contact_form')){

            $data['name'] = $_REQUEST['name'];
            $data['email'] = $_REQUEST['email'];
            $data['phone'] = $_REQUEST['phone'];
            $data['subject'] = $_REQUEST['subject'];
            $data['message'] = $_REQUEST['message'];

            $result = denali_theme::submit_contact_form($data);

            echo json_encode($result);

        }

        die();
    }

    /**
     * Converts a text address into coordinates
     *
     * Run on Denali options update to validate blog owner's address for map on front-end.
     *
     * @since Denali 1.0
     */
   static function get_geodata($address = false, $locale = "en"){

        if(!$address) {
          return false;
        }

        $address = urlencode($address);

        $url = str_replace(" ", "+" ,"http://maps.google.com/maps/api/geocode/json?address={$address}&sensor=true");

        $obj = (json_decode(wp_remote_fopen($url)));

        if($obj->status != "OK")
            return false;

        $results = $obj->results;
        $results_object = $results[0];
        $geometry = $results_object->geometry;

        $return->formatted_address = $results_object->formatted_address;
        $return->latitude = $geometry->location->lat;
        $return->longitude = $geometry->location->lng;

        return $return;
    }


    /**
     * Ran by ajaxstatic function to vaildate and send the contact message.
     *
     * @since Denali 1.0
     */
   static function submit_contact_form($data){

        $denali_theme_settings = stripslashes_deep(get_option('denali_theme_settings'));

        foreach($data as $entry)
            $data[$entry] = trim($entry);

        $data = stripslashes_deep($data);

        if($data['name'] === '') {
            $errors['name'] =  __('You forgot to enter your  name.', 'wpp');
        }


        if($data['message'] === '') {
            $errors['message'] =  __('You forgot to enter a message.', 'wpp');
        }

        //Check to make sure that a valid email address is submitted
        if($data['email'] === '')  {
            $errors['email'] = __('You forgot to enter your e-mail.', 'wpp');
         } elseif (!eregi("^[A-Z0-9._%-]+@[A-Z0-9._%-]+\.[A-Z]{2,4}$", $data['email'])) {
            $errors['email'] = 'Please verify your email.';
         }

        if(!empty($errors)) {

            return array('success' => 'false', 'errors' => $errors);

        }

        $emailFrom = $denali_theme_settings['email_from'];

         $body = nl2br( "
            Name: {$data[name]}
            E-mail: {$data[email]}
            $phone
            Subject: {$data[subject]}
            - - - - - - - - - - - - - - - - -
            Message: {$data[message]}");
        $headers = 'Content-type: text/html; charset=utf-8' . "\r\n";
        $headers .= 'From: '. $emailFrom. "\r\n" . 'Reply-To: ' . $emailFrom;

        if(wp_mail($denali_theme_settings[email], $data[subject], $body, $headers))
            return array('success' => 'true');

        return array('success' => 'false', 'errors' => array('mail' => __('Error with sending message. Please contact site administrator.', 'wpp')));


    }

    /**
     * Draw the custom site background
     *
     * Run on Denali options update to validate blog owner's address for map on front-end.
     *
     * @since Denali 1.0
     */

   static function custom_background() {
    $background = get_background_image();
    $color = get_background_color();

   if ( ! $background && ! $color )
      return;

    $style = $color ? "background-color: #$color;" : '';

    $image = " background-image: url('$background');";

    $repeat = get_theme_mod( 'background_repeat', 'no-repeat' );

    if ( ! in_array( $repeat, array( 'no-repeat', 'repeat-x', 'repeat-y', 'repeat' ) ) )
      $repeat = 'no-repeat';

    $repeat = " background-repeat: $repeat;";

    $position = get_theme_mod( 'background_position_x', 'left' );
    if ( ! in_array( $position, array( 'center', 'right', 'left' ) ) )
      $position = 'center';

    $position = " background-position: top $position;";

    $attachment = get_theme_mod( 'background_attachment', 'scroll' );
    if ( ! in_array( $attachment, array( 'fixed', 'scroll' ) ) )
    $attachment = 'scroll';
    $attachment = " background-attachment: $attachment;";

    $style .= $image . $repeat . $position . $attachment;

    ?>
    <style type="text/css">
    div.wrapper { <?php echo trim( $style ); ?> }
    </style>
    <?php

    }

  /**
   * Display area for background image in back-end
   *
   *
   * @since Denali 1.2
   */
  function admin_image_div_callback() { ?>

    <h3><?php _e('Background Image'); ?></h3>
    <table class="form-table">
    <tbody>
    <tr valign="top">
    <th scope="row"><?php _e('Preview'); ?></th>
    <td>
    <?php
    $background_styles = '';
    if ( $bgcolor = get_background_color() )
      $background_styles .= 'background-color: #' . $bgcolor . ';';

    if ( get_background_image() ) {
      // background-image URL must be single quote, see below
      $background_styles .= ' background-image: url(\'' .  get_background_image() . '\');'
        . ' background-repeat: ' . get_theme_mod('background_repeat', 'no-repeat') . ';'
        . ' background-position: top ' . get_theme_mod('background_position_x', 'left');
    }
    ?>
    <div id="custom-background-image" style=" min-height: 200px;<?php echo $background_styles; ?>"><?php // must be double quote, see above ?>

    </div>
    <?php

  }

  /**
   * Setups up core theme functions
   *
   * Adds image header section and default headers
   *
   * @since Denali 1.2
   */
  static function denali_theme_setup() {
    global $wp_properties, $pagenow, $_wp_theme_features;

    add_action( 'widgets_init', array('denali_theme', 'widgets_init'));

    add_editor_style();
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'custom-background' );

    // These can be disabled via UI, or child theme (if so, there will be no option in UI to toggle)
    add_theme_support( 'home_page_attention_grabber_area' );
    add_theme_support( 'post_page_attention_grabber_area' );
    add_theme_support( 'header-property-search' );
    add_theme_support( 'header-property-contact' );
    add_theme_support( 'header-login' );
    add_theme_support( 'header-card' );
    add_theme_support( 'footer_explore_block' );
    add_theme_support( 'inner_page_slideshow_area' );

    // Hooks used to add / remove theme_support
    do_action('denali_post_theme_support');

    // Add default posts and comments RSS feed links to head
    add_theme_support( 'automatic-feed-links' );

    define('BACKGROUND_IMAGE', get_bloginfo('template_url') . '/img/back.png');

    $header_width = $wp_properties['image_sizes']['header_image']['width'];
    $header_height = $wp_properties['image_sizes']['header_image']['height'];

    if(empty($header_width))
        $header_width = 986;

    if(empty($header_height))
        $header_height = 220;


    // No CSS, just IMG call. The %s is a placeholder for the theme template directory URI.
    define( 'NO_HEADER_TEXT', true );
    define( 'HEADER_IMAGE', '%s/img/cont_img.jpg' );
    define( 'HEADER_IMAGE_WIDTH', apply_filters( 'denali_header_image_width',  $header_width) );
    define( 'HEADER_IMAGE_HEIGHT', apply_filters( 'denali_header_image_height',    $header_height ) );
    add_custom_image_header( '', create_function('',''));

    // Default custom headers packaged with the theme. %s is a placeholder for the theme template directory URI.
    register_default_headers( array (
      'oceanfront' => array (
        'url' => '%s/img/headers/oceanfront.jpg',
        'thumbnail_url' => '%s/img/headers/oceanfront_thumb.jpg',
        'description' => __( 'Oceanfront', 'denali' )),
      'minneapolis' => array (
        'url' => '%s/img/headers/minneapolis.jpg',
        'thumbnail_url' => '%s/img/headers/minneapolis_thumb.jpg',
        'description' => __( 'Minneapolis', 'denali' )),
      'suburbs' => array (
        'url' => '%s/img/headers/suburbs.jpg',
        'thumbnail_url' => '%s/img/headers/suburbs_thumb.jpg',
        'description' => __( 'Cozy Suburbs', 'denali' )),
      'wilmington' => array (
        'url' => '%s/img/headers/wilmington.jpg',
        'thumbnail_url' => '%s/img/headers/wilmington_thumb.jpg',
        'description' => __( 'Wilmington', 'denali' ))
    ));


    if(current_theme_supports('custom-background')) {
     add_custom_background(array('denali_theme','custom_background'),'',array('denali_theme','admin_image_div_callback'));
    }

    do_action('denali_theme_setup');

   }


  /**
   * Adds a widget to a sidebar.
   *
   * Adds a widget to a sidebar, making sure that sidebar doesn't already have this widget.
   *
   * Example usage:
   * denali_theme::add_widget_to_sidebar('global_property_search', 'text', array('title' => 'Automatically Added Widget', 'text' => 'This widget was added automatically'));
   *
   * @todo Some might exist that adds widgets twice.
   * @todo Consider moving functionality to UD_F
   *
   * @since Denali 1.0
   */
   static function add_widget_to_sidebar($sidebar_id = false, $widget_id = false, $settings = array(), $args = '') {
    global $wp_registered_widget_updates, $wp_registered_widgets;

    $defaults = array(
      'do_not_duplicate' => 'true'
    );

    extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

    require_once(ABSPATH . 'wp-admin/includes/widgets.php');

    do_action('load-widgets.php');
    do_action('widgets.php');
    do_action('sidebar_admin_setup');

    //** Need some validation here */
    if(!$sidebar_id) {
      return false;
    }

     if(!$widget_id) {
      return false;
    }

    if(empty($settings)) {
      return false;
    }

    //** Load sidebars */
    $sidebars = wp_get_sidebars_widgets();

    //** Get widget ID */
    $widget_number  = next_widget_id_number($widget_id);

    if(is_array($sidebars[$sidebar_id])) {
      foreach($sidebars[$sidebar_id] as $this_sidebar_id => $sidebar_widgets) {

        //** Check if this sidebar already has this widget */
        if(strpos($sidebar_widgets, $widget_id) === false) {
          continue;
        }

        $widget_exists = true;

      }
    }

    if($do_not_duplicate == 'true' && $widget_exists) {
      return true;
    }

    foreach ( (array) $wp_registered_widget_updates as $name => $control ) {

      if ( $name == $widget_id ) {
        if ( !is_callable( $control['callback'] ) )
          continue;

        ob_start();
          call_user_func_array( $control['callback'], $control['params'] );
        ob_end_clean();
        break;
      }
    }

    //** May not be necessary */
    if ( $form = $wp_registered_widget_controls[$widget_id] ) {
      call_user_func_array( $form['callback'], $form['params'] );
    }

    //** Add new widget to sidebar array */
    $sidebars[$sidebar_id][] = $widget_id . '-' . $widget_number;

    //** Add widget to widget area */
    wp_set_sidebars_widgets($sidebars);

    //** Get widget configuration */
    $widget_options = get_option('widget_' . $widget_id);

    //** Check if current widget has any settings (it shouldn't) */
    if($widget_options[$widget_number]) {
    }

    //** Update widget with settings */
    $widget_options[$widget_number] = $settings;

    //** Commit new widget data to database */
    update_option('widget_' . $widget_id, $widget_options);


    return true;

   }

   /**
   * Remove all instanced of a widget from a sidebar
   *
   * Adds a widget to a sidebar, making sure that sidebar doesn't already have this widget.
   *
   * @since Denali 1.0
   */
   function remove_widget_from_sidebar($sidebar_id, $widget_id) {
     global $wp_registered_widget_updates;


    //** Load sidebars */
    $sidebars = wp_get_sidebars_widgets();


    //** Get widget ID */

    if(is_array($sidebars[$sidebar_id])) {
      foreach($sidebars[$sidebar_id] as $this_sidebar_id => $sidebar_widgets) {

        //** Check if this sidebar already has this widget */

        if(strpos($sidebar_widgets, $widget_id) === 0 || $widget_id == 'all') {

          //** Remove widget instance if it exists */
          unset($sidebars[$sidebar_id][$this_sidebar_id]);

        }

      }
    }


    //** Save new siebars */
    wp_set_sidebars_widgets($sidebars);
   }

  /**
   * Displays first-time setup splash screen
   *
   * WPP widgets:
   * - searchpropertieswidget
   * - childpropertieswidget
   * - latestpropertieswidget
   * - gallerypropertieswidget
   * - featuredpropertieswidget
   * - agentwidget
   *
   * @todo Fix permalink rewrites.
   *
   * @since Denali 1.0
   */
  static function admin_init() {
    global $wp_properties, $wp_registered_widget_updates, $wpdb;


    //** Check if child thme exists and updates denali_theme_settings accordingly */
    denali_theme::denali_child_theme_exists();


    if(!empty($_REQUEST['_wpnonce'])) {
    
    //** Save main Denali Settings */
    if(wp_verify_nonce($_REQUEST['_wpnonce'], 'denali_settings')) {

      foreach($_REQUEST['denali_theme_settings'] as $key => $value) {
          $denali_theme_settings[$key] = $value;
      }

      // Set coordinates
      $coordinates = denali_theme::get_geodata($denali_theme_settings[address]);
      $denali_theme_settings['longitude'] = $coordinates->longitude;
      $denali_theme_settings['latitude'] = $coordinates->latitude;

      //Set logo
      if( ! empty ($_FILES['logo']['name'])){
          $overrides = array ( 'test_form' => false );
          $file = wp_handle_upload($_FILES['logo'], $overrides);
          $denali_theme_settings['logo'] = $file['url'];
      } else {
          $curr_option =    get_option('denali_theme_settings');
          $denali_theme_settings['logo'] = $curr_option['logo'];
      }

      
      if($_REQUEST['denali_theme_settings']['install_denali_child_theme'] == 'true') {
        //** Install theme if it is not */
        if(denali_theme::install_child_theme()) {
          $denali_theme_settings['install_denali_child_theme'] = 'true';
        } else {
          $denali_theme_settings['install_denali_child_theme'] = 'false'; 
        }
 
      }
      
      update_option('denali_theme_settings', $denali_theme_settings);      
      
      //** Redirect page to default Theme Settings page */
      wp_redirect(admin_url('themes.php?page=functions.php&message=settings_updated'));
      die();    

    }

    

    if(current_user_can('edit_theme_options') && wp_verify_nonce($_REQUEST['_wpnonce'], 'denali_auto_setup')) {

    $dfts = $_REQUEST['dfts'];
 
    //** Load current settings */
    $denali_theme_settings = stripslashes_deep(get_option('denali_theme_settings'));

    if(!empty($dfts)) {


      //** Set default title in header if no image or text exists already */
      if(empty($denali_theme_settings['logo_text']) && empty($denali_theme_settings['logo'])) {
        $denali_theme_settings['logo_text'] = get_bloginfo('name');
      }

      $default_search_widget_config = array(
        'title' => __('Property Search'),
        'searchable_property_types' => $wp_properties['searchable_property_types'],
        'searchable_attributes' => array_slice($wp_properties['searchable_attributes'], 0, 4)
      );

      foreach($dfts as $key => $value) {


        switch($key) {

          //** Do these first in case property data is needed later */
          case 'automation_tasks':

            foreach($value as $best_practice) {

              switch ($best_practice) {

                case 'generate_properties':
                  denali_theme::generate_dummy_properties();
                break;

                case 'create_agents':

                  //** Create some default fields */
                  if(empty($wp_properties['configuration']['feature_settings']['agents']['agent_fields'])) {
                    $wp_properties['configuration']['feature_settings']['agents']['agent_fields']['phone_number']['name'] = "Phone Number";
                    $wp_properties['configuration']['feature_settings']['agents']['agent_fields']['website_url']['name'] = "Website URL";
                  }

                  denali_theme::generate_dummy_agents();
                break;

              }

            }
          break;

          //** Setup color scheme, exact value is passed */
          case 'color_scheme':

            $denali_theme_settings['color_scheme'] = $value;

            if(empty($value)) {
              remove_theme_mod('background_image');
              remove_theme_mod('background_image_thumb');
              set_theme_mod('background_repeat', 'repeat-x');
            }

            if($value == 'skin-dark-colors.css') {
              set_theme_mod('background_color', '868686');
            }

            if($value == 'skin-dark-colors.css' || $value == 'skin-blue.css') {
              set_theme_mod('background_color', '');
              set_theme_mod('background_image', '');
              set_theme_mod('background_image_thumb', '');
            }

          break;

          //** Setup color scheme, exact value is passed */
          case 'home_page':

            if($value == 'option_1') {
              //** Do not include a widget area on home page, have the regular content fill up entire page.  */
              denali_theme::remove_widget_from_sidebar('home_sidebar', 'all');

            } elseif ($value == 'option_2') {
              //** Show a sidebar on home page, and load some widgets in there.  */
              denali_theme::remove_widget_from_sidebar('home_sidebar', 'all');
              denali_theme::add_widget_to_sidebar('home_sidebar', 'text', array('title' => 'Welcome!', 'text' => 'This widget was added automatically to the <b>Home - Sidebar</b> widget area.'));
              denali_theme::add_widget_to_sidebar('home_sidebar', 'searchpropertieswidget',$default_search_widget_config);
            }

          break;

          case 'slideshow':

            if($value == 'option_1') {
              //** Show a slideshow with a property search widget.  */
              denali_theme::add_widget_to_sidebar('global_property_search', 'searchpropertieswidget', $default_search_widget_config);
              $denali_theme_settings['hide_slideshow_search'] = 'false';
              $denali_theme_settings['home_page_attention_grabber_area_hide'] = 'false';
              $wp_properties['configuration']['feature_settings']['slideshow']['glob']['image_size'] = 'home_page_slideshow';
              $wp_properties['configuration']['feature_settings']['slideshow']['glob']['link_to_property'] = 'true';
              $wp_properties['configuration']['feature_settings']['slideshow']['glob']['show_property_title'] = 'true';

              //** Somehow add the slidershow to home page */

            } elseif ($value == 'option_2') {
              //** Show a large slideshow, but no search widget.  */

              $denali_theme_settings['hide_slideshow_search'] = 'true';
              $denali_theme_settings['home_page_attention_grabber_area_hide'] = 'false';
              $wp_properties['configuration']['feature_settings']['slideshow']['glob']['image_size'] = 'property_slideshow';
              $wp_properties['configuration']['feature_settings']['slideshow']['glob']['link_to_property'] = 'true';
              $wp_properties['configuration']['feature_settings']['slideshow']['glob']['show_property_title'] = 'true';
              //** Somehow add the slidershow to home page */


            } elseif ($value == 'option_3') {
              //** Do not show any slideshow or search widget on home page at all.  */
              $denali_theme_settings['home_page_attention_grabber_area_hide'] = 'true';
            }

          break;

          case 'property_page':
            if($value == 'option_1') {
              //** Yes, show large slideshow above property information when slideshow images exist.  */
              $wp_properties['configuration']['feature_settings']['slideshow']['property']['image_size'] = 'property_slideshow';
              $denali_theme_settings['property_static_image_size'] = 'property_slideshow';
              $denali_theme_settings['never_show_property_slideshow'] = 'false';


            } elseif ($value == 'option_2') {
              //** No, I don't like slideshows or header images, do not show a header.  */
              $denali_theme_settings['property_static_image_size'] = '';
              $denali_theme_settings['never_show_property_slideshow'] = 'true';

            }

          break;


          case 'best_practices':

            foreach($wp_properties['property_types'] as $property_slug => $property_title) {
              $wpp_property_sidebars[] = "wpp_sidebar_$property_slug";
            }

            foreach($value as $best_practice) {

              switch ($best_practice) {

                case 'setup_single_property_page':
                  //** Setup the single listing page with all the widgets.*/

                  foreach($wpp_property_sidebars as $sidebar_id) {
                    denali_theme::add_widget_to_sidebar($sidebar_id, 'agentwidget', array('title' => '', 'saved_fields' => array('display_name', 'agent_image', 'widget_bio', 'phone_number')));
                    denali_theme::add_widget_to_sidebar($sidebar_id, 'gallerypropertieswidget', array('title' => 'Gallery', 'image_type' => 'sidebar_gallery', 'big_image_type' => 'large'));
                  }
                break;

                case 'fix_permalinks':
                  //** Setup my URLs to be pretty.*/
                  global $wp_rewrite;
                  update_option('permalink_structure', '/%category%/%postname%/');
                  $wp_rewrite->rewrite_rules();
                break;

                case 'setup_property_page':
                  //** Setup my property result page with shortcodes and attributes */

                  if(is_callable(array('WPP_F','setup_default_property_page'))) {
                    $post_page = WPP_F::setup_default_property_page();

                    if(!empty($post_page)) {
                      $wp_properties['configuration']['base_slug'] = $post_page['post_name'];
                      $wp_properties['configuration']['automatically_insert_overview'] = 'false';
                      $wp_properties['configuration']['do_not_override_search_result_page'] = 'true';
                    }
                  }
                    $wp_properties['configuration']['property_overview']['thumbnail_size'] = 'overview_thumbnail';

                  //** Setup attributes for property overviews */

                  $denali_theme_settings['property_overview_attributes']['detail'] = array_slice(array_keys($wp_properties['property_stats']), 0, 6);


                break;

                case 'setup_widgets':
                  //** Setup all the widgets. */

                  denali_theme::add_widget_to_sidebar('right_sidebar', 'pages', array(
                    'title' => __('Pages', 'wpp')
                  ));

                  denali_theme::add_widget_to_sidebar('right_sidebar', 'featuredpropertieswidget', array(
                    'title' => __('Featured', 'wpp'),
                    'image_type' => 'sidebar_gallery',
                    'big_image_type' => 'large'
                  ));


                  denali_theme::add_widget_to_sidebar('property_overview_sidebar', 'featuredpropertieswidget', array(
                    'title' => __('Featured', 'wpp'),
                    'image_type' => 'sidebar_gallery',
                    'big_image_type' => 'large'
                  ));

                  denali_theme::add_widget_to_sidebar('property_overview_sidebar', 'pages', array(
                    'title' => __('Pages', 'wpp')
                  ));

                break;

                case 'setup_homepage':
                  //** Setup up my homepage, put some properties on there, and a search form for good measure.*/

                  denali_theme::add_widget_to_sidebar('property_overview_sidebar', 'featuredpropertieswidget', array(
                    'title' => __('Featured', 'wpp'),
                    'image_type' => 'sidebar_gallery',
                    'big_image_type' => 'large'
                  ));

                  denali_theme::add_widget_to_sidebar('property_overview_sidebar', 'pages', array(
                    'title' => __('Pages', 'wpp')
                  ));

                  $home_page_id = denali_theme::setup_default_home_page();

                  if(get_option('show_on_front') != 'page') {
                    update_option('show_on_front', 'page');
                  }

                  update_option('page_on_front', $home_page_id);



                break;

              }

            }

          break;




        }

      }
    }

    //** Save settings */
    update_option('wpp_settings', $wp_properties);
    update_option('denali_theme_settings', $denali_theme_settings);

    //wp_redirect(admin_url('themes.php?page=functions.php&action=first_time_setup'));

    //** Redirect page to default Theme Settings page */
    wp_redirect(admin_url('themes.php?page=functions.php&message=auto_complete_done'));
    die();

    }

    }

  }


  /**
   * Displays first-time setup splash screen
   *
   *
   * @since Denali 1.0
   */
  static function show_first_time_setup() {
    global $wp_properties;
    $denali_theme_settings = stripslashes_deep(get_option('denali_theme_settings'));
    ?>
    <style type="text/css">
      .denali_setup_block {
        background: none repeat scroll 0 0 #F4F4F4;
        margin-bottom: 20px;
        overflow: auto;
        padding: 10px 10px 15px;
        width: 97%;
      }
      .denali_setup_block .step_explanation {
        float: left;
        width: 170px;
        margin: 9px 15px 9px 9px;
      }
      .denali_setup_block .step_explanation h3 {
        margin-top: 0;
      }
      .denali_setup_block .step_explanation p{
        color: #818181;
        font-size: 1.2em;
        line-height: 1.4em;
      }

      .block_options {
        float: left;
        display: block;

      }
      .block_options li {
        display: block;
        float: left;
      }

      .block_options.regular_list li {
        display: list;
        float: none;
      }

      .block_options li.denali_setup_option_block {
        margin-right: 25px;

      }

      .block_options li.denali_setup_option_block img {
        border: 5px solid #F4F4F4;
        cursor: pointer;
      }
      .block_options li.denali_setup_option_block.selected_option img {
        border: 5px solid #FFDC80;
      }
      .block_options li.denali_setup_option_block input {
        display: none;
      }
      .block_options li.denali_setup_option_block .option_note{
        background: none repeat scroll 0 0 #EBEBEB;
        margin-left: 1px;
        padding: 8px 16px;
        width: 195px;
      }
      .block_options li.denali_setup_option_block.selected_option .option_note {
        background: #FFD669;
      }

      input.denali_save_settings {
        background: none repeat scroll 0 0 #FFE89F;
        font-size: 1.5em;
        margin-left: 10px;
        padding: 5px 10px;
      }

      label.denali_save_settings {
        color: #A3A3A3;
        font-size: 1.3em;
        margin-left: 31px;
        margin-right: 12px;
        position: relative;
        top: -5px;
      }

      .bigger_text {
        font-size: 1.3em;
        width: 500px;
        margin-bottom: 15px;
      }


    </style>
    <script type="text/javascript">
      jQuery(document).ready(function() {

        //** Cycle through all checked checkboxes and highlight their parent elements */
        jQuery('li.denali_setup_option_block input[type=checkbox]').each(function() {

          var parent_row = jQuery(this).parents('ul.block_options');
          var parent_holder = jQuery(this).parents('li.denali_setup_option_block');

          if(jQuery(this).is(":checked")) {
            jQuery(parent_holder).addClass('selected_option');
          }

        });


        //** When a 'denali_setup_option_block' element is clicked, the child checkbox is checked, and the element is highlighted
        jQuery('li.denali_setup_option_block').click(function() {
          var parent_row = jQuery(this).parents('ul.block_options');
          var this_option_checkbox = jQuery('input[type=checkbox]', this);
          jQuery('li.denali_setup_option_block', parent_row).removeClass('selected_option');

          jQuery('input[type=checkbox]', parent_row).removeAttr("checked")
          jQuery(this_option_checkbox).attr('checked', true);

          jQuery(this).addClass('selected_option');

        });
      });
    </script>

    <h2><?php _e('Thank you for using the Denali Theme.'); ?></h2>
    <p><?php _e('This is a step-by-step process for quickly setting up your WP-Property website by following some of our best setup practices. '); ?></p>

    <form action="#" method="post">
      <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('denali_auto_setup'); ?>" />

      <div class="denali_setup_block">

        <div class="step_explanation">
          <h3 class="step_title"><?php _e('General Color Scheme.'); ?></h3>
          <p><?php _e('Which of the three default color schemes would you like to use?'); ?></p>
        </div>

         <ul class="block_options">
          <li class="denali_setup_option_block">
            <img src="<?php echo get_bloginfo('template_url'); ?>/skin-default.jpg" />
            <div class="option_note"><?php _e('Brown colors with a gradient background.'); ?> </div>
            <input type="checkbox"  <?php checked('', $denali_theme_settings['color_scheme']); ?> name="dfts[color_scheme]" value="" />
          </li>

         <?php
         if($skins = denali_theme::get_color_schemes()) {
          foreach($skins as $skin_slug => $skin_data) {

            //** Don't show skins without a thumb */
            if(empty($skin_data['thumb'])) {
              continue;
            }
         ?>
          <li class="denali_setup_option_block">
            <img src="<?php echo $skin_data['thumb']; ?>" />
            <div class="option_note"><?php echo $skin_data['description']; ?> </div>
            <input type="checkbox"  <?php checked($skin_slug, $denali_theme_settings['color_scheme']); ?> name="dfts[color_scheme]" value="<?php echo $skin_slug; ?>" />
          </li>
          <?php } }?>
         </ul>
      </div>


       <div class="denali_setup_block">
        <div class="step_explanation">
          <h3 class="step_title"><?php _e('Home Page'); ?></h3>
          <p><?php _e('How do you want to display the content on the home page?'); ?></p>
        </div>

         <ul class="block_options">
          <li class="denali_setup_option_block">
            <img src="<?php echo get_bloginfo('template_url'); ?>/img/admin/first_time_setup/row_4_opt_1.jpg" />
            <div class="option_note"><?php _e('Do not include a widget area on home page, have the regular content fill up entire page.'); ?> </div>
            <input type="checkbox" <?php echo (!is_active_sidebar('home_sidebar') ? 'checked="true"' :''); ?> name="dfts[home_page]" value="option_1" />
          </li>

          <li class="denali_setup_option_block">
            <img src="<?php echo get_bloginfo('template_url'); ?>/img/admin/first_time_setup/row_4_opt_2.jpg" />
            <div class="option_note"><?php _e('Show a sidebar on home page, and load some widgets in there.'); ?> </div>
            <input type="checkbox" <?php echo (is_active_sidebar('home_sidebar') ? 'checked="true"' :''); ?> name="dfts[home_page]" value="option_2" />
          </li>
         </ul>
      </div>

       <div class="denali_setup_block">
        <div class="step_explanation">
          <h3 class="step_title"><?php _e('Slideshow.'); ?></h3>
          <p><?php _e('Do you want to show a slideshow on the home page? How about a property search form?'); ?></p>
        </div>

         <ul class="block_options">
          <li class="denali_setup_option_block">
            <img src="<?php echo get_bloginfo('template_url'); ?>/img/admin/first_time_setup/row_2_opt_1.jpg" />
            <div class="option_note"><?php _e('Show a slideshow with a property search widget.'); ?> </div>
            <input type="checkbox" name="dfts[slideshow]" <?php echo ($denali_theme_settings['hide_slideshow_search'] != 'true' && $denali_theme_settings['home_page_attention_grabber_area_hide'] != 'true' ? 'checked="true"' :''); ?>  value="option_1" />
          </li>

          <li class="denali_setup_option_block">
            <img src="<?php echo get_bloginfo('template_url'); ?>/img/admin/first_time_setup/row_2_opt_2.jpg" />
            <div class="option_note"><?php _e('Show a large slideshow, but no search widget.'); ?> </div>
            <input type="checkbox" name="dfts[slideshow]"  <?php echo ($denali_theme_settings['hide_slideshow_search'] == 'true' && $denali_theme_settings['home_page_attention_grabber_area_hide'] != 'true' ? 'checked="true"' :''); ?>   value="option_2" />
          </li>

          <li class="denali_setup_option_block">
            <img src="<?php echo get_bloginfo('template_url'); ?>/img/admin/first_time_setup/row_2_opt_3.jpg" />
            <div class="option_note"><?php _e('Do not show any slideshow or search widget on home page at all.'); ?> </div>
            <input type="checkbox" name="dfts[slideshow]"  <?php echo ($denali_theme_settings['home_page_attention_grabber_area_hide'] == 'true' ? 'checked="true"' :''); ?>   value="option_3" />
          </li>
         </ul>
      </div>

       <div class="denali_setup_block">
        <div class="step_explanation">
          <h3 class="step_title"><?php _e('Property Page'); ?></h3>
          <p><?php _e('Should we show slideshows on the property pages?'); ?></p>
        </div>

         <ul class="block_options">
          <li class="denali_setup_option_block">
            <img src="<?php echo get_bloginfo('template_url'); ?>/img/admin/first_time_setup/row_3_opt_1.jpg" />
            <div class="option_note"><?php _e('Yes, show large slideshow above property information when slideshow images exist.'); ?> </div>
            <input type="checkbox" name="dfts[property_page]" <?php checked($denali_theme_settings['never_show_property_slideshow'], 'false'); ?> value="option_1" />
          </li>

          <li class="denali_setup_option_block">
            <img src="<?php echo get_bloginfo('template_url'); ?>/img/admin/first_time_setup/row_3_opt_2.jpg" />
            <div class="option_note"><?php _e('No, I don\'t like slideshows or header images, do not show a header.'); ?> </div>
            <input type="checkbox" name="dfts[property_page]" <?php checked($denali_theme_settings['never_show_property_slideshow'], 'true'); ?> value="option_2" />
          </li>
         </ul>
      </div>

       <div class="denali_setup_block">
        <div class="step_explanation">
          <h3 class="step_title"><?php _e('Best Practices'); ?></h3>
        </div>

         <ul class="block_options regular_list">
          <li class="bigger_text"><?php _e('We can quickly setup your site according to some of the best practices we have established while working with hundreds of our cilents, and helping them setup their WP-Property powered sites:'); ?></li>

          <li><input type="checkbox" name="dfts[best_practices][]" value="setup_homepage" id="setup_homepage"><label for="setup_homepage"> <?php _e('Setup up my homepage, put some properties on there, and a search form for good measure.'); ?></li>

          <?php if(is_callable(array('WPP_F','setup_default_property_page'))) {  ?>
          <li><input type="checkbox" name="dfts[best_practices][]" value="setup_property_page" id="setup_property_page"><label for="setup_property_page"> <?php _e('Setup my property result page with shortcodes and attributes.'); ?></li>
          <?php } ?>

          <li><input type="checkbox" name="dfts[best_practices][]" value="setup_single_property_page" id="setup_single_property_page"><label for="setup_single_property_page"> <?php _e('Setup the single listing page with all the widgets.'); ?></li>

          <?php if(get_option('permalink_structure') == '') { ?>
          <li><input type="checkbox" name="dfts[best_practices][]" value="fix_permalinks" id="fix_permalinks"><label for="fix_permalinks"> <?php _e('Setup my URLs to be pretty.'); ?></li>
          <?php } ?>

          <li><input type="checkbox" name="dfts[best_practices][]" value="setup_widgets" id="setup_widgets"><label for="setup_widgets"> <?php _e('Setup all the widgets.'); ?></li>

         </ul>

      </div>

       <div class="denali_setup_block">
        <div class="step_explanation">
          <h3 class="step_title"><?php _e('Other'); ?></h3>
        </div>

         <ul class="block_options regular_list">
          <li><input type="checkbox" name="dfts[automation_tasks][]" value="generate_properties" id="generate_properties"><label for="generate_properties"> <?php _e('Create a few sample properties.'); ?></li>

          <?php if(is_callable(array('class_agents','create_agent'))) { ?>
          <li><input type="checkbox" name="dfts[automation_tasks][]" value="create_agents" id="create_agents"><label for="create_agents"> <?php _e('Create a sample agent, a dedicated agent page, and associate with the sample properties.'); ?></li>
          <?php } ?>
         </ul>

      </div>

      <label class="denali_save_settings" for="denali_save_settings"><?php _e('...enough questions,'); ?></label>
      <input type="submit" value="<?php _e('Setup My Site!'); ?>" class="denali_save_settings" />

    </form>

    <?php
  }


  /**
   * Adds "Theme Options" page on back-end
   *
   * Used for configurations that cannot be logically placed into a built-in Settings page
   *
   * @todo Update 'auto_complete_done' message to include a link to the front-end for quick view of setup results.
   *
   * @since Denali 1.0
   */
  static function options_page() {
    global $wp_properties;

    if($_GET['action'] == 'first_time_setup') {
      denali_theme::show_first_time_setup();
      return;
    }

    if($_REQUEST['message'] == 'auto_complete_done') {
        $updated = __('Your site has been setup.  You may configure more advanced options here.');
    }

    if($_REQUEST['message'] == 'settings_updated') {
        $updated = __('Settings updated.');
    }
    
    // Load latest settings
    $denali_theme_settings = stripslashes_deep(get_option('denali_theme_settings'));


    // Load some default settings
    if(!isset($denali_theme_settings['wpp_d_inquiry_fields'])) {
      $denali_theme_settings['wpp_d_inquiry_fields']['phone_number']['name'] = __('Phone Number');
    }


    ?>

    <script type="text/javascript">
      jQuery(document).ready(function($) {
        jQuery("#wpp_settings_tabs").tabs({ cookie: { expires: 30 } });

        jQuery("input[group=options_explore]").change(function(){

          if(jQuery("input#custom_html").is(":checked"))
            jQuery(".denali_theme_settings_explore_custom_html").show();
          else
            jQuery(".denali_theme_settings_explore_custom_html").hide();

        });


        jQuery(".denali_delete_logo").click(function() {
          jQuery.post(ajaxurl, {action: 'denali_actions', denali_action: 'delete_logo', _wpnonce: '<?php echo wp_create_nonce('denali_actions'); ?>'},function (result) {

           if(result.success == 'true') {
              jQuery(".current_denali_logo").remove();
            }

          }, 'json');

        });


       jQuery(".denali_help_wrap .denali_help_switch, .denali_help_wrap .denali_help_element").click(function() {
        var parent= jQuery(this).parents('.denali_help_wrap');
        jQuery('.denali_help_element', parent).toggle();

       });

      });
    </script>
    <style type="text/css">

    #tab_property_display .alignright,
    #tab_property_display .alignleft {
      width: 49%;
    }

    .big_text { font-size: 1.3em; width: 400px;}
    .denali_theme_settings_explore_custom_html {
      padding-top: 15px;
    }
    .denali_theme_settings_explore_custom_html textarea {
      height: 150px;
    }

    #wpp_settings_tabs .options_page_message {
        background: none repeat scroll 0 0 #F8FBE9;
        border: 1px solid #DBDEB7;
        margin: 10px;
        padding: 10px;
    }

    #wpp_settings_tabs .ui-tabs-panel {
        padding: 0px;
    }
    #wpp_settings_tabs .form-table{
        margin: 10px;
    }

    .denali_delete_logo {
      margin-top: 10px;
    }

    #wpp_settings_tabs p {
        margin: 15px 6px;
    }
    textarea {width: 400px; }
    input[type=text] {width: 300px; }

    .denali_help_wrap {
      clear:both;
    }

    .denali_help_switch {
      padding-top: 5px;
      color: #6B79B3;
      text-decoration: underline;
      cursor: pointer;
    }

    .denali_help_element {
      display:none;
    }
    .denali_help_image {
      border: 5px solid #E0E0E0;
      margin: 10px 0;
    }



    </style>
    <div id="denali_settings_page" class="wrap">
      <h2><?php _e('Denali Settings'); ?>
      <a class="add-new-h2" href="<?php echo admin_url('themes.php?page=functions.php&action=first_time_setup'); ?>"><?php _e('Open Setup Assistant'); ?></a>
      </h2>
      <?php if($updated): ?>
          <div class="updated fade">
              <p><?php echo $updated; ?></p>
          </div>
      <?php endif; ?>
      <form action="<?php echo admin_url('themes.php?page=functions.php'); ?>" method="post" enctype="multipart/form-data">
      <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('denali_settings'); ?>" />
      <div id="wpp_settings_tabs" class="clearfix">
        <ul class="tabs">
          <li><a href="#tab_misc"><?php _e('General Settings','wpp'); ?></a></li>
          <li><a href="#tab_property_display"><?php _e('Property Display','wpp'); ?></a></li>
          <li><a href="#tab_footer"><?php _e('Footer','wpp'); ?></a></li>
          <li><a href="#tab_inquiry"><?php _e('Inquiry','wpp'); ?></a></li>
          <li><a href="#tab_contact"><?php _e('Contact Info','wpp'); ?></a></li>
          <li><a href="#tab_logo"><?php _e('Logo','wpp'); ?></a></li>
        </ul>

        <div id="tab_misc">
        <div class="options_page_message">
        <p>If you would like to change the header images, please visit the <a href="<?php echo admin_url("themes.php?page=custom-header"); ?>">Header page</a>.</p>
        <p>The navigational menus are configured are on the  <a href="<?php echo admin_url("nav-menus.php"); ?>">Menus page</a>.</p>
        <p>And be sure to configure the widgets on the  <a href="<?php echo admin_url("widgets.php"); ?>">Widgets page</a>.</p>
        </div>
        <table class="form-table">
        <tbody>
        <tr valign="top">
        <th>General Settings</th>
        <td>
          <ul>

          <li>
              <input type='hidden' name='denali_theme_settings[maintanance_mode]' value='false' /><input type='checkbox' id="maintanance_mode" name='denali_theme_settings[maintanance_mode]' value='true'  <?php if($denali_theme_settings['maintanance_mode'] == 'true') echo " CHECKED " ?>/>
              <label for="maintanance_mode">Put site into maintanance mode.</label>
              <br />
              <span class="description">Maintanance mode will display a splash image on front-end for non-administrators while you make changes.</span>
          </li>


          <li>
              <input type='hidden' name='denali_theme_settings[show_property_comments]' value='false' /><input type='checkbox' id="show_property_comments" name='denali_theme_settings[show_property_comments]' value='true'  <?php if($denali_theme_settings['show_property_comments'] == 'true') echo " CHECKED " ?>/>
              <label for="show_property_comments">Don't treat property comments as inquiries.</label>
              <br />
              <span class="description">If enabled, property comments will be displayed on front-end and handled as comments.  If left disabled, comments will be treated as inquiries. You can enable/disable comments on individual property pages.</span>
              <div class="denali_help_wrap">
            <div class="denali_help_switch"><?php _e('More about inquiries', 'wpp'); ?></div>
            <img src="<?php echo get_bloginfo('template_url'); ?>/img/admin/2011-05-11_0856.png" class='denali_help_image denali_help_element' />
          </div>

          <li>
            <input <?php echo checked('true', $denali_theme_settings['hide_logo']); ?> type="checkbox"  name='denali_theme_settings[hide_logo]' id="hide_logo"  value="true" />
            <label for="hide_logo">Hide logo from header.</label>
          </li>

          <li>
            <input <?php echo checked('true', $denali_theme_settings['use_shortcodes_in_text_widget']); ?> type="checkbox"  name='denali_theme_settings[use_shortcodes_in_text_widget]' id="use_shortcodes_in_text_widget"  value="true" />
            <label for="use_shortcodes_in_text_widget">Execute shortcodes in text widgets.</label>
          </li>

          </ul>

        </td>
        </tr>
        <tr valign="top">

        <th><label for="name"><?php _e('Color Scheme', 'wpp'); ?><label></th>
          <td>
          <?php $color_schemes = denali_theme::get_color_schemes(); ?>

           <?php if($color_schemes): ?>

            <ul>
              <li>
              <input <?php echo checked(false, $denali_theme_settings['color_scheme']); ?> type="radio" group="denali_color_scheme" name='denali_theme_settings[color_scheme]' id="color_scheme_default"  value="" />
              <label for="color_scheme_default">Default Colors</label>
              </li>
              
            <?php foreach($color_schemes as $scheme => $scheme_data): ?>
              <li>
              <input group="denali_color_scheme" <?php echo checked($scheme, $denali_theme_settings['color_scheme']); ?> type="radio" name='denali_theme_settings[color_scheme]' id="color_scheme_<?php echo $scheme; ?>"  value="<?php echo $scheme; ?>" />
              <label for="color_scheme_<?php echo $scheme; ?>"><?php echo $scheme_data['name']; ?> - <?php echo $scheme_data['description']; ?></label>
              </li>
            <?php endforeach; ?>
            </ul>

           <?php else: ?>
            You don't have any extra color schemes. <br />
           <?php endif; ?>
            <span class="description">To create a color palette, upload a CSS file into wp-content/themes/denali/colors and it will be listed here.</span>
              </td>
        </tr>

        <tr valign="top">

        <th>
          <?php _e('Single Property Header Image', 'wpp'); ?>
          <div class="description"><?php _e('Image size to display in the header of a single property page.', 'wpp'); ?></div>
          </th>
        <td>
          <ul>
          <li>
          <?php WPP_F::image_sizes_dropdown("blank_selection_label= No Static Header Image &name=denali_theme_settings[property_static_image_size]&selected={$denali_theme_settings['property_static_image_size']}"); ?>

          </li>
          <li>
            <input <?php echo checked('true', $denali_theme_settings['hide_single_page_header_if_image_too_small']); ?> type="checkbox"  name='denali_theme_settings[hide_single_page_header_if_image_too_small]' id="hide_single_page_header_if_image_too_small"  value="true" />
            <label for="hide_single_page_header_if_image_too_small"><?php _e("If the image size you selected above does not exist, and there is no slidshow, do not show header area at all on property pages.", 'wpp'); ?></label>
          </li>
          <li>
            <input <?php echo checked('true', $denali_theme_settings['never_show_property_slideshow']); ?> type="checkbox"  name='denali_theme_settings[never_show_property_slideshow]' id="never_show_property_slideshow"  value="true" />
            <label for="never_show_property_slideshow"><?php _e("Never display a slideshow on property pages.", 'wpp'); ?></label>
          </li>
          </ul>
            <div class="denali_help_wrap">
            <div class="denali_help_switch"><?php _e('More about property header images.', 'wpp'); ?></div>
            <img src="<?php echo get_bloginfo('template_url'); ?>/img/admin/2011-05-11_0919.png" class='denali_help_image denali_help_element' />
          </div>
          </li>
        </td>
        </tr>


        <tr valign="top">
        <th>Send Notifications To</th>
        <td>
            <input type="text" name="denali_theme_settings[email]" id="email" value="<?php echo $denali_theme_settings['email'];?>" />
            <br /><span class="description">Messages submitted via the "Contact Us" form will be sent here. Separate multiple recipients with a comma.</span>
        </td>
    </tr>

    <tr valign="top">
        <th>Email From Address</th>
        <td>
            <input type="text" name="denali_theme_settings[email_from]" id="email" value="<?php echo $denali_theme_settings['email_from'];?>" />
            <br /><span class="description">This is the email messaged sent by the website will appear to be sent from. You can do something like this: <b>Contact Form &lt;website@mydomain.com&gt;</b></span>
        </td>
    </tr>

      <tr valign="top">
      <th>Dropdown Header</th>
      <td>
        <ul>
        <?php if(current_theme_supports('header-login')): ?>
        <li>
          <input <?php echo checked('true', $denali_theme_settings['hide_header_login']); ?> type="checkbox"  name='denali_theme_settings[hide_header_login]' id="denali_theme_settings_hide_header_login"  value="true" />
          <label for="denali_theme_settings_hide_header_login">Hide "Login" tab in header.</label>
        </li>
        <?php endif; ?>
        <?php if(current_theme_supports('header-property-contact')): ?>
        <li>
          <input <?php echo checked('true', $denali_theme_settings['hide_header_contact']); ?> type="checkbox"  name='denali_theme_settings[hide_header_contact]' id="denali_theme_settings_hide_header_contact"  value="true" />
          <label for="denali_theme_settings_hide_header_contact">Hide "Contact Us" tab in header.</label>
        </li>
        <?php endif; ?>
        </ul>

        </td>
        </tr>

        <tr valign="top">
        <th><?php _e('Category Pages'); ?></th>
        <td>
        <ul>
        <li>
          <input <?php echo checked('true', $denali_theme_settings['hide_meta_data_on_category_pages']); ?> type="checkbox"  name='denali_theme_settings[hide_meta_data_on_category_pages]' id="denali_theme_settings_hide_meta_data_on_category_pages"  value="true" />
          <label for="denali_theme_settings_hide_meta_data_on_category_pages"><?php _e('Hide post meta (date posted, author, categories listed in,etc) on category pages.', 'wpp'); ?></label>
        </li>
        </ul>
        <div class="denali_help_wrap">
          <div class="denali_help_switch"><?php _e('What is "post meta"?', 'wpp'); ?></div>
          <img src="<?php echo get_bloginfo('template_url'); ?>/img/admin/2011-05-11_0932.png" class='denali_help_image denali_help_element' />
        </div>
        </td>
        </tr>


        <tr valign="top">
        <th><?php _e('Home Page'); ?></th>
        <td>
        <ul>

        <li>
          <input <?php echo checked('true', $denali_theme_settings['hide_slideshow_search']); ?> type="checkbox"  name='denali_theme_settings[hide_slideshow_search]' id="hide_slideshow_search"  value="true" />
          <label for="hide_slideshow_search">Hide "Property Search" box from slideshow on home page.</label>
        </li>
        <li>
          <input <?php echo checked('true', $denali_theme_settings['hide_slideshow_search_from_posts_page']); ?> type="checkbox"  name='denali_theme_settings[hide_slideshow_search_from_posts_page]' id="hide_slideshow_search_from_posts_page"  value="true" />
          <label for="hide_slideshow_search_from_posts_page">Hide "Property Search" from slideshow on Posts Page.</label><br />
          <span class="description">"Posts Page" is typically used to display the "Blog" part of site when WordPress is used as a CMS. You can configure the posts page on the <a href="<?php echo admin_url("options-reading.php"); ?>">WordPress Reading settings page</a>.</span>
        </li>

        <?php if(current_theme_supports('home_page_attention_grabber_area')): ?>
        <li>
          <input <?php echo checked('true', $denali_theme_settings['home_page_attention_grabber_area_hide']); ?> type="checkbox"  name='denali_theme_settings[home_page_attention_grabber_area_hide]' id="home_page_attention_grabber_area_hide"  value="true" />
          <label for="home_page_attention_grabber_area_hide">Hide the entire slideshow and property search area on the home page.</label>
        </li>
        <?php endif; ?>

        <?php if(current_theme_supports('post_page_attention_grabber_area')): ?>
        <li>
          <input <?php echo checked('true', $denali_theme_settings['post_page_attention_grabber_area_hide']); ?> type="checkbox"  name='denali_theme_settings[post_page_attention_grabber_area_hide]' id="post_page_attention_grabber_area_hide"  value="true" />
          <label for="post_page_attention_grabber_area_hide">Hide the entire slideshow and property search area on the posts page.</label>
        </li>
        <?php endif; ?>

        </ul>

          <div class="denali_help_wrap">
            <div class="denali_help_switch"><?php _e('More about home and post page property search.', 'wpp'); ?></div>
            <img src="<?php echo get_bloginfo('template_url'); ?>/img/admin/2011-05-11_0923.png" class='denali_help_image denali_help_element' />
          </div>

        </td>
        </tr>

        <tr valign="top">
          <th><?php _e('Child Theme'); ?></th>
          <td>
            <ul>
            <li>
              <?php if(is_child_theme()) { ?>
               <?php _e('You are currently using the Default Denali child theme, you are safe to make code and CSS customizations.'); ?>
              <?php } elseif(denali_theme::denali_child_theme_exists()) { ?>
              <?php _e('Default Denali child theme is installed. Please be sure to make any code customizations in there to avoid losing your changes on upgrade.'); ?>
              <?php } else { ?>
              
              <input <?php echo checked('true', $denali_theme_settings['install_denali_child_theme']); ?> type="checkbox"  name='denali_theme_settings[install_denali_child_theme]' id="denali_theme_settings_install_denali_child_theme"  value="true" />
              <label for="denali_theme_settings_install_denali_child_theme"><?php _e('Install Denail child theme.', 'wpp'); ?></label>
              
              <?php if($denali_theme_settings['install_denali_child_theme'] == 'false') { ?>
              <?php _e('Note: There was a problem installing the default Child Theme. You may need to do manually.'); ?>
              <?php } ?>
              
              <?php } ?>
            </li>
            </ul>
          </td>
        </tr>


      </tbody>
    </table>
    </div>


    <div id="tab_footer" style="display:block">
      <table class="form-table">

   <tr valign="top">
      <th><?php _e('What do you want to show in Explore block?', 'wpp'); ?></th>
      <td>
        <?php $explore = $denali_theme_settings['options_explore']; ?>
        <ul>
            <li><input group='options_explore'  type="radio" name="denali_theme_settings[options_explore]" id="pages" value="pages" <?php if($explore =='pages') echo 'checked="checked"'; ?>> <label for="pages"> Pages</label></li>
            <li><input group='options_explore'  type="radio" name="denali_theme_settings[options_explore]" id="cats" value="cats" <?php if($explore =='cats') echo 'checked="checked"'; ?>> <label for="cats"> Categories</label></li>
            <li>
            <input group='options_explore' type="radio" name="denali_theme_settings[options_explore]" id="custom_html" value="custom_html" <?php checked($explore,'custom_html'); ?>> <label for="custom_html"> Custom HTML</label>
            <div class="denali_theme_settings_explore_custom_html <?php echo ($explore != 'custom_html' ? 'hidden' : ''); ?>">
              <textarea name="denali_theme_settings[explore][custom_html_content]" id="custom_html_content"><?php echo ($denali_theme_settings ? $denali_theme_settings['explore']['custom_html_content'] : ''); ?></textarea>
            </div>
            </li>
          <li><input type="checkbox" name="denali_theme_settings[footer_explore_block_hide]" id="footer_explore_block_hide" value="true" <?php checked($denali_theme_settings['footer_explore_block_hide'], 'true'); ?> /> <label for="footer_explore_block_hide">Hide the <b>Explore Block</b>.</label></li>
          <li><span class="description">The "Explore" block is located in footer of every page.</span></li>

          <li><input type="checkbox" name="denali_theme_settings[footer_explore_title_hide]" id="footer_explore_title_hide" value="true" <?php checked($denali_theme_settings['footer_explore_title_hide'], 'true'); ?> /> <label for="footer_explore_title_hide">Hide the <b>Explore Block</b> title.</label></li>
          <li>
          <input <?php echo checked('true', $denali_theme_settings['show_equal_housing_icon']); ?> type="checkbox"  name='denali_theme_settings[show_equal_housing_icon]' id="show_equal_housing_icon"  value="true" />
          <label for="show_equal_housing_icon">Show "Equal Housing Opportunity" icon in footer.</label>
          </li>
          </ul>

        <div class="denali_help_wrap">
          <div class="denali_help_switch"><?php _e('What is the "Explore" block?', 'wpp'); ?></div>
          <img src="<?php echo get_bloginfo('template_url'); ?>/img/admin/2011-05-11_0938.png" class='denali_help_image denali_help_element' />
        </div>


      </td>
    </tr>

      <tr>
      <th><?php _e('Social Media Links'); ?></th>
      <td>
        <p>A social media icon will be displayed on the bottom of every page for every social media link that is filled out.</p>
        <p>Be sure to put <b>http://</b> before the links.</p>

        <table class="form-table">
          <tbody>
              <tr valign="top">
                  <th><label for="twit">Twitter<label></th>
                  <td><input type="text" name="denali_theme_settings[social_twitter]" value="<?php echo $denali_theme_settings['social_twitter']; ?>"/></td>
              </tr>
              <tr valign="top">
                  <th><label for="fb">Facebook<label></th>
                  <td><input type="text" name="denali_theme_settings[social_facebook]"  value="<?php echo $denali_theme_settings['social_facebook'];?>"/></td>
              </tr>
              <tr valign="top">
                  <th><label for="in">LinkedIn<label></th>
                  <td><input type="text" name="denali_theme_settings[social_linkedin]" value="<?php echo $denali_theme_settings['social_linkedin'];?>" /></td>
              </tr>
              <tr valign="top">
                  <th><label for="rss">RSS<label></th>
                  <td><input type="text" name="denali_theme_settings[social_rss_link]"  value="<?php echo $denali_theme_settings['social_rss_link']; ?>"/></td>
              </tr>
              <tr valign="top">
                  <th><label for="rss">YouTube<label></th>
                  <td><input type="text" name="denali_theme_settings[social_youtube_link]"  value="<?php echo $denali_theme_settings['social_youtube_link']; ?>"/></td>
              </tr>
          </tbody>
        </table>
        <div class="denali_help_wrap">
          <div class="denali_help_switch"><?php _e('What are social media icons?', 'wpp'); ?></div>
          <img src="<?php echo get_bloginfo('template_url'); ?>/img/admin/2011-05-11_0935.png" class='denali_help_image denali_help_element' />
        </div>
      </td>
      </tr>
    </table>


    </div>
      <div id="tab_property_display" style="display:block">
      <?php
        // Add extra attributes
        $wp_properties['property_meta']['post_content'] = 'Property Content';

      ?>
      <table class="form-table">
      <tbody>
      <tr>
        <th><?php _e('Overview Attributes'); ?>
        <div class="description"><?php _e('Select the attributes to display in the [property_overview] shortcodes.', 'wpp'); ?></div>
        </th>
        <td>
          <div class="alignleft">
          <b>Horizontal List - does not display titles, just the values with an icon, if one exists</b>
          <div class="wp-tab-panel">
          <ul>
          <?php foreach($wp_properties['property_meta'] as $attrib_slug => $attrib_title): ?>
            <li><?php echo UD_UI::checkbox("id=property_overview_attributes_{$attrib_title}_stats&name=denali_theme_settings[property_overview_attributes][stats][]&label=$attrib_title&value={$attrib_slug}", (is_array($denali_theme_settings['property_overview_attributes']['stats']) && in_array($attrib_slug, $denali_theme_settings['property_overview_attributes']['stats']) ? true : false)); ?></li>
          <?php endforeach; ?>
          <?php foreach($wp_properties['property_stats'] as $attrib_slug => $attrib_title): ?>
            <li><?php echo UD_UI::checkbox("id=property_overview_attributes_{$attrib_title}_stats&name=denali_theme_settings[property_overview_attributes][stats][]&label=$attrib_title&value={$attrib_slug}", (is_array($denali_theme_settings['property_overview_attributes']['stats']) && in_array($attrib_slug, $denali_theme_settings['property_overview_attributes']['stats']) ? true : false)); ?></li>
          <?php endforeach; ?>

          </ul>
          </div>
          </div>

          <div class="alignright">
          <b>Detailed list below the horizontal list, includes titles and values.</b>
          <div class="wp-tab-panel">
          <ul>
          <?php foreach($wp_properties['property_stats'] as $attrib_slug => $attrib_title): ?>
            <li><?php echo UD_UI::checkbox("id=property_overview_attributes_{$attrib_title}_detail&name=denali_theme_settings[property_overview_attributes][detail][]&label=$attrib_title&value={$attrib_slug}", (is_array($denali_theme_settings['property_overview_attributes']['detail']) && in_array($attrib_slug, $denali_theme_settings['property_overview_attributes']['detail']) ? true : false)); ?></li>
          <?php endforeach; ?>
          <?php foreach($wp_properties['property_meta'] as $attrib_slug => $attrib_title): ?>
            <li><?php echo UD_UI::checkbox("id=property_overview_attributes_{$attrib_title}_detail&name=denali_theme_settings[property_overview_attributes][detail][]&label=$attrib_title&value={$attrib_slug}", (is_array($denali_theme_settings['property_overview_attributes']['stats']) && in_array($attrib_slug, $denali_theme_settings['property_overview_attributes']['detail']) ? true : false)); ?></li>
          <?php endforeach; ?>

          </ul>
          </div>
          </div>
          <div class="denali_help_wrap">
            <div class="denali_help_switch"><?php _e('Which is which?', 'wpp'); ?></div>
            <img src="<?php echo get_bloginfo('template_url'); ?>/img/admin/2011-05-11_0842.png" class='denali_help_image denali_help_element' />
          </div>
        </td>
      </tr>
    <tr>
        <th><?php _e('Overview Attributes - Grid'); ?>
        <div class="description"><?php _e('Select the attributes to display in the [property_overview template=grid] shortcodes.'); ?></div>
        </th>
        <td>
           <div class="wp-tab-panel">
          <ul>
          <?php foreach($wp_properties['property_stats'] as $attrib_slug => $attrib_title): ?>
            <li><?php echo UD_UI::checkbox("id=property_overview_attributes_grid_{$attrib_title}_stats&name=denali_theme_settings[grid_property_overview_attributes][stats][]&label=$attrib_title&value={$attrib_slug}", (is_array($denali_theme_settings['grid_property_overview_attributes']['stats']) && in_array($attrib_slug, $denali_theme_settings['grid_property_overview_attributes']['stats']) ? true : false)); ?></li>
          <?php endforeach; ?>
          <?php foreach($wp_properties['property_meta'] as $attrib_slug => $attrib_title): ?>
            <li><?php echo UD_UI::checkbox("id=property_overview_attributes_grid_{$attrib_title}_stats&name=denali_theme_settings[property_overview_attributes][stats][]&label=$attrib_title&value={$attrib_slug}", (is_array($denali_theme_settings['property_overview_attributes']['stats']) && in_array($attrib_slug, $denali_theme_settings['grid_property_overview_attributes']['stats']) ? true : false)); ?></li>
          <?php endforeach; ?>

          </ul>
          </div>
          <div class="denali_help_wrap">
            <div class="denali_help_switch"><?php _e('What is this for?', 'wpp'); ?></div>
            <img src="<?php echo get_bloginfo('template_url'); ?>/img/admin/2011-05-11_0852.png" class='denali_help_image denali_help_element' />
          </div>
          </td>
      </tr>

      </tbody>
      </table>
      </div>


      <div id="tab_inquiry">
      <table class="form-table">
          <tbody>
          <tr>
            <th><?php _e('Fields'); ?></th>
            <td>
            <p><?php _e('Add any additional input fields you would like to be displayed on the property inquiry forms. Name and e-mail address are required and already displayed.'); ?>

         <table class="ud_ui_dynamic_table widefat" id="wpp_d_inquiry_fields">
          <thead>
            <tr>
              <th><?php _e('Field Name'); ?></th>
              <th style="width:50px;">Slug</th>
              <th style="width:90px;">Required</th>
              <th>&nbsp;</th>
            </tr>
          </thead>
          <tbody>

            <?php foreach($denali_theme_settings['wpp_d_inquiry_fields'] as $field_slug => $field_data): $field_value = $field_data['name']; ?>
              <tr new_row="false" slug="<?php echo $field_slug; ?>" class="wpp_dynamic_table_row">
                <td><input type="text" value="<?php echo $field_value; ?>" name="denali_theme_settings[wpp_d_inquiry_fields][<?php echo $field_slug; ?>][name]" class="slug_setter"></td>
                <td><input type="text" class="slug" readonly="readonly" value="<?php echo $field_slug; ?>"></td>
                <td><input type="checkbox" value="on" name="denali_theme_settings[wpp_d_inquiry_fields][<?php echo $field_slug; ?>][required]" <?php checked('on', $field_data['required']); ?>></td>
                <td><span class="wpp_delete_row wpp_link"><?php _e('Delete'); ?></span></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          <tfoot>
            <tr>
              <td colspan="4"><input type="button" value="<?php _e('Add Row'); ?>" class="wpp_add_row button-secondary"></td>
            </tr>
          </tfoot>
        </table>

        </td>
        </tr>
        <tr>
        <th><?php _e('Options'); ?></th>

        <td>
          <ul>
            <?php if(method_exists('class_agents', 'init')): ?>
            <li>
              <input <?php checked('on', $denali_theme_settings['wpp_d_show_agent_dropdown_on_inquiry']); ?> type="checkbox" name="denali_theme_settings[wpp_d_show_agent_dropdown_on_inquiry]" value="on" id="wpp_d_show_agent_dropdown_on_inquiry" />
              <label for="wpp_d_show_agent_dropdown_on_inquiry"><?php _e('Show agent dropdown on inquiry form listing all agents associated with the property.'); ?></label>
            </li>

            <?php endif; ?>
          </ul>
        </td>

        </tr>
        </table>


      </div>


      <div id="tab_contact">
      <div class="options_page_message">
      <p>"Contact Us" information at the top of every page.</p>
      </div>

    <table class="form-table">
      <tbody>
      <tr valign="top">
        <th><label for="name">Contact Name<label></th>
        <td>
          <input type="text" name="denali_theme_settings[name]" id="name" value="<?php echo  $denali_theme_settings['name'];  ?>"/>
          <br /><span class="description">Appears at the top of the "Contact Us" dropdown box.</span>
        </td>
      </tr>
      <tr valign="top">
        <th><label for="info">Info<label></th>
        <td>
          <textarea name="denali_theme_settings[info]" id="info"><?php echo  $denali_theme_settings['info']; ?></textarea>
          <br /><span class="description">Information displayed under the name, within the "Contact Us" dropdown header.</span>

          <div class="denali_help_wrap">
          <div class="denali_help_switch"><?php _e('Where is this displayed?', 'wpp'); ?></div>
          <img src="<?php echo get_bloginfo('template_url'); ?>/img/admin/2011-05-11_0943.png" class='denali_help_image denali_help_element' />
          </div>

        </td>
      </tr>
      <tr valign="top">
        <th><label for="address">Address<label></th>
        <td>
          <textarea name="denali_theme_settings[address]" id="address"><?php echo  $denali_theme_settings['address']; ?></textarea>
          <br /><span class="description">The address will be automatically converted into coordinates, and a Google Map will be displayed on the top of every page.</span>

          <div class="denali_help_wrap">
          <div class="denali_help_switch"><?php _e('How is this address used?', 'wpp'); ?></div>
          <img src="<?php echo get_bloginfo('template_url'); ?>/img/admin/2011-05-11_0941.png" class='denali_help_image denali_help_element' />
          </div>
        </td>

    </tr>

      <tr valign="top">
      <th><label for="address">Address<label></th>
      <td>

      <input  <?php echo checked('true', $denali_theme_settings['hide_address_from_card']); ?> type="checkbox" name='denali_theme_settings[hide_address_from_card]' id="hide_address_from_card"  value="true" />
      <label for="hide_address_from_card">Don't display the address below the phone number on top of page.</label>
      </td>
      </tr>

      <tr valign="top">
      <th><label for="phone">Phone Number<label></th>
      <td>
      <input type="text" name="denali_theme_settings[phone]" id="phone"  value="<?php echo $denali_theme_settings['phone']; ?>"/>
      <br /><span class="description">Displayed at the top and bottom of every page.</span>
      </td>
      </tr>


      <tr valign="top">
      <th><label for="phone">Fax Number<label></th>
      <td>
      <input type="text" name="denali_theme_settings[fax]" id="phone"  value="<?php echo $denali_theme_settings['fax']; ?>"/>
      </td>
      </tr>


        </tbody>
      </table>
      </div>

      <div id="tab_logo">
        <div class="options_page_message">
          <p>On this page you can load custom logo picture.</p>
          <p>Recommended height of the picture is: <b>100px</b> </p>
        </div>

        <table class="form-table">
          <tbody>
          <tr>

          <th>
            <label for="logo_text"><?php _e('Text Logo', 'wpp'); ?></a>
           </th>
           <td>
            <input class='big_text' id="logo_text" type="regular-text" value="<?php echo $denali_theme_settings['logo_text']; ?>" name="denali_theme_settings[logo_text]" />
            <span class="description"><?php _e('Leave blank if you have an image logo.', 'wpp'); ?></span>
           </td>
          </tr>

        <tr>
        <th>
          Image Logo
        </th>

        <td>

        <div class="denali_logo_upload">
        <p>
        Choose an image from your computer: <input type="file" name="logo" />
        </p>

          <?php if(!empty($denali_theme_settings['logo'])): ?>
            <div class='current_denali_logo'>
            <img src="<?php echo $denali_theme_settings['logo']; ?>" class="denali_logo" />
            <div class="denali_delete_logo"><span class="button "><?php _e('Delete Logo', 'wpp'); ?></span></div>
            </div>
          <?php else: ?>
          <p><?php _e('You currently do not have a logo uploaded. You may set one using CSS as well.', 'wpp'); ?></p>
          <?php endif; ?>
        </div>
        </td>
        </tr>
         </tbody>
      </table>

    </div>

      <br style="clear:both;" />
      <p class="submit">
      <input type="submit" value="Save Changes" class="button-primary" name="Submit"/>
      </p>
      </form>
    </div>
    <?php }



    /**
     * Configured default Denali widget areas
     *
     * @since Denali 1.0
     *
     */
   static function widgets_init() {


        register_sidebar( array(
            'name' => __( 'Global Property Search' ),
            'id' => 'global_property_search',
            'description' => __( 'This area is displayed in the header under "Find Your Property" and on home page next to slideshow. Place your property search widget in here.' ),
            'before_widget' => '<div id="%1$s" class="denali_widget clearfix  %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h5 class="widgettitle">',
            'after_title' => '</h5>',
        ) );

        // Area Featured Listings.
        register_sidebar( array(
            'name' => __( 'Home - Sidebar' ),
            'id' => 'home_sidebar',
            'description' => __( 'Sidebar located on the right side on the home page page below the property slideshow and search widget.' ),
            'before_widget' => '<div id="%1$s"  class="denali_widget clearfix  %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h5 class="widgettitle">',
            'after_title' => '</h5>',
        ) );

        register_sidebar( array(
            'name' => __( 'Posts Page Home - Sidebar' ),
            'id' => 'posts_page_sidebar',
            'description' => __( 'Sidebar located on the "Posts page" home page if you have one selected under Settings -> Reading -> Front page displays. "' ),
            'before_widget' => '<div id="%1$s"  class="denali_widget clearfix  %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h5 class="widgettitle">',
            'after_title' => '</h5>',
        ) );


        register_sidebar( array(
            'name' => __( 'Property Listing Sidebar' ),
            'id' => 'property_overview_sidebar',
            'description' => __( 'Sidebar located on the the property overview and search result pages. "' ),
            'before_widget' => '<div id="%1$s"  class="denali_widget clearfix  %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h5 class="widgettitle">',
            'after_title' => '</h5>',
        ) );


        // Area Featured Properties.
        register_sidebar( array(
            'name' => __( 'Home - Horizontal Widget Area' ),
            'id' => 'home_bottom_sidebar',
            'description' => __( 'Widget area located below home page content on left side. This is a good place for the "Featured Properties" widget.' ),
            'before_widget' => '<div id="%1$s" class="denali_widget clearfix %2$s">',
            'after_widget' => '</div>'
        ) );


        // Area Featured Listings.
        register_sidebar( array(
            'name' => __( 'Property - Horizontal Widget Area' ),
            'id' => 'denali_property_footer',
            'description' => __( 'Appears on every single property page below the content.' ),
            'before_widget' => '<div id="%1$s" class="denali_widget clearfix  %2$s">',
            'after_widget' => '</div>'
        ) );



        // Area Featured Listings.
        register_sidebar( array(
            'name' => __( 'Inside Page Sidebar' ),
            'id' => 'right_sidebar',
            'description' => __( 'This widget area shows up on the right side of all "inside" pages, or any page other than the home page or a property page.' ),
            'before_widget' => '<div id="%1$s" class="denali_widget clearfix  %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h5 class="widgettitle">',
            'after_title' => '</h5>',
        ) );


        //Area Latest Listing (footer)
         register_sidebar( array(
            'name' => __( 'Footer - Bottom Left Block' ),
            'id' => 'latest_listings',
            'description' => __( 'This widget area is in the bottom left corner.  It works best with listing properties, showing only thumbnails.' ),
            'before_widget' => '<div id="%1$s" class="denali_widget clearfix %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h5 class="widgettitle">',
            'after_title' => '</h5>',
        ) );

    do_action('denali_widgets_init');

    }

   /**
     * Add CSS to comment page for phone number
     *
     * @since Denali 1.0
     */
  function comment_page_css() {
    echo '<style type="text/css">th#phone_number{width: 120px;}</style>';
  }

  /**
     * Add Phone number column
     *
     * @since Denali 1.0
     */
  function add_inquiry_columns($columns) {
     $denali_theme_settings = stripslashes_deep(get_option('denali_theme_settings'));

    // Load some default settings
    if(!isset($denali_theme_settings['wpp_d_inquiry_fields']))
      $denali_theme_settings['wpp_d_inquiry_fields']['phone_number']['name'] = __('Phone Number');

    foreach($denali_theme_settings['wpp_d_inquiry_fields'] as $slug => $data)
      $columns[$slug] = $data['name'];

    return $columns;

  }


   /**
     * Display phone number in column in comment row
     *
     * @since Denali 1.0
     */
  function manage_comments_custom_column($column_name, $comment_ID) {

    $denali_theme_settings = stripslashes_deep(get_option('denali_theme_settings'));

    // Load some default settings
    if(!isset($denali_theme_settings['wpp_d_inquiry_fields']))
      $denali_theme_settings['wpp_d_inquiry_fields']['phone_number']['name'] = __('Phone Number');

    foreach($denali_theme_settings['wpp_d_inquiry_fields'] as $slug => $data) {


      if($column_name == $slug) {
        echo get_comment_meta($comment_ID, $slug, true);
      }


    }

  }
  
  /**
   * Determine if the current comment is Inquiry.
   * Used as hook for filter 'pre_render_inquiry_form'.
   *
   * @since Denali 2.1
   * @author Maxim Peshkov
   */
  function pre_render_inquiry_form ($inquiry) {
    
    /* Determine if we are no using property comments as inquiries */
    $denali_theme_settings = stripslashes_deep(get_option('denali_theme_settings'));
    if($denali_theme_settings['show_property_comments'] == 'true') {
      $inquiry = false;
    }
    
    return $inquiry;
  }

  
  /**
   * Handles remaining functionality after comment creation
   * to avoid sending notification to moderator/postauthor (actually, admin)
   * if the current comment is Inquiry
   * AND if comment's post is property and real agent exist in delivery
   * OR admin has disabled admin notifications. 
   *
   * @since Denali 2.1
   * @author Maxim Peshkov
   */
  function pre_send_admin_inquiry_notification ($comment_ID, $approved) {
    global $post;
    
    /* Determine if the current comment is spam */
    if($approved === 'spam') {
      return;
    }
    
    /* Determine if post is not property */
    if($post->post_type != 'property') {
      return;
    }
    
    /* Bail if we are no using property comments as inquiries */
    $denali_theme_settings = stripslashes_deep(get_option('denali_theme_settings'));
    if($denali_theme_settings['show_property_comments'] == 'true') {
      return;
    }
    
    /* Determine if Real Agent exist in delivery */
    if(empty($_REQUEST['wpp_agent_contact_id'])) {
      return;
    }
    
    /* 
     * If all conditions are passed
     * Handle remaining functionality after comment creation
     * this functionality is duplicated from /wp-comments-post.php
     */
    $user = wp_get_current_user();
    $comment = get_comment($comment_ID);
    if ( !$user->ID ) {
      $comment_cookie_lifetime = apply_filters('comment_cookie_lifetime', 30000000);
      setcookie('comment_author_' . COOKIEHASH, $comment->comment_author, time() + $comment_cookie_lifetime, COOKIEPATH, COOKIE_DOMAIN);
      setcookie('comment_author_email_' . COOKIEHASH, $comment->comment_author_email, time() + $comment_cookie_lifetime, COOKIEPATH, COOKIE_DOMAIN);
      setcookie('comment_author_url_' . COOKIEHASH, esc_url($comment->comment_author_url), time() + $comment_cookie_lifetime, COOKIEPATH, COOKIE_DOMAIN);
    }
    
    $location = empty($_POST['redirect_to']) ? get_comment_link($comment_ID) : $_POST['redirect_to'] . '#comment-' . $comment_ID;
    $location = apply_filters('comment_post_redirect', $location, $comment);
    
    wp_redirect($location);
    exit;
  }
  
  
  /**
     * Process property inquiry saving
     *
     * @since Denali 1.0
     */
  function wp_insert_comment($id, $comment) {
    global $wpdb, $current_user;
    
    $denali_theme_settings = stripslashes_deep(get_option('denali_theme_settings'));
    
    // Bail if we are no using property comments as inquiries
    if($denali_theme_settings['show_property_comments'] == 'true') {
      return;
    }
    
    // Bail if comment is not about a property
    if($wpdb->get_var("SELECT post_type FROM {$wpdb->prefix}posts WHERE ID = '{$comment->comment_post_ID}'") != 'property') {
      return;
    }
    
    $property_id = $comment->comment_post_ID;
    
    if (is_user_logged_in()) {
      get_currentuserinfo();
      
      $inquiry['name'] = $current_user->display_name;
      $inquiry['email'] = $current_user->user_email;
    } else {
      $inquiry['name'] = $_REQUEST['author'];
      $inquiry['email'] = $_REQUEST['email'];
    }
    
    $inquiry['comment'] = $_REQUEST['comment'];
    $inquiry['property'] = $wpdb->get_var("SELECT post_title FROM {$wpdb->posts} WHERE ID = '{$property_id}' ") . " ({$property_id})";
    
    // Load some default settings
    if(!isset($denali_theme_settings['wpp_d_inquiry_fields']))
      $denali_theme_settings['wpp_d_inquiry_fields']['phone_number']['name'] = __('Phone Number');

    foreach($denali_theme_settings['wpp_d_inquiry_fields'] as $slug => $data) {
        // Check if phone number is set, if so - save
        if(!empty($_REQUEST[$slug])) {
          $new_value = $_REQUEST[$slug];
          add_comment_meta($id, $slug, $new_value);
          $inquiry[$slug] = $new_value;
        }
    }

    do_action('wpp_insert_property_comment', $comment, $inquiry);

  }



   /**
     * Sends notification to agent specified during inquiry
     *
     *
     * @since Denali 1.0
     */
  function send_agent_inquiry_notification($comment, $inquiry) {
    global $wpdb;
    
    if(!isset($_REQUEST['wpp_agent_contact_id'])) {
      return;
    }
    
    if(!method_exists('class_agents','send_agent_notification')) {
      return;
    }
    
    $subject = __('Inquiry About:', 'wpp') . " " . $inquiry['property'];
    
    foreach($inquiry as $key => $value) {
      $message_lines[] = UD_F::de_slug($key) . ": {$value}";
    }
    
    $message = __('Inquiry message:', 'wpp') . "\n\n" . implode("\n", $message_lines);
    
    class_agents::send_agent_notification($_REQUEST['wpp_agent_contact_id'], $subject, $message);
    
    return;
  }
  
   /**
     * Replace comment fields when comment form is being used for property inquiries
     *
     *
     * @since Denali 1.0
     */
  function comment_form_defaults($defaults) {
    global $post, $wpdb;
    
    $denali_theme_settings = stripslashes_deep(get_option('denali_theme_settings'));
    
    if($denali_theme_settings['show_property_comments'] == 'true') {
      return $defaults;
    }
    
    if($post->post_type !=  'property') {
      return $defaults;
    }
    
    // Remove website URL field
    unset($defaults['fields']['url']);

    // Rename "Comment" to "Message"
    $defaults['comment_field'] = '<p class="comment-form-comment">' .
    '<label for="comment">' . __( 'Message', 'wpp' ) . '</label>' .
    '<textarea id="comment" name="comment" cols="45" rows="8"  aria-required="true"></textarea>' .
    '</p><!-- #form-section-comment .form-section -->';
    
    
    // Load some default settings
    if(!isset($denali_theme_settings['wpp_d_inquiry_fields']))
      $denali_theme_settings['wpp_d_inquiry_fields']['phone_number']['name'] = __('Phone Number');
    
    /* If user logged in we clear all default fields */
    if (is_user_logged_in()) {
      $defaults['fields'] = array();
    }
    
    foreach($denali_theme_settings['wpp_d_inquiry_fields'] as $slug => $data) {

      unset($required);
      unset($class);

      $label = $data['name'];

      if($data['required'] == 'on') {
        $class = 'wpp_required_field';

        $label = $label . '<span class="required">*</span>';

        $required = " aria-required='true' ";
      }

      $defaults['fields'][$slug] = '<p class="comment-form-'.$slug.' '.$class.'">' .
      '<label for="'.$slug.'">' .$label . '</label> ' .
      '<input id="'.$slug.'" name="'.$slug.'" type="text" size="30" '.$required.' />' .
      '</p>';
    }
    
    
    if($denali_theme_settings['wpp_d_show_agent_dropdown_on_inquiry'] == 'on') {
      // Show agent
      if(is_array($post->wpp_agents)) {
        foreach($post->wpp_agents as $agent_id) {
          // Does agent accept notifications?
          if(get_user_meta($agent_id, 'notify_agent_on_property_inquiry', true) != 'on') {
            continue;
          }
          if($agent_name = $wpdb->get_var("SELECT display_name FROM {$wpdb->users} WHERE ID = '$agent_id'")) {
            $agent_options[]  = "<option value='$agent_id'>$agent_name</option>";
          }
        }
        
        if(count($agent_options) > 1) {
          $agent_dropdown = "<select id='wpp_agent' name='wpp_agent_contact_id'>" . implode('', $agent_options) . "</select>";
          $defaults['fields']['wpp_agent'] = '<p class="comment-form-wpp_agent">' .'<label for="wpp_agent">'.__('Send to Agent') . '</label>' . $agent_dropdown . '</p>';
        } elseif(count($agent_option == 1)) {
           $agent_dropdown = $defaults['fields']['wpp_agent'] = "<input type='hidden' name='wpp_agent_contact_id' value='{$agent_id}' />";
        }

      }
    }
    
    // Rename submitbutton
    $defaults['label_submit'] = __('Submit Inquiry','wpp');
    
    return $defaults;
  
  }

  /**
     * Checks Denali's new version (makes request to TCT server once per day)
     * Works like filter
     *
     * @param object $value contains information about current and new available versions of themes
     * @return object
     */
   static function check_denali_updates($value){
        $denali_theme = get_theme('Denali - Premium WP-Property Theme');

        if(empty($denali_theme)) {
            return $value;
        }

        // The option contains information about available theme's version and about time of last request to server
        $versionData = get_option('denali_version_updates');

        // If data of option is empty, it can be because of this option is not exist
        // So try to add option
        if(empty($versionData)) {
            $versionData = array();
            add_option('denali_version_updates', $versionData);
        }

        // Checks time of last request to TCT Server
        // If it is more then one day ago, - do request and update option 'denali_version_updates'
        if((time() - $versionData['last_checked']) > 86400) {
            $url = "http://updates.twincitiestech.com/themes/denali?refer=".urlencode(get_bloginfo('siteurl'));

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $httpResponse = curl_exec($ch);

            if(empty($httpResponse)) {
                return $value;
            }

            $versionData = json_decode($httpResponse, true);
            $versionData['last_checked'] = time();
            update_option('denali_version_updates', $versionData);
        }

        // If the current version is older then available new one,
        // Add ability to update it (by adding information to object)
        if ( $denali_theme['Version'] < $versionData['version']) {
            if(empty($value->checked)) {
                $value->checked = array();
            }

            $value->checked['denali'] = $denali_theme['Version'];

            if(empty($value->response)) {
                $value->response = array();
            }

            $value->response['denali'] = array(
                'new_version' => $versionData['version'],
                'url' => 'http://sites.twincitiestech.com/the-denali',
                'package' => 'http://updates.twincitiestech.com/themes/denali/download?refer='.urlencode(get_bloginfo('siteurl'))
            );
        }

        return $value;
    }

    /*
     * Adds option for showing notice on Theme Settings updating
     * And shows Notice 'Clear W3 Cache' if W3 Total Cache plugin is used
     *
     */
    static function show_clear_W3_total_cache_notice() {

        if(class_exists('W3_Plugin_TotalCache')) {

            // Checks Denali Settings Request and Add option
            if(wp_verify_nonce($_REQUEST['_wpnonce'], 'denali_settings')) {
                add_option('denali_theme_clear_cache_notice', 'true');
            }

            $clear_notice = get_option('denali_theme_clear_cache_notice');
            if(!empty($clear_notice)) {
                $note = '';
                ob_start();
                ?>
                <p><?php _e('Looks like Denali theme Settings were updated. But W3 Total Cache plugin is used. Please, clear cache to be sure that the changes are involved '); ?>
                <input type="button" value="Clear Page Cache" onclick="denali_delete_clearcache_option();document.location.href = 'admin.php?page=w3tc_general&amp;flush_pgcache';" class="button " />
                <?php _e('or') ?>
                <input type="button" value="Hide Notice" onclick="denali_delete_clearcache_option();denali_hide_notice();" class="button " />
                </p>
                <script type="text/javascript">
                   function denali_delete_clearcache_option() {
                        jQuery.ajax({
                            url: ajaxurl,
                            async: false,
                            type: 'POST',
                            data: 'action=denali_delete_option_clearcache'
                        });
                    }

                   function denali_hide_notice() {
                        jQuery('#denali_w3_total_cache_notice').slideToggle('slow', function(){
                            jQuery(this).remove();
                        });

                    }
                </script>
                <?php
                $note .= ob_get_contents();
                ob_end_clean();

                // Print notice
                echo sprintf('<div id="denali_w3_total_cache_notice" class="updated fade">%s</div>', $note);
            }
        } else {
            // Try to delete option
            delete_option('denali_theme_clear_cache_notice');
        }
    }

    /*
     * Ajax function. Deletes 'denali_theme_clear_cache_notice' option,
     * which is used for showing notice to clear W3 Cache if W3 Total Cache plugin is used.
     */
   static function delete_option_clearcache () {
        delete_option('denali_theme_clear_cache_notice');
        echo json_encode(array('status'=>'success'));
        exit();
    }


  /**
   * Determines if header area on this page should be displayed
   *
   * Only ran if static image must be displayed, checks if static image is big enough to fill area, if setting is set to do so
   *
   * @since Denali 1.0
   */
    static function show_this_header_area() {
      global $post;

      $denali_theme_settings = stripslashes_deep(get_option('denali_theme_settings'));
      $header_image_size = $denali_theme_settings['property_static_image_size'];

      if(empty($header_image_size))
        return false;

      // if not set to true, don't bother checking
      if($denali_theme_settings['hide_single_page_header_if_image_too_small'] != 'true')
        return true;

     // no featured image
      if(!has_post_thumbnail( $post->ID ))
        return false;

     $needed_size = WPP_F::get_image_dimensions($header_image_size);
     list($src, $width, $height) = @wp_get_attachment_image_src(get_post_thumbnail_id( $post_id ), $header_image_size);

      // fail if either the height or width don't match
     if($needed_size['width'] != $width)
      return false;

     if($needed_size['height'] != $height)
      return false;

     // If everything passed, then its good
     return true;

    }

  /**
   * Generates dummy agent(s)
   *
   *
   * @since Denali 1.0
   */
    function generate_dummy_agents( ) {
       global $wpdb;

       $dummy_properties = $wpdb->get_col("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'dummy_property'");

       if(!email_exists('agent.john@usabilitydynamics.com')) {

        $agent_id = class_agents::create_agent('agent.john', 'Agent John', 'agent.john@usabilitydynamics.com');
 

        if($agent_id) {
          update_user_meta($agent_id, 'first_name', 'Agent');
          update_user_meta($agent_id, 'last_name', 'John');
          update_user_meta($agent_id, 'widget_bio', "Agent John is a sample agent created for demonstration purposes only. Take a look at all of John\'s properties here.");
          update_user_meta($agent_id, 'phone_number', '800-270-0781');
          update_user_meta($agent_id, 'website_url', 'http://UsabilityDynamics.com');
        }

       } else {
 
        $agent_id = email_exists('agent.john@usabilitydynamics.com');
       }

       foreach($dummy_properties as $post_id){
        delete_post_meta($post_id, 'wpp_agents');
        add_post_meta($post_id, 'wpp_agents', $agent_id);
       }
       
        //** Create dedicated agent page */
        if(!$wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE post_name = 'agent_johns_listings' ")) {
          //** Create Agent Page */
          $insert_id = wp_insert_post(  array(
            'post_title' => 'Agent John',
            'post_status' => 'publish',
            'post_name' => 'agent_johns_listings',
            'post_content' => "This is an automatically generated page for Agent John.  [property_overview template=grid wpp_agents={$agent_id}].",
            'post_type' => 'page'
          ));
        }
        

    }


  /**
   * Generates dummy properties
   *
   * Only ran if static image must be displayed, checks if static image is big enough to fill area, if setting is set to do so
   *
   * @since Denali 1.0
   */
    function generate_dummy_properties( ) {
      global $user_ID, $wp_properties, $wpdb;
      
      /* Determine if the dummy properties already exist */
      $posts = $wpdb->get_col("
        SELECT `post_title`
        FROM {$wpdb->posts}
        WHERE `post_title` IN ('122 Bishopsgate', '2 Bedroom Home')
      ");
      /* Check array to avoid issues in future */
      if(!is_array($posts)) {
        $posts = array();
      }
      
      require_once(ABSPATH . 'wp-admin/includes/image.php');
      
      $upload_dir = wp_upload_dir();
      
      /* If Property doesn't exist we create it */
      if (!in_array('122 Bishopsgate', $posts)) {
        //** Move temporary image files */
        $dummy_images[] = TEMPLATEPATH . "/img/dummy_data/property_1_img_0.jpg";
        $dummy_images[] = TEMPLATEPATH . "/img/dummy_data/property_1_img_1.jpg";
        $dummy_images[] = TEMPLATEPATH . "/img/dummy_data/property_1_img_2.jpg";
        
        foreach( $dummy_images as $dummy_path) {
          if(@copy ($dummy_path, $upload_dir['path'] . "/" . basename($dummy_path))) {
            $image_files[] = $upload_dir['path'] . "/" . basename($dummy_path);
          }
        }
        
        $insert_id = wp_insert_post(  array(
          'post_title' => '122 Bishopsgate',
          'post_status' => 'publish',
          'post_content' => 'Take notice of this amazing home! It has an original detached 2 garage/workshop built with the home and on a concrete slab along with regular 2 car attached garage. Very nicely landscaped front and back yard. Hardwood floors in Foyer, den, dining and great room. Great room is open to large Kitchen. Carpet in all upstairs bedrooms. Home is located in the Woodlands in the middle of very nice community. You and your family will feel right at home. A must see.',
          'post_type' => 'property'
        ));
        
        update_post_meta($insert_id, 'property_type', 'single_family_home');
        update_post_meta($insert_id, 'tagline', 'Need Room for your TOYS! Take notice of this unique Home!');
        update_post_meta($insert_id, 'location', '122 Bishopsgate, Jacksonville, NC 28540, USA');
        update_post_meta($insert_id, 'price', '195000');
        update_post_meta($insert_id, 'bedrooms', '4');
        update_post_meta($insert_id, 'bathrooms', '4');
        update_post_meta($insert_id, 'phone_number', '8002700781');
        update_post_meta($insert_id, 'dummy_property', true);
        
        
        if(!empty($image_files)) {
          foreach($image_files as $filename) {
            $wp_filetype = wp_check_filetype(basename($filename), null );
            $attach_id = wp_insert_attachment(  array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
            'post_status' => 'inherit'
            ), $filename, $insert_id );
            $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
            wp_update_attachment_metadata( $attach_id, $attach_data );
          }
        }
        update_post_meta( $insert_id, '_thumbnail_id', $attach_id );
        
        unset($dummy_images);
        unset($image_files);
      }
      
      /* If Property doesn't exist we create it */
      if (!in_array('2 Bedroom Home', $posts)) {
        //** Move temporary image files */
        $dummy_images[] = TEMPLATEPATH . "/img/dummy_data/property_2_img_0.jpg";
        $dummy_images[] = TEMPLATEPATH . "/img/dummy_data/property_2_img_1.jpg";
        $dummy_images[] = TEMPLATEPATH . "/img/dummy_data/property_2_img_2.jpg";
        
        foreach( $dummy_images as $dummy_path) {
          if(@copy ($dummy_path, $upload_dir['path'] . "/" . basename($dummy_path))) {
            $image_files[] = $upload_dir['path'] . "/" . basename($dummy_path);
          }
        }
        
        $insert_id = wp_insert_post(  array(
          'post_title' => '2 Bedroom Home',
          'post_status' => 'publish',
          'post_content' => 'Donec volutpat elit malesuada eros porttitor blandit. Donec sit amet ligula quis tortor molestie sagittis tincidunt at tortor. Phasellus augue leo, molestie in ultricies gravida; blandit et diam. Curabitur quis nisl eros! Proin quis nisi quam, sit amet lacinia nisi. Vivamus sollicitudin magna eu ipsum blandit tempor. Duis rhoncus orci at massa consequat et egestas lectus ornare? Duis a neque magna, quis placerat lacus. Phasellus non nunc sapien, id cursus mi! Mauris sit amet nisi vel felis molestie pretium.',
          'post_type' => 'property'
        ));
        
        update_post_meta($insert_id, 'property_type', 'single_family_home');
        update_post_meta($insert_id, 'tagline', 'Great starter home in beautiful St. Paul, Minnesota.');
        update_post_meta($insert_id, 'location', '332 S Main St, St Paul, Minnesota ');
        update_post_meta($insert_id, 'price', '119000');
        update_post_meta($insert_id, 'bedrooms', '3');
        update_post_meta($insert_id, 'bathrooms', '2');
        update_post_meta($insert_id, 'phone_number', '8002700781');
        
        if(!empty($image_files)) {
          foreach($image_files as $filename) {
            $wp_filetype = wp_check_filetype(basename($filename), null );
            $attach_id = wp_insert_attachment(  array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
            'post_status' => 'inherit'
            ), $filename, $insert_id );
            $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
            wp_update_attachment_metadata( $attach_id, $attach_data );
          }
        }
        update_post_meta( $insert_id, '_thumbnail_id', $attach_id );
      }
    
    }



  /**
   * Installs a Denali child theme.
   *
   * Copies files from /denali-child folder into the them folder so denali child can be used.
   *
   * @since Denali 1.0
   */
    function install_child_theme( ) {
      global $user_ID, $wp_properties, $wpdb, $wp_theme_directories;

      if(denali_theme::denali_child_theme_exists()) {
        return true;
      }
  
      $destination = $wp_theme_directories[0];  

      $original = TEMPLATEPATH . '/denali-child';
        
       if(!file_exists($original)) {
        return false;
       }
       
       if(!is_writable($destination)) {       
        return false;
       }
         
       $destination = $destination . '/denali-child';

       //** Create destination folder */
       if (!@mkdir($destination, 0755)) {
          return false;
      }     
       
             
       if ($original_handle = opendir($original . '/')) {
         while (false !== ($file = readdir($original_handle))) {
          
          if ($file != "." && $file != "..") {
            
            $file_path = $original . '/'. $file;
            
            /* Determine if it's directory, We don't copy it */
            if (is_dir($file_path)) {
              continue;
            }
            
            if(copy($file_path, $destination . '/' . $file)) {
              $copied[] = $file;
            }  else {
              $not_copied[] = $file;
            }
          }
          
         }
       }
 
      if(count($copied) > 0) {
        return true;
      }
      
      return false;
        
  }

    


  /**
   * Check if default denali child theme exists.
   *
   *
   * @since Denali 1.0
   */
    function denali_child_theme_exists( ) {
      global $user_ID, $wp_properties, $wpdb;

      $themes =get_themes();

      foreach($themes as $theme) {

        if($theme['Name'] == 'Child Theme for Denali') {

          $denali_theme_settings = stripslashes_deep(get_option('denali_theme_settings'));
          $denali_theme_settings['install_denali_child_theme'] = 'true';
          update_option('denali_theme_settings', $denali_theme_settings);
          return true;
        }

      }


    }


}

if ( ! function_exists( 'denali_posted_on' ) ) :
/**
 * Prints HTML with meta information for the current post date/time and author.
 *
 * @since Denali 1.0
 */
function denali_posted_on() {
    printf( __( '<span class="%1$s">Posted on</span> %2$s <span class="meta-sep">by</span> %3$s', 'denali' ),
        'meta-prep meta-prep-author',
        sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><span class="entry-date">%3$s</span></a>',
            get_permalink(),
            esc_attr( get_the_time() ),
            get_the_date()
        ),
        sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s">%3$s</a></span>',
            get_author_posts_url( get_the_author_meta( 'ID' ) ),
            sprintf( esc_attr__( 'View all posts by %s', 'denali' ), get_the_author() ),
            get_the_author()
        )
    );
}
endif;



/**
 * Draws the header image for a given page
 *
 * Outside of class for ease-of-use within templaes
 *
 * @since Denali 1.0
 */
if ( ! function_exists( 'denali_header_image' ) ) :
   function denali_header_image($return = false, $url_only = false) {
    global $post;

    $denali_theme_settings = stripslashes_deep(get_option('denali_theme_settings'));

    if(function_exists('is_property_overview_page')) {
      $is_property_overview_page = is_property_overview_page();
    } else {
      $is_property_overview_page = false;

    }

    if(is_front_page() && !$is_property_overview_page) {  ?>
      <img src="<?php echo get_bloginfo('template_url'); ?>/img/headers/denali_default_home_header.jpg"  alt="" />
      <?php
      return;
    }

    $header_image = ($denali_theme_settings['property_static_image_size'] ?  $denali_theme_settings['property_static_image_size'] :'header_image');

    // Retrieve the dimensions of the current post thumbnail -- no teensy header images for us!
    $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), $header_image);

    list($src, $width, $height) = $image;

      // Check if this is a post or page, if it has a thumbnail, and if it's a big one
    if (($post->post_type == 'property' || is_singular()) && has_post_thumbnail( $post->ID )) :
      echo get_the_post_thumbnail( $post->ID, $header_image );
    elseif($get_header_image = get_header_image()) : ?>
          <img src="<?php echo $get_header_image; ?>" alt="<?php echo $post->post_title; ?>" />
  <?php endif;

    }
endif;


/**
 * Handles comments
 *
 * Based on denali 1.1 comment handler
 *
 * @since Denali 1.0
 */
if ( ! function_exists( 'denali_comment' ) ) :
function denali_comment( $comment, $args, $depth ) {
    $GLOBALS['comment'] = $comment;
    switch ( $comment->comment_type ) :
        case '' :
    ?>
    <li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
        <div id="comment-<?php comment_ID(); ?>">
        <div class="comment-author vcard">
            <?php echo get_avatar( $comment, 40 ); ?>
            <?php printf( __( '%s <span class="says">says:</span>', 'denali' ), sprintf( '<cite class="fn">%s</cite>', get_comment_author_link() ) ); ?>
        </div><!-- .comment-author .vcard -->
        <?php if ( $comment->comment_approved == '0' ) : ?>
            <em><?php _e( 'Your comment is awaiting moderation.', 'denali' ); ?></em>
            <br />
        <?php endif; ?>

        <div class="comment-meta commentmetadata"><a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
            <?php
                /* translators: 1: date, 2: time */
                printf( __( '%1$s at %2$s', 'denali' ), get_comment_date(),  get_comment_time() ); ?></a><?php edit_comment_link( __( '(Edit)', 'denali' ), ' ' );
            ?>
        </div><!-- .comment-meta .commentmetadata -->

        <div class="comment-body"><?php comment_text(); ?></div>

        <div class="reply">
            <?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
        </div><!-- .reply -->
    </div><!-- #comment-##  -->

    <?php
            break;
        case 'pingback'  :
        case 'trackback' :
    ?>
    <li class="post pingback">
        <p><?php _e( 'Pingback:', 'denali' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __('(Edit)', 'denali'), ' ' ); ?></p>
    <?php
            break;
    endswitch;
}
endif;

/**
 * Display Folow icons in the footer if required data is inputed
 */
if ( ! function_exists( 'denali_footer_follow' ) ) :
   function denali_footer_follow($denali_theme_settings) {

    $twitter = $denali_theme_settings['social_twitter'];
    $facebook = $denali_theme_settings['social_facebook'];
    $youtube = $denali_theme_settings['social_youtube_link'];
    $in = $denali_theme_settings['social_linkedin'];
    $rss = $denali_theme_settings['social_rss_link'];

    $template_dir = get_bloginfo('stylesheet_directory');

    if (!empty($twitter) || !empty($facebook) || !empty($in) || !empty($rss)) {
        if(trim($twitter) != '') echo '<a href="'. $twitter .'"><img src="'. $template_dir .'/img/follow_t.png" /></a> ';
        if(trim($facebook) != '') echo '<a href="'. $facebook .'"><img src="'. $template_dir .'/img/follow_f.png" /></a> ';
        if(trim($youtube) != '') echo '<a href="'. $youtube .'"><img src="'. $template_dir .'/img/follow_y.png" /></a> ';
        if(trim($in) != '') echo '<a href="'. $in .'"><img src="'. $template_dir .'/img/follow_in.png" /></a> ';
        if(trim($rss) != '') echo '<a href="'. $rss .'"><img src="'. $template_dir .'/img/follow_rss.png" /></a>';
    }

    }
endif;

/**
 * Conditional tag to determine if current page is selected to be the primary posts page
 *
 * @since Denali 1.7
 */
if ( ! function_exists( 'is_posts_page' ) ) :
   function is_posts_page() {
    global $wp_query;

    if($wp_query->is_posts_page)
      return true;

    return false;
    }
endif;
