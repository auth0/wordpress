document.addEventListener('DOMContentLoaded', function() {
    var hash = window.location.hash;
    if ( ! hash || hash.indexOf('id_token') < 0 ) {
        return;
    }

    if (hash[0] === '#') {
        hash = hash.slice(1);
    }

    var data = hash.split('&').reduce(function( p, c ) {
        var parts = c.split('=');
        p[parts[0]] = parts[1];
        return p;
    }, {});

    var form = document.createElement('form');
    form.setAttribute('method', 'post');
    form.setAttribute('action', wpAuth0ImplicitGlobal.postUrl);

    var hiddenField = document.createElement('input');
    hiddenField.setAttribute('type', 'hidden');

    var tokenField = hiddenField.cloneNode();
    tokenField.setAttribute('name', 'token');
    tokenField.setAttribute('value', data.id_token);
    form.appendChild( tokenField );

    var stateField = hiddenField.cloneNode();
    stateField.setAttribute('name', 'state');
    stateField.setAttribute('value', data.state);
    form.appendChild( stateField );

    document.body.appendChild(form);
    form.submit();
});