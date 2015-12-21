<div class="a0-wrap">

	<?php require(WPA0_PLUGIN_DIR . 'templates/initial-setup/partials/header.php'); ?>
	<?php require(WPA0_PLUGIN_DIR . 'templates/initial-setup/partials/steps.php'); ?>

	<div class="container-fluid">

		<h1><?php _e("Step 2: Set up end-user logins", WPA0_LANG); ?></h1>

		<p class="a0-step-text"><?php _e("If your WordPress site's visitors already have social network accounts, they can access your site with their existing credentials, or they can set up a username/password combination safeguarded by Auth0's password complexity policies and brute force protection.", WPA0_LANG); ?></p>

		<div class="a0-separator"></div>

		<div class="a0-db-connection">
			<h3><?php _e("Database Connections", WPA0_LANG); ?></h3>
			<div class="a0-switch">
				<input type="checkbox" name="dbconnection" id="db-connection-check" value="db" />
				<label for="db-connection-check"></label>
			</div>
			<p class="a0-step-text"><?php _e("Select this option to let users choose their own name/password. If a user already has an account on your site, Auth0 will log them in with their existing credentials and then migrate them to a new account behind the scenes - no need to change passwords.", WPA0_LANG); ?></p>
		</div>

		<div class="a0-separator"></div>

		<h3><?php _e("Social Connections", WPA0_LANG); ?></h3>

		<div class="row">
			<div class="connections col-md-7">
				<?php foreach($social_connections as $social_connection) { ?>
					<div class="connection">
						<div class="logo" data-logo="<?php echo $social_connection['icon']; ?>">
							<span class="logo-child"></span>
						</div>

						<div class="a0-switch">
							<input type="checkbox" name="social_<?php echo $social_connection['provider']; ?>" id="wpa0_social_<?php echo $social_connection['provider']; ?>" value="<?php echo $social_connection['provider']; ?>" <?php echo checked( $social_connection['status'], 1, false ); ?>/>
							<label for="wpa0_social_<?php echo $social_connection['provider']; ?>"></label>
						</div>
					</div>
				<?php } ?>
			</div>
			<div class="lock col-md-5" id="lock-container"></div>
		</div>
	</div>
</div>
<script src="http://cdn.auth0.com/js/lock-8.min.js"></script>
<script type="text/javascript">

var domain = 'wptest.auth0.com';

var clientID = 'KNuydwEqwGsPNpxdAhACmOWDUmBEZsLn'; 


var lock = new Auth0Lock(clientID, domain);

lock.once('shown', function() {
  showLock();
});

lock.once('signin ready', function() {
	jQuery('.connections').height( jQuery('#a0-lock').height() );
});

lock.once('signin success', function() {
  showLock();
});

function showLock() {
	lock.show({
		container: 'lock-container',
		socialBigButtons: true,
		popup:false,
		rememberLastLogin:false,
		sso:false
	},function (err, profile, token) {
		showLock();
	});
}

showLock();

document.addEventListener("DOMContentLoaded", function() {
	
	var q = async.queue(function (task, callback) {

    console.log('enable ' + task.connection);

    callback();
	}, 1);

	jQuery('.a0-switch input').click(function(e) {
		q.push({connection: e.target.value});
	});

	q.drain = function() {
	  showLock()
	}

});
</script>
