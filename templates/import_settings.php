<?php
$opts          = WP_Auth0_Options::Instance();
$constant_keys = $opts->get_all_constant_keys();
?>
<div class="a0-wrap settings">

	<?php require WPA0_PLUGIN_DIR . 'templates/initial-setup/partials/header.php'; ?>

  <div class="container-fluid">

	<div class="row">
	  <h1><?php _e( 'Import and Export Settings', 'wp-auth0' ); ?></h1>
	  <p class="a0-step-text top-margin">
			<?php _e( 'You can import and export your Auth0 WordPress plugin settings here. ', 'wp-auth0' ); ?>
			<?php _e( 'This allows you to either backup the data, or to move your settings to a new WordPress instance.', 'wp-auth0' ); ?>
		</p>
		<?php if ( ! empty( $constant_keys ) ) : ?>
			<p class="a0-step-text top-margin no-bottom-margin">
				<strong><?php _e( 'Please note:', 'wp-auth0' ); ?></strong>
				<?php _e( 'Settings stored in constants cannot be exported or imported.', 'wp-auth0' ); ?>
			</p>
		<?php endif; ?>
	</div>
	<div class="row">

	  <ul class="nav nav-tabs" role="tablist">
		<li role="presentation" class="active"><a href="#import" aria-controls="import" role="tab" data-toggle="tab" class="js-a0-import-export-tabs"><?php _e( 'Import Settings', 'wp-auth0' ); ?></a></li>
		<li role="presentation"><a href="#export" aria-controls="export" role="tab" data-toggle="tab" class="js-a0-import-export-tabs"><?php _e( 'Export Settings', 'wp-auth0' ); ?></a></li>
	  </ul>

	  <div class="tab-content">
		<div role="tabpanel" class="tab-pane active" id="import">

		  <form action="options.php" method="post" enctype="multipart/form-data">
			<input type="hidden" name="action" value="wpauth0_import_settings" />

			  <p class="a0-step-text top-margin"><?php _e( 'Paste the settings JSON in the field below:', 'wp-auth0' ); ?>
			  <div class="a0-step-text top-margin"><textarea name="settings-json" class="large-text code" rows="6"></textarea></div>

			<div class="a0-buttons">
			  <input type="submit" name="setup" class="a0-button primary" value="<?php _e( 'Import', 'wp-auth0' ); ?>" />
			</div>

		  </form>

		</div>
		<div role="tabpanel" class="tab-pane" id="export">

		  <form action="options.php" method="post">
			<input type="hidden" name="action" value="wpauth0_export_settings" />

			<p class="a0-step-text top-margin"><?php _e( 'Download the entire plugin configuration.', 'wp-auth0' ); ?></p>

			<div class="a0-buttons">
			  <input type="submit" name="setup" class="a0-button primary" value="<?php _e( 'Export', 'wp-auth0' ); ?>" />
			</div>

		  </form>

		</div>
	  </div>

	  </div>

  </div>

</div>
