<div class="a0-wrap">

    <?php require WPA0_PLUGIN_DIR . 'templates/initial-setup/partials/header.php'; ?>

    <div class="a0-table"><h1><?php _e( 'Auth0 Error Log', WPA0_LANG ); ?></h1></div>

    <table class="a0-table widefat">
        <thead>
            <tr>
                <th>Date</th>
                <th>Section</th>
                <th>Error code</th>
                <th>Message</th>
            </tr>
        </thead>
        <tbody>
    <?php
if ( empty( $data ) ) {
?>
    <tr>
        <td class="message" colspan="4">No errors.</td>
    </tr>
<?php
}

foreach ( $data->posts as $item ) {
?>
    <tr>
        <td><?php echo date( 'm/d/Y H:i:s', strtotime( $item->post_date ) ); ?></td>
        <td><?php echo $item->post_title; ?></td>
        <td><?php echo empty( $item->post_excerpt ) ? '-' : $item->post_excerpt; ?></td>
        <td><?php echo strip_tags( $item->post_content ); ?></td>
    </tr>
<?php
}
?>

        </tbody>
    </table>

</div>
