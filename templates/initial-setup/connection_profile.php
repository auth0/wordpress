<div class="wrap">

  <?php screen_icon(); ?>
  <h2><?php _e("Auth0 for WordPress - Quick Start Guide (step $step)", WPA0_LANG); ?></h2>

  <p>What kind of connections do you want?</p>

  <form action="options.php" method="POST">

    <input type="hidden" name="action" value="wpauth0_callback_step2" />

    <input type="submit" value="Social" name="type"/>
    <input type="submit" value="Enterprise" name="type"/>
  </form>

</div> 
