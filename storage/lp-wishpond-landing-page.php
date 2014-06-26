<?php
  // Acts like a model for wishpond landing pages
  class LpWishpondLandingPage
  {
    const POST_META_KEY                           = "wishpond_landing_page";
    const POST_META_WISHPOND_ID_KEY               = "wishpond_landing_page_id";
    const POST_META_WISHPOND_MARKETING_ID_KEY     = "wishpond_lp_marketing_id";
    const POST_META_WISHPOND_IMAGE_KEY            = "wishpond_landing_page_image";
    const POST_META_WISHPOND_APPID_KEY            = "wishpond_landing_page_appid";

    # Need to shorten it because weird things start happening beyond some length
    const POST_META_WISHPOND_DESC_KEY             = "wishpond_landing_page_desc";

    public function __construct($args = array())
    {
      if($args["wordpress_post_id"] == "") 
      {
        $args["wordpress_post_id"] = -1;
      }
      $this->update_values($args);
    }

    // Should be done in accessors, too few to matter now though
    public function update_values($args = array())
    {
      if(isset($args["path"]) && $args["path"] != "")
      {
        $this->path         = $args["path"];
      }
      if(isset($args["title"]) && $args["title"] != "")
      {
        $this->title        = $args["title"];
      }
      if(isset($args["description"]) && $args["description"] != "")
      {
        $this->description        = $args["description"];
      }
      if(isset($args["image_url"]) && $args["image_url"] != "")
      { 
        $this->image_url    = $args["image_url"];
      }
      if(isset($args["wishpond_marketing_id"]) && $args["wishpond_marketing_id"] != "")
      { 
        $this->wishpond_marketing_id    = $args["wishpond_marketing_id"];
      }
      if(isset($args["wishpond_id"]) && $args["wishpond_id"] != "")
      { 
        $this->wishpond_id    = $args["wishpond_id"];
      }
      if(isset($args["wordpress_post_id"]) && $args["wordpress_post_id"] != "")
      { 
        $this->wordpress_post_id    = $args["wordpress_post_id"];
      }
      if(isset($args["facebook_app_id"]) && $args["facebook_app_id"] != "")
      { 
        $this->facebook_app_id    = $args["facebook_app_id"];
      }
      self::store_meta($args);
    }

    public function id()
    {
      return $this->wordpress_post_id;
    }

    public function wordpress_id()
    {
      return $this->wordpress_post_id;
    }

    public function wordpress_post()
    {
      return get_post($this->wordpress_post_id);
    }

    public function url()
    {
      return get_bloginfo('url') . "/" . $this->path;
    }

    public static function sanitize($permalink)
    {
      if(preg_match("/[^A-Za-z_\-0-9]/i", $path)) {
        return "Please only use alphanumeric characters, dashes and underscores in the landing page url";
      }
      if(strlen($string) > 0) {
        return "Please only use alphanumeric characters, dashes and underscores in the landing page url";
      }
      return true;
    }

    /**
    * Creates a wordpress post, if possible
    */
    public function save()
    {
      $post_id = $this->wordpress_post_id;

      // Setup post info
      $author_id = get_current_user_id();
      $slug = $this->path;
      $title = $this->title;

      $fields = array(
        'comment_status'=>  'closed',
        'ping_status'   =>  'closed',
        'post_author'   =>  $author_id,
        'post_name'     =>  $slug,
        'post_title'    =>  htmlspecialchars($title),
        'post_status'   =>  'publish',
        'post_type'     =>  'page',
        'page_template' =>  'templates/wishpond-landing-page.php',
        'post_content'  =>  '<!-- Please do not edit this page directly. This page was created using the Landing Pages Builder plugin by Wishpond -->[wpsc_landing_page id="'.$this->wishpond_id.'" width="100%"]'
      );

      //already have a wordpress id for this
      if($this->wordpress_post_id > 0)
      {
        $fields["ID"] = $this->wordpress_post_id;
        wp_update_post($fields);
        self::store_meta();
        return $this->wordpress_post_id;
      }
      //insert a new wordpress post
      else
      {
        // If page already exists, just update it
        $existing_post = LpWishpondLandingPage::get_by_wishpond_id($this->wishpond_id);

        if($existing_post == NULL)
        {
          // If the page doesn't already exist, then create it; this might require permalinks
          if( null == get_page_by_title( $title ) ) {
            $post_id = wp_insert_post($fields);
            $this->wordpress_post_id = $post_id;
            self::store_meta();
          } else {
            $post_id = -2;
          }
          return $post_id;
        }
        else
        {
          // Probably never reached - unless lp-wishpond-landing-page gets initialized without a wordpress post id
          $fields["ID"] = $existing_post->wordpress_post_id;
          wp_update_post($fields);
          self::store_meta();
          return $existing_post->wordpress_post_id;
        }
      }
    }

    public static function load($post)
    {
      if($post == NULL)
      {
        return NULL;
      }
      return new LpWishpondLandingPage(array(
        "path"                  => $post->post_name,
        "wishpond_marketing_id" => get_post_meta($post->ID, LpWishpondLandingPage::POST_META_WISHPOND_MARKETING_ID_KEY, true),
        "wishpond_id"           => get_post_meta($post->ID, LpWishpondLandingPage::POST_META_WISHPOND_ID_KEY, true),
        "title"                 => $post->post_title,
        "image_url"             => get_post_meta($post->ID, LpWishpondLandingPage::POST_META_WISHPOND_IMAGE_KEY, true),
        "description"           => get_post_meta($post->ID, LpWishpondLandingPage::POST_META_WISHPOND_DESC_KEY, true),
        "facebook_app_id"       => get_post_meta($post->ID, LpWishpondLandingPage::POST_META_WISHPOND_APPID_KEY, true),
        "wordpress_post_id"     => $post->ID
      ));
    }

    public function get_by_wordpress_id($wordpress_id)
    {
      return LpWishpondLandingPage::load(get_post($wordpress_id));
    }

    public function get_by_wishpond_id($wishpond_id)
    {
      $meta_query[] = array(
        'key' => LpWishpondLandingPage::POST_META_WISHPOND_ID_KEY,
        'value' => $wishpond_id,
        'compare' => '='
      );

      $args = array('post_type' => 'page'); 
      $query = new WP_Query($args);

      $query->set('meta_query', $meta_query);
      $posts = $query->get_posts();
      return LpWishpondLandingPage::load($posts[0]);
    }

    public function store_meta($args = array())
    {
      $wishpond_id            = $args["wishpond_id"] != "" ? $args["wishpond_id"] : $this->wishpond_id;
      $wishpond_marketing_id  = $args["wishpond_marketing_id"] != "" ? $args["wishpond_marketing_id"] : $this->wishpond_marketing_id;
      $image_url              = $args["image_url"] != "" ? $args["image_url"] : $this->image_url;
      $description            = $args["description"] != "" ? $args["description"] : $this->description;

      $facebook_app_id        = $args["facebook_app_id"] != "" ? $args["facebook_app_id"] : $this->facebook_app_id;

      add_post_meta($this->wordpress_post_id, LpWishpondLandingPage::POST_META_KEY, true, true);

      if($wishpond_id  != "")
      {
        self::add_or_update_meta($this->wordpress_post_id, LpWishpondLandingPage::POST_META_WISHPOND_ID_KEY, $wishpond_id);
      }

      if($wishpond_marketing_id != "")
      {
        self::add_or_update_meta($this->wordpress_post_id, LpWishpondLandingPage::POST_META_WISHPOND_MARKETING_ID_KEY, $wishpond_marketing_id);
      }

      if($image_url != "")
      {
        self::add_or_update_meta($this->wordpress_post_id, LpWishpondLandingPage::POST_META_WISHPOND_IMAGE_KEY, $image_url);
      }

      if($description != "")
      {
        self::add_or_update_meta($this->wordpress_post_id, LpWishpondLandingPage::POST_META_WISHPOND_DESC_KEY, $description); 
      }
      
      if($facebook_app_id != "")
      {
        self::add_or_update_meta($this->wordpress_post_id, LpWishpondLandingPage::POST_META_WISHPOND_APPID_KEY, $facebook_app_id); 
      }
      add_post_meta($this->wordpress_post_id, '_wp_page_template', 'templates/wishpond-landing-page.php', true);
    }

    public function add_or_update_meta($id, $key, $value)
    {
      if(get_post_meta($id, $key, $value))
      {
        update_post_meta($id, $key, $value);
      }
      else
      {
        add_post_meta($id, $key, $value);
      }
    }

    public static function get_all_posts()
    {
      $meta_query[] = array(
        'key' => LpWishpondLandingPage::POST_META_KEY,
        'value' => true,
        'compare' => '='
      );

      $args = array('post_type' => 'page'); 
      $query = new WP_Query($args);

      $query->set('meta_query', $meta_query);
      return $query->get_posts();
    }

    public static function page_by_slug($slug)
    {
      return get_page_by_path($slug);
    }

    public static function page_by_id($id)
    {
      return get_post($id); 
    }

    public static function page_slug_used($slug, $allowed_id = "")
    {
      $post = self::page_by_slug($slug);
      if($post != NULL && $post->ID != $allowed_id)
      {
        return true;
      }
      return false;
    }
  }
?>