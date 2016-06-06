<?php echo __( 'Please verify your email and log in again.', WPA0_LANG );?>
<br/>
<strong><a id="resend" href="#"><?php echo __( 'Resend verification email.', WPA0_LANG );?> </a></strong>
<br/><br/>
<a href="<?php echo wp_login_url()?>"> <?php echo __( '← Login', WPA0_LANG ) ?> </a>
<script src="//code.jquery.com/jquery-1.12.0.min.js"></script>
<script type="text/javascript">
    function resendEmail() {

        jQuery.post({
            url: 'https://<?php echo $domain ?>/api/users/<?php echo $userId ?>/send_verification_email',
            dataType: 'html',
            contentType: 'application/json',
            headers: {
              'Authorization': "Bearer <?php echo $token ?>"
            },
            success: function (resp) {
                alert("An email was sent to <?php echo $email?>" );
            }
        }).fail(function() {
            alert("Sorry, something went wrong");
        })
    }
    document.getElementById("resend").onclick = function () {
        resendEmail();
    }
</script>
