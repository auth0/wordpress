/* globals jQuery, console, Cookies, wpAuth0LockGlobal, wpAuth0LockGlobalFields, Auth0Lock, Auth0LockPasswordless */
jQuery(document).ready(function ($) {
    var opts = wpAuth0LockGlobal;
    var loginForm = $( '#' + opts.loginFormId );

    // Missing critical Auth0 settings
    if ( ! opts.ready || ! loginForm.length ) {
        $( '#form-signin-wrapper' ).hide();
        $( '#loginform' ).show();
        $( '#login' ).find( 'h1' ).show();

        if ( ! opts.ready ) {
            console.error( opts.i18n.notReadyText );
        }
        if ( ! loginForm.length ) {
            console.error( opts.i18n.cannotFindNodeText + '"' + opts.loginFormId + '"' );
        }
        return;
    }

    // Set state cookie to verify during callback
    Cookies.set( opts.stateCookieName, opts.settings.auth.params.state );

    if ( opts.settings.auth.params.nonce ) {
        Cookies.set( opts.nonceCookieName, opts.settings.auth.params.nonce );
    }

    // Look for additional fields to display
    if ( typeof wpAuth0LockGlobalFields === 'object' ) {
        opts.settings.additionalSignUpFields = wpAuth0LockGlobalFields;
    }

    // Set Lock to standard or Passwordless
    var Lock = opts.usePasswordless
        ? new Auth0LockPasswordless( opts.clientId, opts.domain, opts.settings )
        : new Auth0Lock( opts.clientId, opts.domain, opts.settings );

    // Check if we're showing as a modal, can be used in shortcodes and widgets
    if ( opts.showAsModal ) {
        $( '<button>' )
            .text( opts.i18n.modalButtonText )
            .attr( 'id', 'a0LoginButton' )
            .insertAfter( loginForm )
            .click( function () {
                Lock.show();
            } );
    } else {
        Lock.show();
    }
});

