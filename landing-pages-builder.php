<?php
  /**
   * Plugin Name: Landing Pages Builder
   * Plugin URI: http://corp.wishpond.com/landing-page-builder/
   * Description: Create amazing landing pages from your wordpress site and host them anywhere. Monitor analytics and improve conversion rates and much more.
   * Version: 1.4.4
   * Author: Wishpond
   * Text Domain: landing-pages-builder
   * Author URI: http://corp.wishpond.com
   * License: GNU General Public License version 2.0 (GPL-2.0)
   */

  /*  Copyright 2014 Wishpond  ( email : support@wishpond.com )

      This program is free software; you can redistribute it and/or modify
      it under the terms of the GNU General Public License, version 2, as 
      published by the Free Software Foundation.

      This program is distributed in the hope that it will be useful,
      but WITHOUT ANY WARRANTY; without even the implied warranty of
      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
      GNU General Public License for more details.

      You should have received a copy of the GNU General Public License
      along with this program; if not, write to the Free Software
      Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
  */

  //debug
  if ( ! defined( 'WISHPOND_SITE_URL' ) )
  {
    define( 'WISHPOND_SITE_URL' , 'http://www.lvh.me' );
  }

  if ( ! defined( 'WISHPOND_SECURE_SITE_URL' ) )
  {
    define( 'WISHPOND_SECURE_SITE_URL' , 'https://www.lvh.me' );
  }
  if ( ! defined( 'LANDING_PAGES_SLUG' ) )
  {
    define( 'LANDING_PAGES_SLUG' , 'wishpond-landing-pages' );
  }
  if ( ! defined( 'WISHPOND_LANDING_PAGES_BUILDER_VERSION' ) )
  {
    define( 'WISHPOND_LANDING_PAGES_BUILDER_VERSION' , '1.4.4' );
  }

  $plugin_constants = array(
    // Wishpond Globals
    'WISHPOND_SIGNUP_URL'       => WISHPOND_SECURE_SITE_URL . "/central/merchant_signups/new/",
    'WISHPOND_GUEST_SIGNUP_URL' => WISHPOND_SECURE_SITE_URL . "/central/merchant_signups/new/",
    'WISHPOND_LOGIN_URL'        => WISHPOND_SECURE_SITE_URL . "/login",
    'WISHPOND_LANDING_PAGES_GET_AUTH_TOKEN_URL'   => WISHPOND_SECURE_SITE_URL.'/central/sessions/get_wordpress_auth_token',
    'WISHPOND_LANDING_PAGES_AUTH_WITH_TOKEN_URL'  => WISHPOND_SECURE_SITE_URL . "/central/sessions/auth_with_wordpress",

    // Landing Pages Builder
    'LANDING_PAGES_DIR'           => plugin_dir_path( __FILE__ ),
    'LANDING_PAGES_ADMIN_EMAIL'   => LANDING_PAGES_SLUG."-admin-email",
    'LANDING_PAGES_FIRST_VISIT'   => LANDING_PAGES_SLUG."-first-visit",
    'DISABLE_GUEST_SIGNUP_OPTION' => LANDING_PAGES_SLUG."-guest-signup",

    // token-based authentication
    'WISHPOND_LANDING_PAGES_MASTER_TOKEN'       => LANDING_PAGES_SLUG.'_master_token',
    'WISHPOND_LANDING_PAGES_AUTH_TOKEN'         => LANDING_PAGES_SLUG.'_auth_token',
    'WISHPOND_LANDING_PAGES_AUTH_TOKEN_EXPIRY'  => LANDING_PAGES_SLUG.'_auth_token_expiry',
    'WISHPOND_LANDING_PAGES_AUTH_TOKEN_TTL'     => 300,

    'WISHPOND_FACEBOOK_APP_ID'                  => "515720611858523"

  );

  foreach( $plugin_constants as $name => $value)
  {
    if ( ! defined( $name ) )
    {
      define( $name, $value );
    }
  }

  /*
  * List & Load plugin files
  */
  $WISHPOND_LANDING_PAGES_PLUGIN_FILES = array(
    "storage/lp-wishpond-storage.php",
    "storage/lp-wishpond-landing-page.php",
    "lp-wishpond-helpers.php",
    "lp-wishpond-key.php",
    "lp-wishpond-authenticator.php",
    "lp-create-menu.php",
    "lp-wishpond-iframe.php",
    "lp-shortcodes.php",
    "lp-wishpond-url.php",
    "lp-wishpond-templater.php"
  );

  foreach( $WISHPOND_LANDING_PAGES_PLUGIN_FILES as $file )
  {
    load_wishpond_landing_pages_file( $file );
  }

  function load_wishpond_landing_pages_file( $file )
  {
    include_once LANDING_PAGES_DIR . $file;
  }

  /*************************
  * Register Activation Hook
  **************************/
  register_activation_hook(__FILE__, 'landing_pages_builder_activate');
  add_action('admin_init', 'landing_pages_builder_redirect');

  function landing_pages_builder_activate() {
    add_option('landing_pages_builder_do_activation_redirect', true);
  }

  function landing_pages_builder_redirect() {
    if ( get_option( 'landing_pages_builder_do_activation_redirect', false ) ) {
      $url = admin_url( "admin.php" )."?page=".LANDING_PAGES_SLUG."-landing-pages-dashboard";
      delete_option( 'landing_pages_builder_do_activation_redirect' );
      exit( wp_redirect( $url ) );
    }
  }

?>