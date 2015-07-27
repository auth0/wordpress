function Auth0Amplify(provider, page_url) {

	var data = {
		'action': 'auth0_amplificator',
		'provider': provider,
		'page_url': page_url
	};

	jQuery.post(auth0_ajax.ajax_url, data, function(response) {

	});

}
