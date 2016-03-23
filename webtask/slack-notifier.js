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
    var message =  util.format('Feedback from `%s` score %d: ```%s```', account, score, feedback);

    var emoji = '';

    if (score >= 0 && score <= 2) {
      emoji = ':broken_heart:';
    } else if (score >= 3 && score <= 5) {
      emoji = ':yellow_heart:';
    } else if (score >= 6 && score <= 8) {
      emoji = ':blue_heart:';
    } else if (score >= 9 && score <= 10) {
      emoji = ':heart:';
    }
console.log(emoji);
    slack.send({
      channel: SLACK_CHANNEL_NAME,
      icon_emoji: emoji,
      text: message,
      unfurl_links: 0,
      username: 'wp-a0-feedback'
    })

    cb();
};