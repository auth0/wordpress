function (user, context, callback) {

    var ENABLED_ON_CLIENTS = ['KNuydwEqwGsPNpxdAhACmOWDUmBEZsLn']; // HAVE TO CHAGE
    if (ENABLED_ON_CLIENTS.indexOf(context.clientID) === -1) {
        return callback(null, user, context);
    }

    var REDIRECT_TO = 'https://webtask.it.auth0.com/api/run/wt-5491169046745-0/tenantcreation?webtask_no_cache=1&token=';
    var ISSUER = 'https://wptest.auth0.com/';// HAVE TO CHAGE to auth0.auth0.com
    var CLIENT_ID = context.clientID; // HAVE TO CHAGE
    var CLIENT_SECRET = 'somerandomsecret'; // HAVE TO CHAGE

    has_tenant(user.user_id, function(){
        return callback(null, user, context);
    }, create_tenant_flow);

    function create_tenant_flow() {
        if (context.protocol !== 'redirect-callback') {

            var token = createToken(CLIENT_ID, CLIENT_SECRET, ISSUER, {
              sub: user.user_id,
              email: user.email,
              ip: context.request.ip
            });

            context.redirect = {
              url: REDIRECT_TO + token
            };

            return callback(null, user, context);
        } else {

            var tenant_name = context.request.query.tenant_name;
            var region = context.request.query.region;

            verifyToken(CLIENT_ID, CLIENT_SECRET, ISSUER, context.request.query.token, function(err, decoded) {

                if (err) {
                    return callback(new UnauthorizedError('Invalid token.'));
                } else if (decoded.sub !== user.user_id) {
                    return callback(new UnauthorizedError('Token does not match the current user.'));
                }
                else {
                    create_tenant(user, region, tenant_name,function(){
                        callback(null, user, context);
                    },function(err){
                        callback(err);
                    });
                }

            });

        }
    }

    function create_tenant(user, region, tenant_name, success, error) {
        user.app_metadata = user.app_metadata || {};
        user.app_metadata.tenant = tenant_name;
        user.app_metadata.region = region;

        auth0.users.updateAppMetadata(user.user_id, user.app_metadata)
            .then(success)
            .catch(error);
    }

    function has_tenant(user_id, cb_yes, cb_no) {
        user.app_metadata = user.app_metadata || {};
        if (user.app_metadata.tenant) {
            cb_yes();
        } else {
            cb_no();
        }
    }

  // Generate a JWT.
  function createToken(client_id, client_secret, issuer, user) {
    var options = {
      expiresInMinutes: 5,
      audience: client_id,
      issuer: issuer
    };

    var token = jwt.sign(user,
      new Buffer(client_secret, 'base64'), options);
    return token;
  }

  // Verify a JWT.
  function verifyToken(client_id, client_secret, issuer, token, cb) {
    var secret = new Buffer(client_secret, 'base64').toString('binary');
    var token_description = { audience: client_id, issuer: issuer };

    jwt.verify(token, secret, token_description, cb);
  }
}
