<?php

class WP_Auth0_CustomDBLib {

  public static $login_script = '
  function login (email, password, callback) {

    request.post("{THE_WS_URL}", {
      form:{username:email, password:password},
      headers:{"Authorization":"Bearer {THE_WS_TOKEN}"}
    }, function(error, response, body){

      if ( response.statusCode === 200) {
        var info = JSON.parse(body);
        
        if (info.error) {
          callback(new Error(info.error), null);
        } else {
          var profile = {
            user_id:     info.data.ID,
            nickname:    info.data.display_name,
            email:       info.data.user_email,
            name:        info.data.user_nicename,
          };

          callback(null, profile);
        }
        
      } else {
        callback(error || new Error("Error"), null);
      }

    });
}
';


  public static $get_user_script = '
  function getByEmail (email, callback) {

    request.post("{THE_WS_URL}", {
      form:{username:email},
      headers:{"Authorization":"Bearer {THE_WS_TOKEN}"}
    }, function(error, response, body){

      if (!error && response.statusCode === 200) {
        var info = JSON.parse(body);

        if (info.error) { 
          callback(null);
        } else {
          var profile = {
            user_id:     info.data.ID,
            nickname:    info.data.display_name,
            email:       info.data.user_email,
            name:        info.data.user_nicename,
          };

          callback(null, profile);
        } 

      } else {
        callback(error || new Error("Error"));
      }
    });
}
';

}
