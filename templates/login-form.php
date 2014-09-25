<?php
if(is_user_logged_in())
    return;
?>

<style type="text/css">
    #extra-options {
        width: 272px;
        margin:auto;
        padding: 0px 24px;
        margin-top:15px;
    }
    .form-signin #extra-options {
        margin-bottom:15px;
        margin-top:0;
    }
    #extra-options a {
        text-decoration: none;
        color: #999;
    }
    #extra-options a:hover {
        color: #2ea2cc;
    }
</style>

<?php
$wordpress_login_enabled = WP_Auth0_Options::get('wordpress_login_enabled') == 1;
if (!$wordpress_login_enabled || !isset($_GET['wle'])) {
    include ('auth0-login-form.php');
}else{
    add_action('login_footer', array(WP_Auth0::class, 'render_back_to_auth0'));
}
?>