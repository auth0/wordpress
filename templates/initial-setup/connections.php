<div class="a0-wrap">


	<?php require(WPA0_PLUGIN_DIR . 'templates/initial-setup/partials/header.php'); ?>

	<h2><?php _e("Step 2: Set up end-user logins", WPA0_LANG); ?></h2>

	<p><?php _e("If your WordPress site's visitors already have social network accounts, they can access your site with their existing credentials, or they can set up a username/password combination safeguarded by Auth0's password complexity policies and brute force protection.", WPA0_LANG); ?></p>

	<form action="options.php" method="POST">

			<input type="hidden" name="action" value="wpauth0_callback_step4" />

		<?php foreach($social_connections as $social_connection) { ?>
			<div>
				Login with <?php echo $social_connection['name']; ?>:
				<input type="checkbox" class="wpa0_social_checkbox" name="social_<?php echo $social_connection['provider']; ?>" id="wpa0_social_<?php echo $social_connection['provider']; ?>" value="1" <?php echo checked( $social_connection['status'], 1, false ); ?>/>
				<div class="subelement social_<?php echo $social_connection['provider']; ?> <?php echo ($social_connection['status'] ? '' : 'hidden'); ?>">
					<label for="wpa0_social_<?php echo $social_connection['provider']; ?>_key" id="wpa0_social_<?php echo $social_connection['provider']; ?>_key_label">Api key:</label>
					<input type="text" id="wpa0_social_<?php echo $social_connection['provider']; ?>_key" name="social_<?php echo $social_connection['provider']; ?>_key" value="<?php echo $social_connection['key']; ?>" />
				</div>
				<div class="subelement social_<?php echo $social_connection['provider']; ?> <?php echo ($social_connection['status'] ? '' : 'hidden'); ?>">
					<label for="wpa0_social_<?php echo $social_connection['provider']; ?>_secret" id="wpa0_social_<?php echo $social_connection['provider']; ?>_secret_label">Api secret:</label>
					<input type="text" id="wpa0_social_<?php echo $social_connection['provider']; ?>_secret" name="social_<?php echo $social_connection['provider']; ?>_secret" value="<?php echo $social_connection['secret']; ?>" />
				</div>
				<div class="subelement social_<?php echo $social_connection['provider']; ?> <?php echo ($social_connection['status'] ? '' : 'hidden'); ?>">
					<span class="description"><?php echo __( 'If you leave your keys empty Auth0 will use its own keys, but we recommend to use your own app. It will you customize the data you want to receive (ie, birthdate for the dashboard age chart).', WPA0_LANG ); ?></span>
				</div>
			</div>
		<?php } ?>


		<input type="submit" value="Next" name="next"/>


	</form>

</div>
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function() {
	jQuery(".wpa0_social_checkbox").click(function(){
			jQuery(this).parent().find(".subelement").toggle(this.checked).removeClass('hidden');
	});
});
</script>
