function Auth0Amplify(ele, provider, page_url) {

	var data = {
		'action': 'auth0_amplificator',
		'provider': provider,
		'page_url': page_url
	};

	ele = jQuery(ele);
	var name = ele.find('span').html();
	ele.find('span').html('Sharing...');

	jQuery.post(auth0_ajax.ajax_url, data, function(response) {

		if (response.success) {
			ele.find('span').html('Done!');

			setTimeout(function(){
				ele.find('span').html(name);
			}, 500);
		}
		else {
			ele.find('span').html(name);
			alert('There was an error sharing with ' + name + "\n" + response.message);
		}

	}, 'json');

}
