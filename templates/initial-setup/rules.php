<div class="wrap">

	<?php screen_icon(); ?>
	<h2><?php _e("Auth0 for WordPress - Setup Wizard (step $step)", WPA0_LANG); ?></h2>

	<p>This will create a new database connections, expose 2 endpoints and will pupulate the custom scripts to call this endpoints to migrate the users to auth0. the users will not be changed in wordpress.</p>

	<form action="options.php" method="POST">
		<input type="hidden" name="action" value="wpauth0_callback_step5" />

		<div>
			MFA: <input type="checkbox" name="mfa" id="wpa0_mfa" value="1" <?php echo (empty($mfa) ? '' : 'checked'); ?>/>
			<div class="subelement">
				<span class="description">
					<?php echo __( 'Mark this if you want to enable multifactor authentication with Google Authenticator. More info ', WPA0_LANG ); ?>
					<a target="_blank" href="https://auth0.com/docs/mfa"><?php echo __( 'HERE', WPA0_LANG ); ?></a>.
					<?php echo __( 'You can enable other MFA providers from the ', WPA0_LANG ); ?>
					<a target="_blank" href="https://manage.auth0.com/#/multifactor"><?php echo __( 'Auth0 dashboard', WPA0_LANG ); ?></a>.
				</span>
			</div>
		</div>

		<div>
			GEO: <input type="checkbox" name="geo_rule" id="wpa0_geo_rule" value="1" <?php echo (is_null($geo) ? '' : 'checked'); ?>/>
			<div class="subelement">
				<span class="description">
					<?php echo __( 'Mark this if you want to store geo location information based on your users IP in the user_metadata', WPA0_LANG );?>
				</span>
			</div>
		</div>


		<div>
			Income: <input type="checkbox" name="income_rule" id="wpa0_income_rule" value="1" <?php echo (is_null($income) ? '' : 'checked'); ?>/>
			<div class="subelement">
				<span class="description"><?php echo __( 'Mark this if you want to store income data based on the zipcode (calculated using the users IP).', WPA0_LANG ); ?></span>
			</div>
			<div class="subelement">
				<span class="description"><?php echo __( 'Represents the median income of the users zipcode, based on last US census data.', WPA0_LANG ); ?></span>
			</div>
		</div>

		<div>
			Fullcontact: <input type="checkbox" class="toggle_check" id="wpa0_fullcontact" name="fullcontact" value="1" <?php echo (empty($fullcontact) ? '' : 'checked'); ?> />

			<div class="subelement toggle fullcontact <?php echo (empty($v) ? 'hidden' : ''); ?>">
				<label for="wpa0_fullcontact_key" id="wpa0_fullcontact_key_label">Enter your FullContact api key:</label>
				<input type="text" id="wpa0_fullcontact_key" name="fullcontact_apikey" value="<?php echo $fullcontact_apikey; ?>" />
			</div>

			<div class="subelement">
				<span class="description">
					<?php echo __( 'Mark this if you want to hydrate your users profile with the data provided by FullContact. A valid api key is requiere.', WPA0_LANG ); ?>
					<?php echo __( 'More info ', WPA0_LANG ); ?>
					<a href="https://auth0.com/docs/scenarios/fullcontact"><?php echo __( 'HERE', WPA0_LANG );?></a>
				</span>
			</div>
		</div>



		<input type="submit" value="Next" name="next"/>
	</form>

</div>
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function() {
	jQuery(".toggle_check").click(function(){
			jQuery(this).parent().find(".subelement.toggle").toggle(this.checked).removeClass('hidden');
	});
});
</script>
