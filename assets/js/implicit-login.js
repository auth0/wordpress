/* global wpAuth0ImplicitGlobal */

document.addEventListener('DOMContentLoaded', function() {
    var hash = window.location.hash;
    if ( ! hash ) {
        redirectWithoutQuery();
        return;
    }

    if ( hash[0] === '#' ) {
        hash = hash.slice(1);
    }

    var data = hash.split('&').reduce(function( p, c ) {
        var parts = c.split('=');
        p[parts[0]] = parts[1];
        return p;
    }, {});

    var form = document.createElement( 'form' );
    form.setAttribute( 'method', 'post' );
    form.setAttribute( 'action', wpAuth0ImplicitGlobal.postUrl );

    if ( data.hasOwnProperty( 'error' ) ) {
        form.appendChild( createHiddenField( 'error', data.error ) );
        form.appendChild( createHiddenField( 'error_description', data.error_description ) );
    } else if ( data.hasOwnProperty( 'id_token' ) && data.hasOwnProperty( 'state' ) ) {
        form.appendChild( createHiddenField( 'token', data.id_token ) );
        form.appendChild( createHiddenField( 'state', data.state ) );
    } else {
        redirectWithoutQuery();
        return;
    }

    document.body.appendChild( form );
    form.submit();

    /**
     * Redirect to current page without URL parameters
     */
    function redirectWithoutQuery() {
        var redirectTo = window.location.href;
        redirectTo = redirectTo.replace( window.location.search, '' );
        window.location.replace( redirectTo );
    }

    /**
     * Return a hidden field node with a specific name=value
     *
     * @param name
     * @param value
     */
    function createHiddenField( name, value ) {
        var newNode = document.createElement( 'input' );
        newNode.setAttribute( 'type', 'hidden' );
        newNode.setAttribute( 'name', name );
        newNode.setAttribute( 'value', value );
        return newNode;
    }
});