<?php echo __('Please verify your email and log in again.', WPA0_LANG);?>
<br/>
<strong><a id="resend" href="#"><?php echo __('Resend verification email.', WPA0_LANG);?> </a></strong>
<br/><br/>
<a href="<?php echo wp_login_url()?>"> <?php echo __('← Login', WPA0_LANG) ?> </a>
<script src="http://cdnjs.cloudflare.com/ajax/libs/reqwest/1.1.0/reqwest.min.js" type="text/javascript"></script>

<script type="text/javascript">
    function resendEmail() {

        reqwest({
            url: 'https://<?php echo $domain ?>/api/users/send_verification_email',
            type: 'html',
            data: '{"email" : "<?php echo $email ?>", "connection" : "<?php echo $connection?>"}',
            method: 'post',
            contentType: 'application/json',
            headers: {
              'Authorization': "Bearer <?php echo $token ?>"
            },
            error: function (err) {
                alert("Sorry, something went wrong");
            },
            success: function (resp) {
                alert("An email was sent to <?php echo $email?>" );

            }
        });
    }
    document.getElementById("resend").onclick = function () {
        resendEmail();
    }
</script>
