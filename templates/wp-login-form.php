<form method="post" class="wp-form-signin" action="">
   <div class="control-group">
		<label for="user-login-email" class="control-label"><?php _e( 'E-mail', LP_THEME_LANG ); ?></label>
		<div class="controls">
			<input id="user-login-email" name="email" type="email" placeholder="<?php esc_attr_e( 'E-mail', LP_THEME_LANG ); ?>"/>
		</div>
	</div>

	<div class="control-group">
		<label for="user-login-password" class="control-label"><?php _e( 'Password', LP_THEME_LANG ); ?></label>
		<div class="controls">
			<input id="user-login-password" name="password" type="password" placeholder="<?php esc_attr_e( 'Password', LP_THEME_LANG ); ?>"/>
		</div>
	</div>

	<div class="control-group submit">
		<div class="controls submit">
			<button type="submit" class="btn btn-primary pull-right"><?php _e( 'Sign in', LP_THEME_LANG ); ?> <i class="icon icon-white icon-arrow-right"></i></button>
		</div>
	</div>
	<input type="hidden" name="referer" value="<?php echo $_SERVER['REQUEST_URI']; ?>"/>
	<input type="hidden" name="portal-login" value="1"/>
</form>
