<div class="wrap">
    <?php screen_icon(); ?>
    <h2><?php _e('Auth0 Error Log', WPA0_LANG); ?></h2>

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
        foreach ($data as $item)
        {
    ?>
        <tr>
            <td><?php echo date('m/d/Y H:i:s', strtotime($item->date)); ?></td>
            <td><?php echo $item->section; ?></td>
            <td><?php echo $item->code; ?></td>
            <td><?php echo $item->message; ?></td>
        </tr>
    <?php
        }
    ?>

        </tbody>
    </table>
</div>