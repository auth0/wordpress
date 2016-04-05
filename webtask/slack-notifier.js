'use strict';

var util = require('util');

/**
 * @param {secret} SLACK_WEBHOOK_URL
 * @param {secret} SLACK_CHANNEL_NAME
 */
module.exports = function(ctx, cb) {
    var params = ctx.body;

    if (!ctx.secrets.SLACK_WEBHOOK_URL || !ctx.secrets.SLACK_CHANNEL_NAME) {
        return cb(new Error('"SLACK_WEBHOOK_URL" and "SLACK_CHANNEL_NAME" parameters required'));
    }

    if (!params.feedback || !params.account || !params.score) {
        return cb(new Error('"feedback" parameter required'));
    }

    var SLACK_WEBHOOK_URL = ctx.secrets.SLACK_WEBHOOK_URL;
    var SLACK_CHANNEL_NAME = ctx.secrets.SLACK_CHANNEL_NAME;
    var slack = require('slack-notify')(SLACK_WEBHOOK_URL);
    var score = params.score;
    var account = params.account;
    var feedback = params.feedback;

    var emoji = '';

    if (score == 1) {
      emoji = ':broken_heart:';
    } else if (score >= 2 && score <= 3) {
      emoji = ':yellow_heart:';
    } else if (score == 4) {
      emoji = ':blue_heart:';
    } else if (score == 5) {
      emoji = ':heart:';
    }

    var message =  util.format( emoji + ' Feedback from `%s` score %d: ```%s```', account, score, feedback);

    slack.send({
      channel: SLACK_CHANNEL_NAME,
      icon_emoji: ':robot_face:',
      text: message,
      unfurl_links: 0,
      username: 'wp-a0-feedback'
    })

    cb();
};