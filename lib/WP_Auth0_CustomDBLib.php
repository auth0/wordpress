<?php

class WP_Auth0_CustomDBLib {

  public static $login_script = '
function login (email, password, callback) {

  var request = require("request");

  request.post("{THE_WS_URL}", {
    form:{username:email, password:password, access_token:"{THE_WS_TOKEN}"},
  }, function(error, response, body){

    if ( error ) {
      return callback(error);
    }

    var info = JSON.parse(body);
    
    if (info.error) {
      callback();
    } else {
      var profile = {
        user_id:     info.data.ID,
        username:    info.data.user_login,
        email_verified: true,
        email:       info.data.user_email,
        name:        info.data.display_name
      };

      callback(null, profile);
    }
   
  });
}
';

  public static $get_user_script = '
function getByEmail (email, callback) {

  var request = require("request");
  
  request.post("{THE_WS_URL}", {
    form:{username:email, access_token:"{THE_WS_TOKEN}"},
  }, function(error, response, body){

    if ( error ) {
      return callback(error);
    }

    var info = JSON.parse(body);

    if (info.error) { 
      callback(null);
    } else {
      var profile = {
        user_id:     info.data.ID,
        username:    info.data.user_login,
        email:       info.data.user_email,
        name:        info.data.display_name,
        email_verified: true
      };

      callback(null, profile);
    } 

  });
}
';

}
