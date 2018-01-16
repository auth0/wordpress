/* globals jQuery, console, WPAuth0EmailVerification */

jQuery( document ).ready( function ($) {

    var $resendLink = $( '#js-a0-resend-verification' );

    $resendLink.click( function () {

        var postData = {
            action: 'resend_verification_email',
            nonce: WPAuth0EmailVerification.nonce,
            sub: WPAuth0EmailVerification.sub
        };

        $.post( WPAuth0EmailVerification.ajaxUrl, postData )
            .done( function( data ) {

                if ( 'success' === data ) {
                    $resendLink.after( WPAuth0EmailVerification.s_msg );
                    $resendLink.remove();
                } else {
                    alert( WPAuth0EmailVerification.e_msg );
                }

            } )
            .fail( function() {
                alert( WPAuth0EmailVerification.e_msg );
            } );
    } );
} );