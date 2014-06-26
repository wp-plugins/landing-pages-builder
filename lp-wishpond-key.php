<?php
  /**
  * Singleton model ( static ) that deals with Wishpond authentication keys
  * TODO: make threadsafe; if get_auth_token is called from two different pages, after activation... Very unlikely though
  */
  class LpWishpondKey
  {

    /**
    * Used for memoization: when using a variable multiple times on the same page
    */
    private static $auth_token = '';

    private static $master_token = '';

    private static $auth_token_expiry = '';

    /*
    * Returns the auth token stored on this wordpress instance;
    * if it expired or was not found, returns false and/or deletes it
    */
    public static function get_auth_token( $refresh_if_expired = true )
    {
      // Memoization
      if( self::auth_token() != '' && !self::auth_token_expired() )
      {
        return self::auth_token();
      }

      // get_master_token is called before getting the auth token, because if a private key needs to be created, a auth key will also be retrieved on the same call
      $master_token        = self::get_master_token();
      $auth_token         = self::auth_token();

      // if expired or not available; get_master_token implicitly sets the auth_token so it should never actually be false in here, unless the server stops in the middle of an operation or something like that
      if( $auth_token == false || self::auth_token_expired() )
      {
        if( $refresh_if_expired )
        {
          $auth_token = self::create_auth_token();
        }
        else
        {
          $auth_token = self::auth_token( false );
        }
      }

      return $auth_token;
    }

    /*
    * Retrieves auth token from server. A auth token needs a private key, so we might create the private key as well
    * 
    */
    public static function create_auth_token()
    {
      $master_token = LpWishpondKey::get_master_token();

      $new_auth_token = LpWishpondAuthenticator::request_auth_token( $master_token );

      self::auth_token( $new_auth_token );

      if( $new_auth_token != false && $new_auth_token != '' )
      {
        self::auth_token_expiry( (int)time() + (int)WISHPOND_LANDING_PAGES_AUTH_TOKEN_TTL );
      }

      return $new_auth_token;
    }

    public static function auth_token_expired()
    {
      return time() > (int)self::auth_token_expiry();
    }

    /*
    * Returns the private key stored on this wordpress instance
    */
    public static function get_master_token( $create_remote_account = true )
    {
      $master_token = self::master_token();

      if( $master_token == false && $create_remote_account )
      {
        $master_token = self::create_master_token();
      }

      return $master_token;
    }

    public static function has_master_token()
    {
      return self::master_token() != false;
    }

    /**
    * Creates private key, stores it and returns it locally, and also makes a server call to create an account on wishpond that uses this private key
    * The private key should be in sync with the Wishpond account. No account = no key
    *
    * If $email is set, resets the private key and uses the new e-mail
    */
    public static function create_master_token( $email = "" )
    {
      $master_token = self::random_key();

      $auth_token = LpWishpondAuthenticator::request_auth_token( $master_token );

      // Only store the keys if the call was successful, otherwise set them to false & setters will auto-delete them
      if( $auth_token == false )
      {
        $master_token = false;
      }

      self::master_token( $master_token );
      self::auth_token( $auth_token );

      return $master_token;
    }

    /*
    * Used as getter and setter for the db auth token
    */
    public static function auth_token( $auth_token = "" )
    {
      if( $auth_token == "" && $auth_token !== false )
      {
        // getter
        if( self::$auth_token == '' )
        {
          self::$auth_token = LpWishpondStorage::get( WISHPOND_LANDING_PAGES_AUTH_TOKEN );
        }
      }
      else
      {
        // setter
        LpWishpondStorage::delete( WISHPOND_LANDING_PAGES_AUTH_TOKEN );
        if( $auth_token != false )
        {
          LpWishpondStorage::add( WISHPOND_LANDING_PAGES_AUTH_TOKEN, $auth_token );
        }
        self::$auth_token = $auth_token;
      }
      return self::$auth_token;
    }

    public static function auth_token_expiry( $auth_token_expiry = "" )
    {
      if( $auth_token_expiry == "" && $auth_token_expiry !== false)
      {
        // getter
        if( self::$auth_token_expiry == '' )
        {
          self::$auth_token_expiry = LpWishpondStorage::get( WISHPOND_LANDING_PAGES_AUTH_TOKEN_EXPIRY );
        }
      }
      else
      {
        LpWishpondStorage::delete( WISHPOND_LANDING_PAGES_AUTH_TOKEN_EXPIRY );
        if( $auth_token_expiry != false )
        {
          LpWishpondStorage::add( WISHPOND_LANDING_PAGES_AUTH_TOKEN_EXPIRY, $auth_token_expiry );
        }
        self::$auth_token_expiry = $auth_token_expiry;
      }
      return self::$auth_token_expiry;
    }

    /*
    * Used as getter and setter for the db private key & memoization
    */
    public static function master_token( $master_token = "" )
    {
      if( $master_token == "" && $master_token !== false )
      {
        // getter
        if( self::$master_token == '' )
        {
          self::$master_token = LpWishpondStorage::get( WISHPOND_LANDING_PAGES_MASTER_TOKEN );
        }
      }
      else
      {
        // setter
        LpWishpondStorage::delete( WISHPOND_LANDING_PAGES_MASTER_TOKEN );
        if( $master_token != false )
        {
          LpWishpondStorage::add( WISHPOND_LANDING_PAGES_MASTER_TOKEN, $master_token );
        }
        self::$master_token = $master_token;
      }
      return self::$master_token;
    }

    /* Gets a randomly generated string that can be used as a user key */
    private static function random_key()
    {
      $hashed_string  = urlencode( php_uname( "n" ) );
      $hashed_string .= site_url();
      $hashed_string .= LpWishpondHelpers::random_string( 64 );
      $hashed_string .= microtime();
      $key = hash( 'sha512', $hashed_string );
      return $key;
    }
  }
?>