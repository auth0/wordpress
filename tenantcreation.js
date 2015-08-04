//wt create tenantcreation.js --no-merge --no-parse
var validRegions = [ 'us', 'eu' ];

var Express = require('express');
var Webtask = require('webtask-tools');
var bodyParser = require('body-parser');

var app = Express();

app.use(bodyParser.urlencoded({ extended: false }));

// POST
app.post('/', function (req, res) {
    var token = req.body.token;
    var tenant_name = req.body.tenant_name;
    var region = req.body.region;
    var messages = [];

    if ( ! tenant_name ) {
        messages.push('Please complete the tenant name.');
    }

    if ( ! region ) {
        messages.push('Please select a region.');
    } else if ( ! validRegions.indexOf(region) === -1 ) {
        messages.push('The region is not valid.');
    }

    if ( messages.length === 0 ) {
        res.redirect('https://wptest.auth0.com/continue?tenant_name=' + tenant_name + '&region=' + region + '&token=' + token);
    } else {
        render_form(res, token, messages);
    }
});

// GET
app.get('/', function (req, res) {
    var token = req.query.token;
    render_form(res, token);
});

function render_form(res, token, messages) {
    res.writeHead(200, { 'Content-Type': 'text/html '});
    res.end(require('ejs').render(view_form.stringify(), {
            token: token,
            messages: messages,
            validRegions: validRegions
    }));
}

function view_form() {/*
<html>
    <body>
        <form action="" method="POST" enctype="application/x-www-form-urlencoded">

        <% if (messages) { %>
            <ul>

            <% for(var i=0; i<messages.length; i++) {%>
                <li style="color:red;"><%= messages[i] %></li>
            <% } %>

            </ul>
        <% } %>

            <input type="text" name="tenant_name" value="" />
            <input type="hidden" name="token" value="<%= token %>" />

            <select name="region">
            <% for(var i=0; i<validRegions.length; i++) {%>
                <option value="<%= validRegions[i] %>"><%= validRegions[i] %></option>
            <% } %>
            </select>

            <input type="submit" name="create" value="Create" />
        </form>
    </body>
</html>
*/}

module.exports = Webtask.fromExpress(app);
