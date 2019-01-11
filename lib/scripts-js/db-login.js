/* globals require */

function login (email, password, callback) {

  var request = require('request');

  request.post(
    // The string below should be replaced with the WP site's migration URL like:
    // https://yourdomain.com/index.php?a0_action=migration-ws-login
    '{THE_WS_URL}',
    {
      form: {
        username:     email,
        password:     password,
        // The string below should be replaced with the migration token found in the plugin settings.
        access_token: '{THE_WS_TOKEN}'
      }
    },
    function(error, response, body) {

      // Error encountered during HTTP request, exit.
      if (error) {
        return callback(error);
      }

      var wpUser = JSON.parse(body);

      // Error returned from WordPress, exit.
      if (wpUser.error) {
        callback(null);
      }

      // Use WordPress profile data to populate Auth0 account.
      var profile = {
        user_id:        wpUser.data.ID,
        username:       wpUser.data.user_login,
        email:          wpUser.data.user_email,
        name:           wpUser.data.display_name,
        email_verified: true
      };

      callback(null, profile);
    });
}
