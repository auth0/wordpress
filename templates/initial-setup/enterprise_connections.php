<div class="a0-wrap">

  <?php require(WPA0_PLUGIN_DIR . 'templates/initial-setup/partials/header.php'); ?>

  <div class="container-fluid">
    <div class="row">
      <h1><?php _e("Configure your enterprise connections", WPA0_LANG); ?></h1>

      <p class="a0-step-text"><?php _e("Make it convenient and secure for your employees to access your WordPress site. Connect your enterprise directory to Auth0 and your users won't need to log in at all, if they're already logged in on the enterprise directory. If they aren't logged in, they'll use their employee credentials - no additional passwords to remember. To configure enterprise connections, you'll use the Auth0 Dashboard.", WPA0_LANG); ?></p>

      <div class="a0-separator"></div>

      <h3><?php _e("Auth0 supports the following identity providers", WPA0_LANG); ?></h3>

    </div>
    <div class="row enterprise-connections">
    <?php foreach($providers as $provider) { ?>
      <div class="col-md-3 col-sm-4 col-xs-6">
        <div class="connection">
          <div class="logo" style="background:#fff url('<?php echo WPA0_PLUGIN_URL; ?>/assets/img/initial-setup/enterprise-connections/<?php echo $provider['icon']; ?>.png') no-repeat center center;"></div>
          <h4 class="title-wrapper"><?php echo $provider['name']; ?></h4>
          <?php if ($provider['url'] !== null) { ?>
          <a onclick="onClick('<?php echo $provider['name']; ?>')" href="<?php echo $provider['url']; ?>" target="_blank"><?php _e("READ MORE", WPA0_LANG); ?></a>
          <?php } else { ?>
          <span>&nbsp;</span>
          <?php } ?>
        </div>
      </div>
    <?php } ?>
    </div>

    <div class="a0-buttons">
      <a onclick="gotodashboard()" href="https://manage.auth0.com" class="a0-button primary" target="_blank"><?php _e("GO TO DASHBOARD", WPA0_LANG); ?></a>
      <a onclick="next()" href="<?php echo admin_url('admin.php?page=wpa0-setup&step=4'); ?>"class="a0-button link"><?php _e("Skip this step", WPA0_LANG); ?></a>
    </div>
  </div>
</div>

<script type="text/javascript">

document.addEventListener("DOMContentLoaded", function() {

  if (typeof(a0metricsLib) !== 'undefined') {
    a0metricsLib.track('open:initial-setup', {
      step:3
    });
  }

});

function onClick(connection) {
  metricsTrack('initial-setup:step4:view:' + connection);
}

function next() {
  metricsTrack('initial-setup:step4:next');
}

function gotodashboard() {
  metricsTrack('initial-setup:step4:dashboard');
}

</script>