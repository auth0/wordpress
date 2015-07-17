<?php

class WP_Auth0_RulesLib {

    public static $google_MFA = array(
        'name' => 'Multifactor-Google-Authenticator-Do-Not-Rename',
        'script' => "
function (user, context, callback) {
  var CLIENTS_WITH_MFA = ['REPLACE_WITH_YOUR_CLIENT_ID'];
  // run only for the specified clients
  if (CLIENTS_WITH_MFA.indexOf(context.clientID) !== -1) {
    // uncomment the following if clause in case you want to request a second factor only from user's that have user_metadata.use_mfa === true
    // if (user.user_metadata && user.user_metadata.use_mfa){
      context.multifactor = {
        provider: 'google-authenticator',
        // issuer: 'Label on Google Authenticator App', // optional
        // key: '{YOUR_KEY_HERE}', //  optional, the key to use for TOTP. by default one is generated for you
        // ignoreCookie: true // optional, force Google Authenticator everytime this rule runs. Defaults to false. if accepted by users the cookie lasts for 30 days (this cannot be changed)
      };
    // }
  }
  callback(null, user, context);
}"
    );

    public static $geo = array(
        'name' => 'Store-Geo-Location-Do-Not-Rename',
        'script' => "
function (user, context, callback) {
  user.user_metadata = user.user_metadata || {};
  user.user_metadata.geoip = context.request.geoip;
  auth0.users.updateUserMetadata(user.user_id, user.user_metadata)
    .then(function(){
      callback(null, user, context);
    })
    .catch(function(err){
      callback(err);
    });
}"
    );

    public static $fullcontact = array(
        'name' => 'Enrich-profile-with-FullContact-Do-Not-Rename',
        'script' => "
function (user, context, callback) {

  var fullContactAPIKey = 'REPLACE_WITH_YOUR_CLIENT_ID';

  if(!user.email) {
    //the profile doesn't have email so we can't query fullcontact api.
    return callback(null, user, context);
  }

  request({
    url: 'https://api.fullcontact.com/v2/person.json',
    qs: {
      email:  user.email,
      apiKey: fullContactAPIKey
    }
  }, function (e,r,b) {
    if(e) return callback(e);

    if(r.statusCode===200){
      user.user_metadata = user.user_metadata || {};
      user.user_metadata.fullContactInfo = JSON.parse(b);

      auth0.users.updateUserMetadata(user.user_id, user.user_metadata)
        .then(function(){
          callback(null, user, context);
        })
        .catch(function(err){
          callback(err);
        });
    }
  });
}"
    );
}
