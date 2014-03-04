<?php

  /* Actions */
  /*-----------------------------------------------------------*/
  add_action( 'admin_menu', 'landing_pages_create_menu_pages' );

  /* Callbacks */
  /*-----------------------------------------------------------*/
  function landing_pages_create_menu_pages($param = null)
  {
    add_menu_page(  
        __( 'Landing Pages', LANDING_PAGES_SLUG ),          // The title to be displayed on the corresponding page for this menu  
        __( 'Landing Pages', LANDING_PAGES_SLUG ),                  // The text to be displayed for this actual menu item  
        'administrator',            // Which type of users can see this menu  
        LANDING_PAGES_SLUG . '-landing-pages-dashboard',                  // The unique ID - that is, the slug - for this menu item  
        'landing_pages_dashboard_page_display',// The name of the function to call when rendering the menu for this page  
        plugins_url("assets/images/fb-ads.png", __FILE__),
        '59.5137'
    );
    add_submenu_page(
      LANDING_PAGES_SLUG . "-landing-pages-dashboard",
      __( "Landing Pages", LANDING_PAGES_SLUG ),
      __( "Landing Pages", LANDING_PAGES_SLUG ),
      "administrator",
      LANDING_PAGES_SLUG . "-landing-pages-dashboard",
      "landing_pages_dashboard_page_display"
    );
    add_submenu_page(
      LANDING_PAGES_SLUG . "-landing-pages-dashboard",
      __( "Create a Landing Page", LANDING_PAGES_SLUG ),
     __( "Create a Landing Page", LANDING_PAGES_SLUG ),
      "administrator",
      LANDING_PAGES_SLUG . "-landing-pages-create-",
      "landing_pages_create_landing_page_page_display"
    );
  }

  /* Page Display Functions */
  /*-----------------------------------------------------------*/
  function landing_pages_dashboard_page_display()
  {
    $iframe_url = LpWishpondAuthenticator::wishpond_auth_url_with_token("/central/landing_pages");

    wp_enqueue_style( "LandingPagesMainCss" );
    $html .= '<div class="wrap landing_pages_iframe_holder">';
        $html .= '<iframe id="wishpond_landing_pages_iframe" src="' . $iframe_url . '">
                  </iframe>';
    $html .= '</div>';

    // Send the markup to the browser  
    echo $html;
  }

  function landing_pages_create_landing_page_page_display()
  {
    wp_enqueue_style( "LandingPagesMainCss" );
    $post_id = intval( $_GET["post_id"] );

    $query_info = array();

    $excerpt = LpWishpondHelpers::get_excerpt_by_id( $post_id );

    if( is_int( $post_id ) && $post_id > 0 )
    {
      array_push( $query_info, 
        array(
          "ad_campaign[ad_creative][title]"             => urlencode( substr( get_the_title( $post_id ), 0, 25 ) ),
          "ad_campaign[ad_creative][body]"              => urlencode( $excerpt ),
          "ad_campaign[ad_creative][link_url]"          => urlencode( esc_url( get_permalink( $post_id ) ) ),
          "ad_campaign[ad_creative][destination_type]"  => urlencode( "external_destination" )
        )
      );
    }
    $create_landing_page_page_url = LpWishpondAuthenticator::wishpond_auth_url_with_token("/wizard/start?wizard=wizards%2Flanding_page&".build_query( $query_info ));

    $html .= '<div class="wrap landing_pages_iframe_holder">';
        $html .= '<iframe id="wishpond_landing_pages_iframe" src="' . $create_landing_page_page_url . '">
                  </iframe>';
    $html .= '</div>';

    // Send the markup to the browser  
    echo $html;
  }
?>