<div class="wrap">

	<?php screen_icon(); ?>
	<h2><?php _e("Auth0 for WordPress - Quick Start Guide (step $step)", WPA0_LANG); ?></h2>

	<div id="a0-lock-wrapper">

  </div>

  <script type="text/javascript">
    function post(path, params, method) {
        method = method || "post"; // Set method to post by default if not specified.

        // The rest of this code assumes you are not using a library.
        // It can be made less wordy if you use one.
        var form = document.createElement("form");
        form.setAttribute("method", method);
        form.setAttribute("action", path);

        for(var key in params) {
            if(params.hasOwnProperty(key)) {
                var hiddenField = document.createElement("input");
                hiddenField.setAttribute("type", "hidden");
                hiddenField.setAttribute("name", key);

                var value = params[key];

                if (typeof(value) === 'object') {
                    value = JSON.stringify(value);
                }

                hiddenField.setAttribute("value", value);

                form.appendChild(hiddenField);
             }
        }

        document.body.appendChild(form);
        form.submit();
    }
    document.addEventListener("DOMContentLoaded", function() {
      var lock = new Auth0Lock('<?php echo $client_id; ?>', '<?php echo $domain; ?>');
      lock.show({
        container: 'a0-lock-wrapper',
				authParams: {
						scope: "openid update:clients update:connections create:connections read:connections create:rules delete:rules update:users name email nickname email_verified identities"
				}
      }, function(err, profile, token) {

        post('options.php', {
          action: 'wpauth0_callback_step3',
          token: token,
					state:<?php echo json_encode(array(
						'redirect_to' => admin_url( 'admin.php?page=wpa0-setup&step=4' )
					)); ?>
        }, 'POST');

      });
    });
  </script>

</div>
