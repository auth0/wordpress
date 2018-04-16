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

    // Show/hide field for specific switches
    $('[data-expand!=""]').each( function() {
        var $thisSwitch = $( this );
        var $showFieldRow = $( '#' + $thisSwitch.attr( 'data-expand' ) ).closest( 'tr' );

        if ( $showFieldRow.length ) {
            if ( ! $thisSwitch.prop( 'checked' ) ) {
                $showFieldRow.hide();
            }
            $thisSwitch.change(function() {
                if ( $( this ).prop( 'checked' ) ) {
                    $showFieldRow.show();
                } else {
                    $showFieldRow.hide();
                }
            } );
        }
    });

    // Persistent admin tab
    if ( localStorageAvailable() ) {
        window.location.hash = window.localStorage.getItem( 'Auth0WPSettingsTab' );
        $( '.nav-tabs [role="tab"]' ).click( function () {
            var tabHref = $( this ).attr( 'href' );
            window.location.hash = tabHref;
            window.localStorage.setItem( 'Auth0WPSettingsTab', tabHref );
        } );
    }

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

    function localStorageAvailable() {
        try {
            var x = '__storage_test__';
            window.localStorage.setItem(x, x);
            window.localStorage.removeItem(x);
            return true;
        }
        catch(e) {
            return false;
        }
    }
});
