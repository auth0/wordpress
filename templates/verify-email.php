<?php echo __('Please verify your email and log in again.', WPA0_LANG);?>
<a id="resend"><?php echo __('Resend verification email.', WPA0_LANG);?> </a>
<br/><br/>
<a href="<?php echo wp_login_url()?>"> <?php echo __('← Login', WPA0_LANG) ?> </a>
<script type="text/javascript">
    function resendEmail() {
        var xmlhttp = null;
        if (window.XMLHttpRequest)
        {// code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        }
        else
        {// code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }

        var url = "https://<?php echo $domain ?>/api/users/send_verification_email";
        var data = '{"email" : "<?php echo $email ?>", "connection" : "Username-Password-Authentication"}';
        xmlhttp.open("POST", url, true);
        xmlhttp.setRequestHeader("Authorization", "Bearer <?php echo $token ?>");
        xmlhttp.setRequestHeader("Content-Type", "application/json");
        xmlhttp.send(data);
    }
    document.getElementById("resend").onclick = function () {
        resendEmail();
        alert("An email was sent to <?php echo $email?>" );
    }
</script>
