<div class="wrap">

	<?php screen_icon(); ?>
	<h2><?php _e("Auth0 for WordPress - Quick Start Guide (step $step)", WPA0_LANG); ?></h2>

	<form action="options.php" method="POST">

			<input type="hidden" name="action" value="wpauth0_callback_step4" />


			<input type="checkbox" class="wpa0_social_checkbox" name="<?php echo $this->a0_options->get_options_name(); ?>[social_facebook]" id="wpa0_social_facebook" value="1" <?php echo checked( $social_facebook, 1, false ); ?>/>
			<div class="subelement social_facebook <?php echo ($social_facebook ? '' : 'hidden'); ?>">
				<label for="wpa0_social_facebook_key" id="wpa0_social_facebook_key_label">Api key:</label>
				<input type="text" id="wpa0_social_facebook_key" name="<?php echo $this->a0_options->get_options_name(); ?>[social_facebook_key]" value="<?php echo $social_facebook_key; ?>" />
			</div>
			<div class="subelement social_facebook <?php echo ($social_facebook ? '' : 'hidden'); ?>">
				<label for="wpa0_social_facebook_secret" id="wpa0_social_facebook_secret_label">Api secret:</label>
				<input type="text" id="wpa0_social_facebook_secret" name="<?php echo $this->a0_options->get_options_name(); ?>[social_facebook_secret]" value="<?php echo $social_facebook_secret; ?>" />
			</div>
			<div class="subelement social_facebook <?php echo ($social_facebook ? '' : 'hidden'); ?>">
				<span class="description"><?php echo __( 'If you leave your keys empty Auth0 will use its own keys, but we recommend to use your own app. It will you customize the data you want to receive (ie, birthdate for the dashboard age chart).', WPA0_LANG ); ?></span>
			</div>


			<input type="submit" value="Next" name="next"/>


	</form>

</div>
