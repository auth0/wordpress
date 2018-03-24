/* globals jQuery, console, alert, wpAuth0PwlGlobal, Auth0LockPasswordless */
jQuery(document).ready(function ($) {

    var formWrapper = $( '#form-signin-wrapper' );
    var modalButton = $( '#a0LoginButton' );

    // Missing critical Auth0 settings
    if ( ! wpAuth0PwlGlobal.lock.ready ) {
        formWrapper.hide();
        $( '#loginform' ).show();
        $( '#login' ).find( 'h1' ).show();
        return;
    }

    // General Lock options pulled from the settings
    var options = wpAuth0PwlGlobal.lock.options;

    // Extra options are set in the shortcode and widget
    var extraOptions = formWrapper.attr( 'data-auth0-opts' );

    if ( extraOptions ) {
        try {
            extraOptions = JSON.parse( extraOptions );
            $.extend( options, extraOptions );
        } catch ( err ) {
            // TODO: better handling
            console.log( err.message );
        }
    }

    var Lock = new Auth0LockPasswordless(
        wpAuth0PwlGlobal.lock.clientId,
        wpAuth0PwlGlobal.lock.domain,
        options
    );

    if ( extraOptions.show_as_modal ) {
        modalButton.click( Lock.show );
    } else {
        Lock.show();
    }
});

