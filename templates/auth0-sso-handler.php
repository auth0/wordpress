<script id="auth0" src="<?php echo $cdn ?>"></script>
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function() {
    var lock = new Auth0Lock('<?php echo $client_id; ?>', '<?php echo $domain; ?>');
    lock.$auth0.getSSOData(function(err, data) {
        if (!err && data.sso) {
            lock.$auth0.signin(<?php echo json_encode($lock_options->get_sso_options()); ?>);
        }
    });
});
</script>
