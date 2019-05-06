/* globals require, configuration */

/**
 * This script will be executed each time a user attempts to login to a custom database.
 * This needs a global configuration option with the following properties:
 *    {string} endpointUrl - Site URL with an empty "a0_action" parameter appended.
 *    {string} migrationToken - Migration token found in the plugin settings
 *    {string} userNamespace - Formatted site name to avoid user ID overlapping.
 *
 * @param {string} email - User email address, provided on login.
 * @param {string} password - User password, provided on login.
 * @param {function} callback - Function to call when the script has completed.
 */
function login (email, password, callback) {

  var request = require('request');

  request.post(
    // The string below should be replaced with the WP site's migration URL like:
    // configuration.endpointUrl + 'migration-ws-login'
    configuration.endpointUrl + '{AUTH0_ACTION}',
    {
      form: {
        username:     email,
        password:     password,
        access_token: configuration.migrationToken
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
        user_id: configuration.userNamespace + '|' + wpUser.data.ID,
        username: wpUser.data.user_login,
        email: wpUser.data.user_email,
        name: wpUser.data.display_name,
        email_verified: true
      };

      callback(null, profile);
    });
}
