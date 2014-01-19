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
        $url = WISHPOND_LANDING_PAGES_GET_AUTH_TOKEN_URL;
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
        $url = self::add_url_param( $url, $field, $val );
      }
      $curlConfig = array(
        CURLOPT_URL            => $url,
        CURLOPT_POST           => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 30,
      );
      curl_setopt_array($handler, $curlConfig);
      $result = curl_exec( $handler );
      return $result;
    }

    public static function wishpond_auth_url_with_token( $redirect_to = "")
    {
      $url = WISHPOND_LANDING_PAGES_AUTH_WITH_TOKEN_URL;

      $auth_token = LpWishpondKey::get_auth_token();

      $url = self::add_url_param( $url, "email", LpWishpondStorage::get_admin_email() );
      $url = self::add_url_param( $url, "auth_token", $auth_token );
      $url = self::add_url_param( $url, "redirect_to", $redirect_to );

      $url = self::add_url_param( $url, "wordpress_analytics[utm_campaign]", "WordpressLANDINGPAGES");
      $url = self::add_url_param( $url, "wordpress_analytics[utm_source]", "wordpress.com");
      $url = self::add_url_param( $url, "wordpress_analytics[utm_medium]", "plugin");

      if( LpWishpondStorage::is_first_visit() )
      {
        $url = self::add_url_param( $url, "first_visit", "true" );
        LpWishpondStorage::disable_first_visit();
      }
      return $url;
    }

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
        $url .= "&amp;";
      }

      $url .= urlencode( $param ) . "=" . urlencode( $value );
      return $url;
    }
  }
?>