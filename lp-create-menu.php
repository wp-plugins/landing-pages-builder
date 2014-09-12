<?php

class LpMenuBuilder
{
  public function __construct()
  {
    add_action( 'admin_menu', array( $this, 'create_menu_pages' ) );
    add_action( 'wp_ajax_wishpond_api', array( $this, 'wishpond_api' ) );
    add_action( 'init', array( $this, 'add_cors_headers' ) );
  }

  /**
   * Returns an instance of this class. 
   */
  public static function get_instance() {
    if( null == self::$instance ) {
            self::$instance = new PageTemplater();
    }
    return self::$instance;
  }

  public function create_menu_pages()
  {
    add_menu_page(  
        __( 'Landing Pages', LANDING_PAGES_SLUG ),          // The title to be displayed on the corresponding page for this menu  
        __( 'Landing Pages', LANDING_PAGES_SLUG ),                  // The text to be displayed for this actual menu item  
        'administrator',            // Which type of users can see this menu  
        LANDING_PAGES_SLUG . '-landing-pages',                  // The unique ID - that is, the slug - for this menu item  
        array( $this, 'display_landing_pages' ),// The name of the function to call when rendering the menu for this page  
        plugins_url("assets/images/landing-pages.png", __FILE__),
        '59.5137'
    );
    add_submenu_page(
      LANDING_PAGES_SLUG . "-landing-pages",
      __( "Landing Pages", LANDING_PAGES_SLUG ),
      __( "Landing Pages", LANDING_PAGES_SLUG ),
      "administrator",
      LANDING_PAGES_SLUG . "-landing-pages",
      array( $this, 'display_landing_pages' )
    );
    add_submenu_page(
      LANDING_PAGES_SLUG . "-landing-pages",
      __( "Add New", LANDING_PAGES_SLUG ),
      __( "Add New", LANDING_PAGES_SLUG ),
      "administrator",
      LANDING_PAGES_SLUG . "-landing-pages-create",
      array( $this, 'display_create_landing_page' )
    );
    add_submenu_page(
      LANDING_PAGES_SLUG . "-landing-pages",
      __( "Dashboard", LANDING_PAGES_SLUG ),
      __( "Dashboard", LANDING_PAGES_SLUG ),
      "administrator",
      LANDING_PAGES_SLUG . "-landing-pages-dashboard",
      array( $this, 'display_dashboard' )
    );
    add_submenu_page(
      LANDING_PAGES_SLUG . "-landing-pages",
      __( "Settings", LANDING_PAGES_SLUG ),
      __( "Settings", LANDING_PAGES_SLUG ),
      "administrator",
      LANDING_PAGES_SLUG . "-landing-pages-settings",
      array( $this, 'display_settings_page' )
    );
  }

  public function display_landing_pages()
  {
    wp_register_style(
      "landing_pages_list_css",
      plugins_url("assets/css/landing-pages-list.css", __FILE__)
    );
    wp_enqueue_style( "landing_pages_list_css" );

    $wishpond_action  = preg_replace('/[^a-zA-Z\-_]+/i', "", $_GET["wishpond-action"]);
    $wishpond_id      = preg_replace('/[^0-9]+/i', "", $_GET["wishpond-id"]);

    if($wishpond_action != "") {
      if(!LpWishpondStorage::permalink_structure_valid()) {
        $notice = 'Invalid permalink structure. Please go to "Settings"->"Permalinks" and make sure your permalinks use the "postname"<br/> Otherwise, you have to manually paste the wishpond "Embed Code" into your homepage';
      }
      else {
        switch($wishpond_action)
        {
          case "make-homepage": {
            update_option( 'page_on_front', LpWishpondLandingPage::get_by_wishpond_id($wishpond_id)->wordpress_post_id );
            update_option( 'show_on_front', 'page' );
            $notice = "Your Landing Page is now your homepage. <a href='".LpWishpondLandingPage::get_by_wishpond_id($wishpond_id)->url()."' target='_blank'>View Page</a>";
            break;
          }
          case "reset-homepage": {
            update_option( 'page_on_front', '' );
            update_option( 'show_on_front', '' );
            $notice = "Home page reset successfully";
            break;
          }
        }
      }
    }
    include_once LANDING_PAGES_DIR . "views/landingpages.php";
  }

  public function display_dashboard()
  {
    $wishpond_action  = preg_replace('/[^a-zA-Z]+/i', "", $_GET["wishpond-action"]);
    $wishpond_id      = preg_replace('/[^0-9]+/i', "", $_GET["wishpond-id"]);
    $wishpond_marketing_id  = preg_replace('/[^0-9]+/i', "", $_GET["wishpond-marketing-id"]);

    switch($wishpond_action)
    {
      case "edit": {
        $redirect_url = "/wizard/start?contest_type=landing_page&participation_type=landing_page&wizard=wizards%2Flanding_page&social_campaign_id=".$wishpond_id;
        break;
      }
      case "manage": {
        $redirect_url = "/central/marketing_campaigns/".$wishpond_marketing_id;
        break;
      }
      case "report": {
        $redirect_url = "/central/landing_pages/".$wishpond_id;
        break;
      }
      default: {
        $redirect_url = "/central/landing_pages";
        break;
      }
    }

    self::enqueue_scripts();
    $dashboard_page = new LpWishpondIframe($redirect_url);
    $dashboard_page->display_iframe(); 
  }

  public function display_create_landing_page()
  {
    self::enqueue_scripts();
    $dashboard_page = new LpWishpondIframe( "/wizard/start?participation_type=landing_page&wizard=wizards%2Flanding_page", self::query_info_from_post_id() );
    $dashboard_page->display_iframe();
  }

  function display_settings_page()
  {
    self::enqueue_scripts();

    $post_error = "";
    if( $_POST["submit"] )
    {
      if( !$_POST["enable_automatic_authentication"] )
      {
        LpWishpondStorage::delete(WISHPOND_LANDING_PAGES_MASTER_TOKEN);
        LpWishpondStorage::delete(WISHPOND_LANDING_PAGES_AUTH_TOKEN);
      }
      else if($_POST["enable_guest_signup"])
      {
        $post_error = "Please disable Automatic Authentication to use Guest Signups";
      }
      else
      {
        $post_error = "Automatic authentication is a deprecated feature and can't be re-enabled"; 
      }

      if( !$_POST["enable_automatic_authentication"] )
      {
        if( $_POST["enable_guest_signup"] )
        {
          LpWishpondStorage::enable_guest_signup();
          $notice = "Guest signup enabled!";
        }
        else
        {
          LpWishpondStorage::disable_guest_signup();
          $notice = "Guest signup disabled!";
        }
      }
    }
    include_once LANDING_PAGES_DIR . 'views/settings.php';
  }

  public function query_info_from_post_id()
  {
    $post_id = intval( $_GET["post_id"] );
    $excerpt = LpWishpondHelpers::get_excerpt_by_id( $post_id );

    $query_info = array();

    if( is_int( $post_id ) && $post_id > 0 )
    {
      $query_info = array(
        "ad_campaign[ad_creative][title]"             => substr( get_the_title( $post_id ), 0, 25 ),
        "ad_campaign[ad_creative][body]"              => $excerpt,
        "ad_campaign[ad_creative][link_url]"          => esc_url( get_permalink( $post_id ) ),
        "ad_campaign[ad_creative][destination_type]"  => "external_destination"
      );
    }
    return $query_info;
  }

  public function wishpond_api()
  {
    if (is_user_logged_in())
    {
      $nonce = $_POST['nonce'];
      $data = $_POST['data'];

      if ( ! wp_verify_nonce( $nonce, 'wishpond-api-nonce' ) ) {
        die ( 'Insufficient Access!');
      }

      /*
      * Only allow this if current user has enough access to modify plugins
      */
      if ( current_user_can( 'activate_plugins' ) )
      {
        $return_message = "";
        $path_start   = strrpos($data["options"]["wordpress_path"], "/");
        $path         = substr($data["options"]["wordpress_path"], $path_start);
        $url          = $data["options"]["wordpress_url"];

        switch($data['endpoint']) {
          case "disable_guest_signup": {
            LpWishpondStorage::disable_guest_signup();
            break;
          }
          case "check_path_availability": {
            $wishpond_id  = preg_replace('/[^0-9]+/i', "", $data["options"]["social_campaign_id"]);
            $existing_post = LpWishpondLandingPage::get_by_wishpond_id($wishpond_id);

            // hosting as home page ?
            if($path == "") {
              $return_message = LpWishpondHelpers::json_message('error', 'The path/slug was empty. Please use a url like "http://domain.com/path" to host your page. To set a landing page as your homepage, just go into "Landing Pages", hover over your landing page and click on "Make Homepage"');
            }
            else if(filter_var($url, FILTER_VALIDATE_URL) === false) {
              $return_message = LpWishpondHelpers::json_message('error', 'URL Invalid; please ensure no invalid characters or spaces were used in the URL. Also make sure http:// or https:// are included in the URL.');
            }
            else if(!LpWishpondStorage::permalink_structure_valid()) {
              $return_message = LpWishpondHelpers::json_message('error',
                'Invalid permalink structure. If you want to automatically publish to wordpress, please go in "Settings"->"Permalinks" and make sure your permalinks use the "postname"<br/> Otherwise, you have to manually create a wordpress page at this path and paste the wishpond "Embed Code"');
            }
            else {
              if( LpWishpondLandingPage::page_slug_used($path, $existing_post->wordpress_post_id) ) {
                $return_message = LpWishpondHelpers::json_message('error', 'Oops! The specified path \''.$path.'\' appears to be in use');
              }
              else
              {
                $return_message = LpWishpondHelpers::json_message('updated', 'The specified path seems to be available!');
              }
            }
            break;
          }

          case "publish_campaign": {
            $wishpond_marketing_id = preg_replace('/[^0-9]+/i', "", $data["options"]["marketing_campaign_id"]);
            $wishpond_id      = preg_replace('/[^0-9]+/i', "", $data["options"]["social_campaign_id"]);

            $page_title       = html_entity_decode($data["options"]["social_campaign_title"], ENT_QUOTES);
            $page_title       = preg_replace('/[^a-zA-Z\-\_0-9\s\(\)\[\]\{\}\"\'\"]+/i', "", $page_title);

            $page_description = $data["options"]["social_campaign_description"];
            $page_description = preg_replace('/[^a-zA-Z\-\_0-9\s\(\)\[\]\{\}\"\'\"]+/i', "", $page_description);
            $page_image_url   = $data["options"]["social_campaign_image_url"];
            $facebook_app_id  = $data["options"]["facebook_app_id"];

            $existing_post = LpWishpondLandingPage::get_by_wishpond_id($wishpond_id);

            if(strlen($path) == 0)
            {
              $return_message = LpWishpondHelpers::json_message('error', 'The path/slug was empty. Please use a url like "http://domain.com/path" to host your page. To set a landing page as your homepage, just go into "Landing Pages" and click on "Make Homepage"');
            }
            else if( LpWishpondLandingPage::page_slug_used($path, $existing_post->wordpress_post_id) ) {
              $return_message = LpWishpondHelpers::json_message('error', 'Duplicate path/slug \'' . $path . '\'. Please try a different path ?');
            }
            else if(!LpWishpondStorage::permalink_structure_valid()) {
              $return_message = LpWishpondHelpers::json_message('error',
                'Invalid permalink structure. To Automatically publish to wordpress, you need to go in "Settings"->"Permalinks" and make sure your permalinks use the "postname"<br/> Otherwise, just use the wishpond Embed code found under "Add to Website"');
            }
            else
            {
              if($existing_post)
              {
                $existing_post->update_values(array(
                  "path"      => $path,
                  "title"     => $page_title,
                  "image_url" => $page_image_url,
                  "wishpond_marketing_id" => $wishpond_marketing_id,
                  "description"           => $page_description,
                  "facebook_app_id"       => $facebook_app_id
                ));
                $existing_post->save();
                $return_message = LpWishpondHelpers::json_message('updated',
                  'Landing Page Successfully updated! &nbsp;&nbsp;&nbsp; <a class="btn" target="_blank" href="'.$existing_post->url().'">View Page</a>'); 
              }
              else
              {
                $new_landing_page = new LpWishpondLandingPage(array(
                  "path"                  => $path,
                  "wishpond_marketing_id" => $wishpond_marketing_id,
                  "wishpond_id"           => $wishpond_id,
                  "title"                 => $page_title,
                  "description"           => $page_description,
                  "image_url"             => $page_image_url,
                  "facebook_app_id"       => $facebook_app_id
                ));
                $new_landing_page->save();
                if($new_landing_page->wordpress_post_id >= 0)
                {
                  $return_message = LpWishpondHelpers::json_message('updated', 'Landing Page Successfully published! &nbsp;&nbsp;&nbsp; <a class="btn" target="_blank" href="'.$new_landing_page->url().'">View Page</a>'); 
                }
                else if (get_page_by_title($page_title) != NULL) {
                  $return_message = LpWishpondHelpers::json_message('error','Duplicate title! Wordpress needs page titles to be unique, so please change the Landing Page title, and publish the page again');
                }
                else
                {
                  $return_message = LpWishpondHelpers::json_message('error','Unknown error occurred! Your landing page could not be created. Maybe try a different slug ? Contact us at 1-800-921-0167 if the problem persists');
                }
              }
            }
            break;
          }
          case "delete_campaign": {
            $wishpond_id  = preg_replace('/[^0-9]+/i', "", $data["options"]["social_campaign_id"]);
            wp_delete_post(LpWishpondLandingPage::get_by_wishpond_id($wishpond_id)->wordpress_post_id);
            $return_message = LpWishpondHelpers::json_message('updated', 'Landing Page deleted successfully.'); 
            break;
          }
        }
        echo $return_message;
      }
    }
    exit();
  }

  public function add_cors_headers()
  {
    header("Origin: ".WISHPOND_SITE_URL.", ".WISHPOND_SECURE_SITE_URL);
    header("Access-Control-Allow-Origin: ".WISHPOND_SITE_URL.", ".WISHPOND_SECURE_SITE_URL);
    header("Access-Control-Allow-Headers: "."origin, x-requested-with, content-type");
    header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
  }

  public function enqueue_scripts()
  {
    wp_register_style(
      "landing_pages_main_css",
      plugins_url("assets/css/landing-pages-main.css", __FILE__)
    );

    wp_enqueue_style( "landing_pages_main_css" );
    wp_enqueue_script( 'json2' );

    $plugin_scripts = array();

    $plugin_scripts["landing_pages_cross_domain_js"] = array(
      "url"           => plugins_url("assets/js/xd.js", __FILE__),
      "dependencies"  => array( 'jquery' ),
      "in_footer"     => true
    );

    $plugin_scripts["landing_pages_wishpond_api_script_js"] = array(
      "url"           => plugins_url("assets/js/wishpond-api.js", __FILE__),
      "dependencies"  => array( 'jquery' ),
      "in_footer"     => true,
      "localize"      => true,
      "localize_variable" => "JS",
      "localize_options"  => 
        array(
          // use wp-admin/admin-ajax.php to process the request
          'ajaxurl'           => admin_url( 'admin-ajax.php' ),
          'global_nonce' => wp_create_nonce( 'wishpond-api-nonce' ),
          'WISHPOND_SITE_URL' => WISHPOND_SITE_URL,
          'WISHPOND_SECURE_SITE_URL' => WISHPOND_SECURE_SITE_URL,
          'is_guest_signup_enabled' => LpWishpondStorage::is_guest_signup_enabled()
        )
    );

    foreach( $plugin_scripts as $name => $options)
    {
      wp_register_script(
        $name,
        $options["url"],
        $options["dependencies"],
        $options["in_footer"]
      );
      wp_enqueue_script( $name );
      if( $options["localize"] )
      {
        wp_localize_script(
          $name,
          $options["localize_variable"],
          $options["localize_options"]
        );
      }
    }
  }
  //------------------------------------------------
}

$menu_builder = new LpMenuBuilder();
?>