<div class="a0-wrap settings wrap">

	<div class="container-fluid">

			<h1><?php _e('Step 2:', 'wp-auth0'); ?> <?php _e('Configure your Connections', 'wp-auth0'); ?></h1>

			<p class="a0-step-text"><?php _e("If your site visitors already have social network accounts, they can authenticate using their existing credentials, or they can set up a username and password combination safeguarded by Auth0's password policies and brute force protection. To configure these connections, use the Configure Connections button below.", 'wp-auth0'); ?></p>

			<div class="a0-separator"></div>

		</div>

		<div class="row">
			<div class="a0-buttons">
			<a href="https://manage.auth0.com/#/applications/
			<?php echo esc_attr(wp_auth0_get_option('client_id')); ?>
			/connections" class="button button-secondary" target="_blank">
			<?php
              _e('Configure Connections', 'wp-auth0');
            ?>
			  </a>
			<a class="button button-primary" href="
			<?php
            echo admin_url('admin.php?page=wpa0-setup&step=' . (wp_auth0_get_option('migration_ws') ? 4 : 3));
            ?>
			" >
			<?php
              _e('Next', 'wp-auth0')
            ?>
			  </a>
		</div>
	</div>
</div>
