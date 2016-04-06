'use strict';

var util = require('util');
var request = require('request');

module.exports = function(ctx, cb) {
    var params = ctx.query;

    request({
      url: params.domain + '/.well-known/oauth2-client-configuration',
      method: 'GET',
      json: true
    }, function(err, res, body) {
      if (err || res.statusCode !== 200) { 
        return cb ('error');
      }
      return cb (null, body);
    });
};