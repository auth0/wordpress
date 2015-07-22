jQuery(document).ready(function($) {
    jQuery( "form h3" )
        .prepend(' <span class="icon">+</span><span class="icon" style="display:none">-</span>')
        .click(function(){
            jQuery(this).next().toggle();
            jQuery(this).find('.icon').toggle();
        }).next().hide();

    jQuery(jQuery( "form h3" )[1]).click();
    jQuery("#wpa0_fullcontact").click(function(){
        jQuery("#wpa0_fullcontact_key_label").toggle().removeClass('hidden');
        jQuery("#wpa0_fullcontact_key").toggle().removeClass('hidden').val('');
    });

    jQuery(".wpa0_social_checkbox").click(function(){
        jQuery(".social").toggle(this.checked).removeClass('hidden');
    });

    //uploading files variable
    var media_frame;
    $(document).on('click', '#wpa0_choose_icon', function(event) {
        event.preventDefault();
        //If the frame already exists, reopen it
        if (typeof(media_frame)!=="undefined")
         media_frame.close();

        var related_control_id = 'wpa0_icon_url';
        if (typeof($(this).attr('related')) != 'undefined' &&
            $(this).attr('related') != '')
        {
            related_control_id = $(this).attr('related');
        }

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
            $('#'+related_control_id).val(attachment.url);
            console.log($('#'+related_control_id));
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
