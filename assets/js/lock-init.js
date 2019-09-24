/* globals console, jQuery, Cookies, wpAuth0LockGlobal, wpAuth0LockGlobalFields, Auth0Lock, Auth0LockPasswordless, auth0 */
jQuery(document).ready(function ($) {
    'use strict';

    var opts = wpAuth0LockGlobal;
    var loginForm = $( '#' + opts.loginFormId );

    // Missing critical Auth0 settings.
    if ( ! opts.ready ) {
        resetWpLoginForm();
        console.error( opts.i18n.notReadyText );
        return;
    }

    // Missing the Lock container.
    if ( ! loginForm.length ) {
        resetWpLoginForm();
        console.error( opts.i18n.cannotFindNodeText + '"' + opts.loginFormId + '"' );
        return;
    }

    // Set state and nonce cookies to verify during callback.
    setStateCookie(opts.settings.auth.params.state);
    if ( opts.settings.auth.params.nonce ) {
        setNonceCookie(opts.settings.auth.params.nonce);
    }

    // Look for additional fields to display.
    if ( typeof wpAuth0LockGlobalFields === 'object' ) {
        opts.settings.additionalSignUpFields = wpAuth0LockGlobalFields;
    }

    // Set Lock to standard or Passwordless.
    var Lock = opts.usePasswordless ?
        new Auth0LockPasswordless( opts.clientId, opts.domain, opts.settings ) :
        new Auth0Lock( opts.clientId, opts.domain, opts.settings );

    // Check if we're showing as a modal (used in shortcodes and widgets).
    if ( opts.showAsModal ) {
        $( '<button>' )
            .text( opts.i18n.modalButtonText )
            .attr( 'id', 'a0LoginButton' )
            .insertAfter( loginForm )
            .click(function () { Lock.show(); });
    } else {
        Lock.show();
    }

    /**
     * Set the state cookie for verification during callback.
     *
     * @param val string - Value for the state cookie.
     */
    function setStateCookie(val) {
        Cookies.set( opts.stateCookieName, val );
    }

    /**
     * Set the nonce cookie for verification during callback.
     *
     * @param val string - Value for the nonce cookie.
     */
    function setNonceCookie(val) {
        Cookies.set( opts.nonceCookieName, val );
    }

    /**
     * Show the WordPress login form.
     */
    function resetWpLoginForm() {
        $( '#form-signin-wrapper' ).hide();
        $( '#loginform' ).show();
        $( '#login' ).find( 'h1' ).show();
    }
});
