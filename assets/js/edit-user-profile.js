/* global jQuery, wpa0UserProfile, alert */

jQuery(function($) {
    'use strict';

    var passwordFieldRow = $('#password');
    var emailField = $('input[name=email]');
    var deleteUserDataButton = $('#auth0_delete_data');
    var deleteMfaDataButton = $('#auth0_delete_mfa');

    /**
     * Hide the password field if not an Auth0 strategy.
     */
    if ( passwordFieldRow.length && 'auth0' !== wpa0UserProfile.userStrategy ) {
        passwordFieldRow.hide();
    }

    /**
     * Disable email changes if not an Auth0 connection.
     */
    if ( emailField.length && 'auth0' !== wpa0UserProfile.userStrategy ) {
        emailField.prop( 'disabled', true );
        $('<p>' + wpa0UserProfile.i18n.cannotChangeEmail + '</p>')
            .addClass('description')
            .insertAfter(emailField);
    }

    /**
     * Delete Auth0 data button click.
     */
    deleteUserDataButton.click(function (e) {
        if ( ! window.confirm(wpa0UserProfile.i18n.confirmDeleteId) ) {
            return;
        }
        e.preventDefault();
        userProfileAjaxAction($(this), 'auth0_delete_data', wpa0UserProfile.deleteIdNonce );
    });

    /**
     * Delete MFA data button click.
     */
    deleteMfaDataButton.click(function (e) {
        if ( ! window.confirm(wpa0UserProfile.i18n.confirmDeleteMfa) ) {
            return;
        }
        e.preventDefault();
        userProfileAjaxAction($(this), 'auth0_delete_mfa', wpa0UserProfile.deleteMfaNonce);
    });

    /**
     * Perform a generic user profile AJAX call.
     *
     * @param uiControl
     * @param action
     * @param nonce
     */
    function userProfileAjaxAction( uiControl, action, nonce ) {
        var postData = {
            'action' : action,
            'nonce' : nonce,
            'user_id' : wpa0UserProfile.userId
        };
        uiControl.prop( 'disabled', true );
        $.post(
            wpa0UserProfile.ajaxUrl,
            postData,
            function(response) {
                if ( parseInt( response, 10 ) > 0 ) {
                    uiControl.val(wpa0UserProfile.i18n.actionComplete);
                } else {
                    alert(wpa0UserProfile.i18n.actionFailed);
                    uiControl.prop( 'disabled', false );
                }
            }
        );
    }
});