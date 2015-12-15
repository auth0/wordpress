<div class="a0-wrap">

	<?php require(WPA0_PLUGIN_DIR . 'templates/initial-setup/partials/header.php'); ?>

	<div class="container-fluid">

		<h1><?php _e("Step 2: Set up end-user logins", WPA0_LANG); ?></h1>

		<p><?php _e("If your WordPress site's visitors already have social network accounts, they can access your site with their existing credentials, or they can set up a username/password combination safeguarded by Auth0's password complexity policies and brute force protection.", WPA0_LANG); ?></p>


		<div class="row">
			<div class="connections col-md-8">
				<?php foreach($social_connections as $social_connection) { ?>
					<div class="connection">
						<div class="logo" data-logo="<?php echo $social_connection['icon']; ?>">
							<span class="logo-child"></span>
						</div>
						<input type="checkbox" class="wpa0_social_checkbox" name="social_<?php echo $social_connection['provider']; ?>" id="wpa0_social_<?php echo $social_connection['provider']; ?>" value="<?php echo $social_connection['provider']; ?>" <?php echo checked( $social_connection['status'], 1, false ); ?>/>
					</div>
				<?php } ?>
			</div>
		</div>



	</div>
</div>
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function() {
	
	var q = async.queue(function (task, callback) {

    console.log('enable ' + task.connection);

    callback();
	}, 1);

	jQuery('.wpa0_social_checkbox').click(function(e) {
		q.push({connection: e.value});
	});

	q.drain = function() {
	  console.log('Update lock');
	}

});
</script>
