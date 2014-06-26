<?php

// Used to display iframes in the menu, with necessary authentication
class LpWishpondIframe
{
  public function __construct( $wishpond_url, $query_info = array() )
  {
    $this->wishpond_url  = $wishpond_url;
    $this->url = LpWishpondAuthenticator::wishpond_auth_url($wishpond_url)."&".build_query( $query_info );
  }

  public function display_iframe()
  {
    $html .= '<div class="wrap landing_pages_iframe_holder">';
        $html .= '<iframe id="wishpond_landing_pages_iframe" src="' . $this->url . '">
                  </iframe>';
    $html .= '</div>';
    self::output_html($html);
  }

  // Send the markup to the browser
  public function output_html($html)
  {
    if( !LpWishpondStorage::using_token_based_auth() )
    {
      $html .= self::guest_status_iframe_html();
    }
    echo $html;
  }

  public function guest_status_iframe_html()
  {
    return "<iframe id='wishpond_guest_status_iframe' style='display:block; height:0; width:0; margin:0; border:0;'></iframe>";
  }
}
?>
