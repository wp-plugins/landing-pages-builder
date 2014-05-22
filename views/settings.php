<?php
  if( ! current_user_can( 'activate_plugins' ) )
  {
    wp_die( __( 'Not enough permissions' ) );
  }
?>
<div class="wrap">
  <?php screen_icon(); ?>
  <h2>Landing Pages Builder Settings</h2>
  <form method="post" action="">
    <h3> Wishpond Authentication </h3>
    <?php if($post_error): ?>
      <div class="error"><p><?php echo $post_error; ?></p></div>
    <?php endif; ?>

    <?php if($notice): ?>
      <div class="updated"><p><?php echo $notice; ?></p></div>
    <?php endif; ?>
    
    <input type="checkbox" id="enable_automatic_authentication" name="enable_automatic_authentication" 
      <?php
        if( LpWishpondStorage::using_token_based_auth() )
        {
          echo "checked='checked'";
        }
      ?>
    />
    <label for="enable_automatic_authentication">
      <?php
        echo __( "Enable Automatic Authentication", LANDING_PAGES_SLUG );
      ?>
    </label>
    <p>
      Previous versions of this plugin used to to automatically set up accounts on wishpond.com,
      and automatically authenticate those accounts to wishpond.com whenever they were accessed from wordpress.
      This feature was taken out due to popular requests, but was kept for backwards-compatibility.<br/><br/>
      Uncheck this if you know your Wishpond e-mail and password and would like to disable automatic authentication.<br/>
      <i>Please note: since this feature was removed, you can't enable automatic authentication once it's disabled</i>
    </p>
    <hr/>
    <br/><br/>
    <input type="checkbox" id="enable_guest_signup" name="enable_guest_signup" 
      <?php
        if( LpWishpondStorage::is_guest_signup_enabled() )
        {
          echo "checked='checked'";
        }
      ?>
    />
    <label for="enable_guest_signup">
      <?php
        echo __( "Enable Guest Signup", LANDING_PAGES_SLUG );
      ?>
    </label>
    <p>
      Automatically use a guest account on wishpond.com when not authenticated on wishpond.com<br/><br/>
      This lets you start using this plugin without having a wishpond.com account.
    </p>
    <?php submit_button(); ?>
  </form>
</div>