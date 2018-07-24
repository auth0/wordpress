<div class="a0-wrap">

	<?php require WPA0_PLUGIN_DIR . 'templates/initial-setup/partials/header.php'; ?>

	<div class="a0-table"><h1><?php _e( 'Auth0 Error Log', 'wp-auth0' ); ?></h1></div>

	<table class="a0-table widefat">
		<thead>
			<tr>
				<th><?php _e( 'Date', 'wp-auth0' ); ?></th>
				<th><?php _e( 'Section', 'wp-auth0' ); ?></th>
				<th><?php _e( 'Error code', 'wp-auth0' ); ?></th>
				<th><?php _e( 'Message', 'wp-auth0' ); ?></th>
			</tr>
		</thead>
		<tbody>
	<?php
	if ( empty( $data ) ) {
		?>
	<tr>
		<td class="message" colspan="4"><?php _e( 'No errors', 'wp-auth0' ); ?></td>
	</tr>
		<?php
	}

	foreach ( $data as $item ) {
		?>
	<tr>
		<td><?php echo date( 'm/d/Y H:i:s', $item['date'] ); ?></td>
		<td><?php echo $item['section']; ?></td>
		<td><?php echo $item['code']; ?></td>
		<td><?php echo $item['message']; ?></td>
	</tr>
		<?php
	}
	?>

		</tbody>
	</table>

</div>
