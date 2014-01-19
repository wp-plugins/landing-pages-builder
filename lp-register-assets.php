<?php
  /* Actions */
  /*-----------------------------------------------------------*/
  add_action('admin_init', 'landing_pages_init');


  /* Callbacks */
  /*-----------------------------------------------------------*/
  function landing_pages_init() {
    wp_register_style("LandingPagesMainCss", plugins_url("assets/css/landing-pages-main.css", __FILE__));
  }
?>