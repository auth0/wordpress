/* globals jQuery, alert, WPAuth0EmailVerification */

jQuery( document ).ready( function ($) {
    'use strict';

    var $resendLink = $( '#js-a0-resend-verification' );

    $resendLink.click( function () {

        var postData = {
            action: 'resend_verification_email',
            _ajax_nonce: WPAuth0EmailVerification.nonce,
            sub: WPAuth0EmailVerification.sub
        };
        var errorMsg = WPAuth0EmailVerification.e_msg;

        $.post( WPAuth0EmailVerification.ajaxUrl, postData )
            .done( function( response ) {
                if ( response.success ) {
                    $resendLink.after( WPAuth0EmailVerification.s_msg );
                    $resendLink.remove();
                } else {
                    if ( response.data && response.data.error ) {
                        errorMsg = response.data.error;
                    }
                    alert( errorMsg );
                }
            } )
            .fail( function() {
                alert( errorMsg );
            } );
    } );
} );