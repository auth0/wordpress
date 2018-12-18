/* globals require */

function changePassword (email, newPassword, callback) {

  var request = require('request');

  request.post(
    // The string below should be replaced with the WP site's migration URL like:
    // https://yourdomain.com/index.php?a0_action=migration-ws-change-password
    '{THE_WS_URL}',
    {
      form: {
        username: email,
        password: newPassword,
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
        callback(new Error(wpUser.error));
      }

      // Password successfully changed.
      callback(null, true);
    });
}
