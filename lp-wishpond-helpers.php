<?php
  class LpWishpondHelpers
  {
    /* Gets a randomly generated string */
    public static function get_random_string( $length=16 )
    {
      list( $usec, $sec ) = explode( ' ', microtime() );
      mt_srand( ( float ) $sec + ( (float ) $usec * 100000 ) );
      $chars ="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890!@#$%^&*():;{}[]|+=-_<>?/~`";//length:89
      $final_rand='';
      for( $i=0;$i<$length; $i++ )
      {
          $final_rand .= $chars[ mt_rand( 0,strlen($chars )-1)];
   
      }
      return $final_rand;
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