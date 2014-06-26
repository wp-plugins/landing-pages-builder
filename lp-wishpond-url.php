<?php

/**
* Built with a URL, adds redirect_to to it
*/
class LpWishpondUrl
{
  public function __construct($url = "")
  {
    $this->url = $url;
    $this->params = array();
  }

  public function set_url($url)
  {
    $this->url = $url;
  }

  public function url()
  {
    return self::url_with_params();
  }

  public function add_param( $param, $value )
  {
    if(strtolower( gettype( $redirect_to ) ) == 'object')
    {
      $this->params[$param] = $value->__toString();
    }
    else
    {
      $this->params[$param] = $value;
    }
  }

  public function __toString()
  {
    self::url();
  }

  private function url_with_params()
  {
    if( count($this->params) > 0 && stripos($this->url, "?") === false )
    {
      $query_string = "?";
    }

    foreach( $this->params as $param => $value )
    {
      $query_string .= "&";
      $query_string .= urlencode( $param ) . "=" . urlencode( $value );
    }

    return $this->url . $query_string;
  }
}

?>
