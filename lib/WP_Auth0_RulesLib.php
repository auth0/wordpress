<?php

class WP_Auth0_RulesLib {

	public static $disable_social_signup = array(

		'name' => 'Disable-Social-Signup-Do-Not-Rename',
		'script' => "
function (user, context, callback) {

  var CLIENTS_ENABLED = ['REPLACE_WITH_YOUR_CLIENT_ID'];
  // run only for the specified clients
  if (CLIENTS_ENABLED.indexOf(context.clientID) === -1) {
    return callback(null, user, context);
  }

  // initialize app_metadata
  user.app_metadata = user.app_metadata || {};

  // if it is the first login (hence the `signup`) and it is a social login
  if (context.stats.loginsCount === 1 && user.identities[0].isSocial) {

    // turn on the flag
    user.app_metadata.is_signup = true;

    // store the app_metadata
    auth0.users.updateAppMetadata(user.user_id, user.app_metadata)
      .then(function(){
        // throw error
        return callback('Signup disabled');
      })
      .catch(function(err){
        callback(err);
      });

    return;
  }

  // if flag is enabled, throw error
  if (user.app_metadata.is_signup) {
    return callback('Signup disabled');
  }

  // else it is a non social login or it is not a signup
  callback(null, user, context);
}
" );
  
	public static $link_accounts = array(

		'name' => 'Account-Linking-Do-Not-Rename',
		'script' => "
function (user, context, callback) {

  var CLIENTS_ENABLED = ['REPLACE_WITH_YOUR_CLIENT_ID'];
  // run only for the specified clients
  if (CLIENTS_ENABLED.indexOf(context.clientID) === -1) {
    return callback(null, user, context);
  }

  // Check if email is verified, we shouldn't automatically
  // merge accounts if this is not the case.
  if (!user.email_verified) {
    return callback(null, user, context);
  }

  var request = require('request');
  var async = require('async');

  var userApiUrl = auth0.baseUrl + '/users';
  request({
   url: userApiUrl,
   headers: {
     Authorization: 'Bearer ' + auth0.accessToken
   },
   qs: {
     search_engine: 'v2',
     q: 'email:\"' + user.email + '\" -user_id:\"' + user.user_id + '\"',
   }
  },
  function(err, response, body) {
    if (err) return callback(err);
    if (response.statusCode !== 200) return callback(new Error(body));

    var data = JSON.parse(body);
    if (data.length > 0) {
      async.each(data, function(targetUser, cb) {
        if (targetUser.email_verified) {
          var aryTmp = targetUser.user_id.split('|');
          var provider = aryTmp[0];
          var targetUserId = aryTmp[1];
          request.post({
            url: userApiUrl + '/' + user.user_id + '/identities',
            headers: {
              Authorization: 'Bearer ' + auth0.accessToken
            },
            json: { provider: provider, user_id: targetUserId }
          }, function(err, response, body) {
              if (response.statusCode >= 400) {
               cb(new Error('Error linking account: ' + response.statusMessage));
              }
            cb(err);
          });
        } else {
          cb();
        }
      }, function(err) {
        callback(err, user, context);
      });
    } else {
      callback(null, user, context);
    }
  });
}"
	);

  public static $guardian_MFA = array(
    'name' => 'Multifactor-Guardian-Do-Not-Rename',
    'script' => "
function (user, context, callback) {
  var CLIENTS_ENABLED = ['REPLACE_WITH_YOUR_CLIENT_ID'];
  // run only for the specified clients
  if (CLIENTS_ENABLED.indexOf(context.clientID) !== -1) {
    // uncomment the following if clause in case you want to request a second factor only from user's that have user_metadata.use_mfa === true
    // if (user.user_metadata && user.user_metadata.use_mfa){
      context.multifactor = {
        provider: 'guardian',
        ignoreCookie: true, // optional. Force Auth0 MFA everytime this rule runs. Defaults to false. if accepted by users the cookie lasts for 30 days (this cannot be changed)
      };
    // }
  }
  callback(null, user, context);
}"
  );

	public static $google_MFA = array(
		'name' => 'Multifactor-Google-Authenticator-Do-Not-Rename',
		'script' => "
function (user, context, callback) {
  var CLIENTS_ENABLED = ['REPLACE_WITH_YOUR_CLIENT_ID'];
  // run only for the specified clients
  if (CLIENTS_ENABLED.indexOf(context.clientID) !== -1) {
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

  var CLIENTS_ENABLED = ['REPLACE_WITH_YOUR_CLIENT_ID'];
  // run only for the specified clients
  if (CLIENTS_ENABLED.indexOf(context.clientID) === -1) {
    return callback(null, user, context);
  }

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

  var CLIENTS_ENABLED = ['REPLACE_WITH_YOUR_CLIENT_ID'];
  // run only for the specified clients
  if (CLIENTS_ENABLED.indexOf(context.clientID) === -1) {
    return callback(null, user, context);
  }

  var fullContactAPIKey = 'REPLACE_WITH_YOUR_FULLCONTACT_API_KEY';

  if(!user.email) {
    //the profile doesn't have email so we can't query fullcontact api.
    return callback(null, user, context);
  }

  var request = require('request');

  request({
    url: 'https://api.fullcontact.com/v2/person.json',
    qs: {
      email:  user.email,
      apiKey: fullContactAPIKey
    }
  }, function (e,r,b) {
    if(e) return callback(e);

    if(r.statusCode===200) {
      user.user_metadata = user.user_metadata || {};
      user.user_metadata.fullContactInfo = JSON.parse(b);

      auth0.users.updateUserMetadata(user.user_id, user.user_metadata)
        .then(function(){
          callback(null, user, context);
        })
        .catch(function(err){
          callback(err);
        });
    } else {
      callback(null, user, context);
    }
  });
}"
	);

	public static $income = array(
		'name' => 'Enrich-profile-with-Zipcode-Income-Do-Not-Rename',
		'script' => "
function (user, context, callback) {

    var CLIENTS_ENABLED = ['REPLACE_WITH_YOUR_CLIENT_ID'];
    // run only for the specified clients
    if (CLIENTS_ENABLED.indexOf(context.clientID) === -1) {
      return callback(null, user, context);
    }

    user.user_metadata = user.user_metadata || {};
    var geoip = user.user_metadata.geoip || context.request.geoip;
    var request = require('request');

    if (!geoip || geoip.country_code !== 'US') return callback(null, user, context);

    if(global.incomeData === undefined) {
        retrieveIncomeData(user, geoip, context, callback);
    } else {
        setIncomeData(global.incomeData, user, geoip, context, callback);
    }


    function retrieveIncomeData(user, geoip, context, callback) {
        request({
            url: 'http://cdn.auth0.com/zip-income/householdincome.json'
        }, function (e,r,b) {
            if(e) return callback(e);
            if(r.statusCode===200){
                var incomeData = JSON.parse(b);
                global.incomeData = incomeData;
                setIncomeData(incomeData,user, geoip,context, callback);
            } else {
              callback(null, user, context);
            }
        });
    }

    function setIncomeData(incomeData, user, geoip, context, callback) {
        if (incomeData[geoip.postal_code]) {
            user.user_metadata.zipcode_income = incomeData[geoip.postal_code];
            auth0.users.updateUserMetadata(user.user_id, user.user_metadata)
                .then(function(){
                    callback(null, user, context);
                })
                .catch(function(err){
                    callback(err);
                });
        } else {
          callback(null, user, context);
        }
    }
}"
	);
}
