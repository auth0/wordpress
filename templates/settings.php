<div class="wrap">
	<?php screen_icon(); ?>
	<h2><?php _e('Auth0 Settings', WPA0_LANG); ?></h2>
	<form action="options.php" method="post">
		<?php settings_fields( WP_Auth0_Options::OPTIONS_NAME ); ?>  
		<?php do_settings_sections( WP_Auth0_Options::OPTIONS_NAME ); ?>  
		<?php submit_button(); ?>
	</form>
</div>