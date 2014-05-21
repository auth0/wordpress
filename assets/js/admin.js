jQuery(document).ready(function($) {
    //uploading files variable
    var media_frame;
    $(document).on('click', '#wpa0_choose_icon', function(event) {
        event.preventDefault();
        //If the frame already exists, reopen it
        if (typeof(media_frame)!=="undefined")
         media_frame.close();

        //Create WP media frame.
        media_frame = wp.media.frames.customHeader = wp.media({
            title: wpa0.media_title,
            library: {
                type: 'image'
            },
            button: {
                text: wpa0.media_button
            },
            multiple: false
        });

        // Set the frame callback
        media_frame.on('select', function() {
            var attachment = media_frame.state().get('selection').first().toJSON();
            $('#wpa0_icon_url').val(attachment.url);
        });

        //Open modal
        media_frame.open();
    });

    function configureHideShowAutoLogin() {
        // Hide/Show login method depending on auto login
        var $loginMethodField = $("#wpa0_auto_login_method").closest("tr");
        var $autoLoginCheckbox = $("#wpa0_auto_login");
        if (!$autoLoginCheckbox.prop("checked")) {
            $loginMethodField.hide();
        }

        $autoLoginCheckbox.change(function() {
            if (!$autoLoginCheckbox.prop("checked")) {
                $loginMethodField.hide();
            } else {
                $loginMethodField.show();
            }

        });
    }

    configureHideShowAutoLogin();

});