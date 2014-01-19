<?php
  /**
   * Plugin Name: Landing Pages Builder
   * Plugin URI: http://corp.wishpond.com/landing-page-builder/
   * Description: Easily create and monitor landing pages for your wordpress site. Improve conversion rates, get new customers and manage all your landing pages in one place.
   * Version: 1.0
   * Author: Wishpond
   * Text Domain: wordpress-landing-pages-builder
   * Author URI: http://wishpond.com
   * License: GNU General Public License version 2.0 (GPL-2.0)
   */

  /*  Copyright 2013 Wishpond  ( email : support@wishpond.com )

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

  /*
  * Wishpond Globals
  */
  define('WISHPOND_SITE_URL', 'http://localhost:3000/');
  if ( ! defined( 'WISHPOND_SITE_URL' ) )
  {
    define('WISHPOND_SITE_URL', 'http://localhost:3000/');
  }

  if ( ! defined( 'WISHPOND_SIGNUP_URL' ) )
  {
    define('WISHPOND_SIGNUP_URL', WISHPOND_SITE_URL . "/central/merchant_signups/new/");
  }

  # Used for authenticating every request, and redirecting to the proper location on central
  if ( ! defined( 'WISHPOND_LANDING_PAGES_AUTH_WITH_TOKEN_URL' ) )
  {
    define('WISHPOND_LANDING_PAGES_AUTH_WITH_TOKEN_URL', WISHPOND_SITE_URL . "/central/sessions/auth_with_wordpress");
  }
  if ( ! defined( 'WISHPOND_LANDING_PAGES_GET_AUTH_TOKEN_URL' ) )
  {
    define('WISHPOND_LANDING_PAGES_GET_AUTH_TOKEN_URL', 'http://10.0.2.2:3000/central/sessions/get_wordpress_auth_token');
  }

  /*
  * Wishpond Ads
  */
  if ( ! defined( 'LANDING_PAGES_DIR' ) )
  {
    define( 'LANDING_PAGES_DIR', plugin_dir_path( __FILE__ ) );
  }
  if ( ! defined( 'LANDING_PAGES_SLUG' ) )
  {
    define( 'LANDING_PAGES_SLUG', "wishpond-landing-pages" );
  }
  if ( ! defined( 'LANDING_PAGES_ADMIN_EMAIL' ) )
  {
    define( 'LANDING_PAGES_ADMIN_EMAIL', LANDING_PAGES_SLUG."-admin-email" );
  }
  if ( ! defined( 'LANDING_PAGES_FIRST_VISIT' ) )
  {
    define( 'LANDING_PAGES_FIRST_VISIT', LANDING_PAGES_SLUG."-first-visit" );
  }

  /*
  * Authentication Keys
  */
  if ( ! defined( 'WISHPOND_LANDING_PAGES_MASTER_TOKEN' ) )
  {
    define('WISHPOND_LANDING_PAGES_MASTER_TOKEN', 'wishpond_landing_pages_master_token');
  }
  if ( ! defined( 'WISHPOND_LANDING_PAGES_AUTH_TOKEN' ) )
  {
    define('WISHPOND_LANDING_PAGES_AUTH_TOKEN', 'wishpond_landing_pages_auth_token');
  }
  if ( ! defined( 'WISHPOND_LANDING_PAGES_AUTH_TOKEN_EXPIRY' ) )
  {
    define('WISHPOND_LANDING_PAGES_AUTH_TOKEN_EXPIRY', 'wishpond_landing_pages_auth_token_expiry');
  }
  if ( ! defined( 'WISHPOND_LANDING_PAGES_AUTH_TOKEN_TTL' ) )
  {
    define( 'WISHPOND_LANDING_PAGES_AUTH_TOKEN_TTL', 300 ); // 5 minutes time to live - ttl on server = around 7 minutes
  }

  /*
  * List & Load plugin files
  */
  $WISHPOND_LANDING_PAGES_PLUGIN_FILES = array(
    "lp-wishpond-storage.php",
    "lp-wishpond-helpers.php",
    "lp-wishpond-key.php",
    "lp-wishpond-authenticator.php",
    "lp-register-assets.php",
    "lp-menu.php",
    "lp-shortcodes.php"
  );

  foreach( $WISHPOND_LANDING_PAGES_PLUGIN_FILES as $file )
  {
    load_wishpond_landing_pages_file( $file );
  }

  function load_wishpond_landing_pages_file( $file )
  {
    include_once LANDING_PAGES_DIR . $file;
  }
?>