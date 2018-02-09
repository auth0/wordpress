/* global jQuery, wpa0 */
jQuery(document).ready(function($) {
    //uploading files variable
    var media_frame;
    $(document).on('click', '#wpa0_choose_icon', function(event) {
        event.preventDefault();
        //If the frame already exists, reopen it
        if (typeof(media_frame)!=="undefined")
         media_frame.close();

        var related_control_id = 'wpa0_icon_url';
        if ( $(this).attr('related') ) {
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
        });

        //Open modal
        media_frame.open();
    });

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

    // Clear cache button on Basic settings page
    var deleteCacheId = 'auth0_delete_cache_transient';
    var $deleteCacheButton = $( '#' + deleteCacheId );
    $deleteCacheButton.click( function(e) {
        e.preventDefault();
        $deleteCacheButton.prop( 'disabled', true ).val( wpa0.clear_cache_working );
        var postData = {
            'action': deleteCacheId,
            '_ajax_nonce': wpa0.clear_cache_nonce
        };

        $.post(wpa0.ajax_url, postData, function() {
            $deleteCacheButton.prop( 'disabled', false ).val( wpa0.clear_cache_done );
        }, 'json');
    } );
});
