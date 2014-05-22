<?php
  class LpWishpondHelpers
  {
    /* Gets a randomly generated string */
    public static function random_string( $length = 16 )
    {
      list( $usec, $sec ) = explode( ' ', microtime() );
      mt_srand( ( float ) $sec + ( (float ) $usec * 100000 ) );
      $chars ="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890!@#$%^&*():;{}[]|+=-_<>?/~`";//length:89
      return self::random_string_block( $chars, $length );
    }

    public static function safe_random_string( $length = 16 )
    {
      $random_string = self::random_string( $length ) . urlencode( php_uname( "n" ) );
      $random_string = hash( 'sha512', $random_string );
      return self::random_string_block( $random_string, $length );
    }

    public static function random_string_block( $string, $length)
    {
      $block = "";
      for( $i=0;$i<$length; $i++ )
      {
          $block .= $string[ mt_rand( 0,strlen($string )-1) ];
      }
      return $block;
    }

    public static function json_message($type, $text) {
      return json_encode(array(
        "message" => array (
          'type' => $type,
          'text' => $text
        )
      ));
    }

    public static function get_excerpt_by_id( $post_id )
    {
      $the_post = get_post( $post_id ); //Gets post ID

      $the_excerpt = get_the_excerpt( $post_id );

      if( $the_excerpt == '' )
      {
        $the_excerpt = $the_post->post_content; //Gets post_content to be used as a basis for the excerpt
      }

      $excerpt_length = 90; //Set excerpt length by string length

      $the_excerpt = strip_tags( strip_shortcodes( $the_excerpt ) ); //Strips tags and images

      $the_excerpt = substr( $the_excerpt, 0, $excerpt_length );

      return $the_excerpt;
    }
  }
?>