function Auth0Amplify(provider) {
	
	var data = {
		'action': 'auth0_amplificator',
		'provider': provider
	};

	jQuery.post(auth0_ajax.ajax_url, data, function(response) {

	});

}