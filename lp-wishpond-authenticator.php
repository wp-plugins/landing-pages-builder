<?php

  /**
  * Used to authenticate current wordpress instance, and use url authentication on requrests
  */
  class LpWishpondAuthenticator
  {

    /**
    * Requests a auth token from wishpond based on the $private key and returns it.
    * If $email is specified, also creates an account with that e-mail
    */
    public static function request_auth_token( $master_token )
    {
      if( function_exists('curl_version') )
      {
        //curl_setopt( $handler, CURLOPT_SSL_VERIFYPEER, false );
        $url = new LpWishpondUrl(WISHPOND_LANDING_PAGES_GET_AUTH_TOKEN_URL);
        $postfields = array(
            'master_token' => $master_token,
            'email' => LpWishpondStorage::get_admin_email(),
            "product_type" => "wp_fb_landing_pages"
        );

        $data = self::curl_url( $url, $postfields );

        $result = json_decode( $data );

        /*
          is null whenever:
            - master_token was null
            - an error occurred
            - the master_token was disabled
            - e-mail already in use
            - an invalid e-mail was passed in
        */
        if($result->first_visit == true)
        {
          LpWishpondStorage::set_first_visit();
        }
        return $result->auth_token;
      }
      else
      {
        return false;
      }
    }

    public static function curl_url( $url, $postfields )
    {
      $handler = curl_init();

      foreach($postfields as $field => $val)
      {
        $url->add_param( $field, $val );
      }

      $curlConfig = array(
        CURLOPT_URL            => $url->url(),
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 30,
      );
      curl_setopt_array($handler, $curlConfig);
      $result = curl_exec( $handler );
      return $result;
    }

    /**
    * Used for the old-style authentication
    */
    public static function wishpond_auth_url_with_token( $redirect_to = "" )
    {
      $url = WISHPOND_LANDING_PAGES_AUTH_WITH_TOKEN_URL;

      $auth_token = LpWishpondKey::get_auth_token();

      $url = self::add_url_param( $url, "email", LpWishpondStorage::get_admin_email() );
      $url = self::add_url_param( $url, "auth_token", $auth_token );
      $url = self::add_url_param( $url, "redirect_to", $redirect_to );

      $url = self::add_url_param( $url, "utm_campaign", "Wordpress");
      $url = self::add_url_param( $url, "utm_source", "wordpress.com");
      $url = self::add_url_param( $url, "utm_medium", "Landing Pages");
      $url = self::add_url_param( $url, "referral", "wp_fb_landing_pages");
      $url = self::add_url_param( $url, "wordpress_anticache", LpWishpondHelpers::random_string(10));

      if( LpWishpondStorage::is_first_visit() )
      {
        $url = self::add_url_param( $url, "first_visit", "true" );
        LpWishpondStorage::disable_first_visit();
      }
      return $url;
    }

    /**
    * Picks guest user if no master token was ever used
    */
    public static function wishpond_auth_url( $redirect_to = "" )
    {
      $master_token = LpWishpondStorage::get(WISHPOND_LANDING_PAGES_MASTER_TOKEN);

      // we want new users to use guest_user signup, but still keep token-based signup/signin for old users
      if(LpWishpondStorage::using_token_based_auth() )
      {
        return self::wishpond_auth_url_with_token( $redirect_to );
      }
      else
      {
        return self::wishpond_auth_url_with_guest_user( $redirect_to );
      }
    }

    /**
    * Used for the new style authentication through guest users
    */
    public static function wishpond_auth_url_with_guest_user( $redirect_to_string = "")
    {
      $url   = new LpWishpondUrl();
      if( LpWishpondStorage::is_guest_signup_enabled() )
      {
        $url->set_url( WISHPOND_GUEST_SIGNUP_URL );
        $url->add_param( "guest_signup", "true" );
        $url->add_param( "show_site_menu", "true" );
      }
      else
      {
        $url->set_url( WISHPOND_LOGIN_URL );
      }

      // $read_only_token = LpWishpondStorage::find_or_create_read_token();

      $url->add_param( "email", LpWishpondStorage::get_admin_email() );

      $redirect_to = new LpWishpondUrl( $redirect_to_string );

      $redirect_to->add_param( "utm_campaign", "Wordpress" );
      $redirect_to->add_param( "utm_source", "wordpress.com" );
      $redirect_to->add_param( "utm_medium", "Landing Pages Builder" );
      $redirect_to->add_param( "wordpress_plugin_parent_url", self::current_page_url() );
      $redirect_to->add_param( "wordpress_plugin_host", self::current_page_host() );
      $redirect_to->add_param( "wordpress_plugin_version", WISHPOND_LANDING_PAGES_BUILDER_VERSION );

      $url->add_param( "redirect_to", $redirect_to->url() );
      $url->add_param( "utm_campaign", "Wordpress" );
      $url->add_param( "utm_source", "wordpress.com" );
      $url->add_param( "utm_medium", "Landing Pages" );
      $url->add_param( "referral", "wp_fb_landing_pages" );
      $url->add_param( "wordpress_anticache", LpWishpondHelpers::random_string(10) );

      if( LpWishpondStorage::is_first_visit() )
      {
        $url->add_param( "first_visit", "true" );
        LpWishpondStorage::disable_first_visit();
      }

      return $url->url();
    }

    public static function current_page_host() {
      return site_url('', $_SERVER["HTTPS"]);
    }

    public static function current_page_url() {
      if ($_SERVER["SERVER_PORT"] != "80") {
        $page_url .= $server_name.":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
      } else {
        $page_url .= self::current_page_host().$_SERVER["REQUEST_URI"];
      }

      return $page_url;
    }

    public static function sample_host_at() {
      return self::current_page_host()."/new-page";
    }

    /**
    * DEPRECATED: used only by token-based authentication
    */
    public static function add_url_param( $url, $param, $value )
    {
      $position_of_question_mark = strpos( $url, "?" );

      // no question mark in url
      if( $position_of_question_mark == false )
      {
        $url .= "?";
      }
      // question mark not at end of url => some params already sent
      else if ( $position_of_question_mark < strlen( $url ) - 1 )
      {
        $url .= "&";
      }

      $url .= urlencode( $param ) . "=" . urlencode( $value );
      return $url;
    }
  }
?>