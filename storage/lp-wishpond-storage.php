<?php
  /**
   * Used to store information and extract information from wordpress
   */
  class LpWishpondStorage
  {
    // ---------------------------------------------------------------
    // Primary Option Editing
    // ---------------------------------------------------------------
    public static function add( $name, $value, $autoload = 'no' )
    {
      add_option( $name, $value, '', $autoload );
    }

    public static function get( $name, $default = false )
    {
      return get_option( $name, $default );
    }

    public static function delete( $name )
    {
      delete_option( $name );
    }

    public static function get_admin_email()
    {
      return LpWishpondStorage::get( 'admin_email' );
    }

    // ---------------------------------------------------------------
    // First visit & Token based auth
    // ---------------------------------------------------------------
    public static function set_first_visit()
    {
      self::disable_first_visit();
      add_option( LANDING_PAGES_FIRST_VISIT, true );
    }

    public static function is_first_visit()
    {
      return get_option( LANDING_PAGES_FIRST_VISIT );
    }

    public static function disable_first_visit()
    {
      delete_option( LANDING_PAGES_FIRST_VISIT );
    }

    public static function using_token_based_auth()
    {
      return LpWishpondStorage::get( WISHPOND_LANDING_PAGES_MASTER_TOKEN ) !== false;
    }

    // ---------------------------------------------------------------
    // Guest Signup
    // ---------------------------------------------------------------
    public static function enable_guest_signup()
    {
      delete_option( DISABLE_GUEST_SIGNUP_OPTION ); 
    }

    public static function disable_guest_signup()
    {
      delete_option( DISABLE_GUEST_SIGNUP_OPTION );
      add_option( DISABLE_GUEST_SIGNUP_OPTION, true );
    }

    public static function is_guest_signup_enabled()
    {
      return !get_option( DISABLE_GUEST_SIGNUP_OPTION );
    }
    // ---------------------------------------------------------------

    // ---------------------------------------------------------------
    // Settings
    // ---------------------------------------------------------------
    public static function permalink_structure_valid() {
      if (strpos(LpWishpondStorage::get("permalink_structure"), "postname") !== FALSE) {
        return true;
      }
      return false;
    }
    // ---------------------------------------------------------------
  }
?>