# Change Log

## [1.2.2](https://github.com/auth0/wp-auth0/tree/1.2.2) (2015-05-19)

[Full Changelog](https://github.com/auth0/wp-auth0/compare/1.2.1...1.2.2)

**Implemented enhancements:**

- Auto Login \(no widget\) Does not work with WooCommerce My Account Login [\#45](https://github.com/auth0/wp-auth0/issues/45)

**Merged pull requests:**

- Added support for WooCommerce + updated info headers [\#63](https://github.com/auth0/wp-auth0/pull/63) ([glena](https://github.com/glena))

## [1.2.1](https://github.com/auth0/wp-auth0/tree/1.2.1) (2015-05-14)

**Implemented enhancements:**

- Auth0 users with different accounts but same username will not be able to log into the site [\#52](https://github.com/auth0/wp-auth0/issues/52)

- Error: Could not create user. The registration process is not available. [\#48](https://github.com/auth0/wp-auth0/issues/48)

- Enhancement: Allow WordPress plugin to work in enterprise environment without internet access [\#42](https://github.com/auth0/wp-auth0/issues/42)

- Support redirecting to arbitrary URLs after login is succesful [\#29](https://github.com/auth0/wp-auth0/issues/29)

- Add link to create Auth0 Account [\#28](https://github.com/auth0/wp-auth0/issues/28)

- Validate settings before saving [\#24](https://github.com/auth0/wp-auth0/issues/24)

- Show WP Auth0 Logs somewhere so that we can easily diagnose problems [\#22](https://github.com/auth0/wp-auth0/issues/22)

- Add option to enter custom CSS [\#21](https://github.com/auth0/wp-auth0/issues/21)

- Make Widget options accessable by the Plugin [\#15](https://github.com/auth0/wp-auth0/issues/15)

- Make the widget showable as Shortcode and Widget [\#14](https://github.com/auth0/wp-auth0/issues/14)

**Fixed bugs:**

- No widget shown in latest release [\#20](https://github.com/auth0/wp-auth0/issues/20)

**Closed issues:**

- SDK Client headers spec compliant [\#61](https://github.com/auth0/wp-auth0/issues/61)

- Make usernames unique if it is already in use [\#58](https://github.com/auth0/wp-auth0/issues/58)

- Check text on "allow signup" option in plugin settings [\#54](https://github.com/auth0/wp-auth0/issues/54)

- Why is Client Secret Needed [\#53](https://github.com/auth0/wp-auth0/issues/53)

- Client Secret Field in Settings should not be remembered by browser [\#44](https://github.com/auth0/wp-auth0/issues/44)

- Demo is down [\#43](https://github.com/auth0/wp-auth0/issues/43)

- wp-login?wle does not work when "Auto Login \(no widget\)" is enabled [\#38](https://github.com/auth0/wp-auth0/issues/38)

- Add fallback URL to log in with WP credentials even after disabling WP login [\#35](https://github.com/auth0/wp-auth0/issues/35)

- Wordpress login no longer works when the "Auto Login \(no widget\)" option is set. [\#34](https://github.com/auth0/wp-auth0/issues/34)

- Shortcode attributes are being ignored [\#33](https://github.com/auth0/wp-auth0/issues/33)

- Update to Lock [\#19](https://github.com/auth0/wp-auth0/issues/19)

- errors not being shown when something fails [\#18](https://github.com/auth0/wp-auth0/issues/18)

- add nice error message when exchange of token returns 401 [\#11](https://github.com/auth0/wp-auth0/issues/11)

- Don't show widget when registrations are not allowed. [\#5](https://github.com/auth0/wp-auth0/issues/5)

- Auto-create users option [\#4](https://github.com/auth0/wp-auth0/issues/4)

- plugin packaging and publish [\#3](https://github.com/auth0/wp-auth0/issues/3)

- after session times out the login widget is shown inside the iframe and after login the site is embedded in the iframe [\#2](https://github.com/auth0/wp-auth0/issues/2)

- lost your password [\#1](https://github.com/auth0/wp-auth0/issues/1)

**Merged pull requests:**

- Updated info headers [\#62](https://github.com/auth0/wp-auth0/pull/62) ([glena](https://github.com/glena))

- Auth WP V1.2 [\#55](https://github.com/auth0/wp-auth0/pull/55) ([glena](https://github.com/glena))

- Security vulnerability fix on login [\#51](https://github.com/auth0/wp-auth0/pull/51) ([glena](https://github.com/glena))

- Add fallback URL to log in with WP credentials even after disabling WP login \#35 [\#36](https://github.com/auth0/wp-auth0/pull/36) ([glena](https://github.com/glena))

- Issues \#24, \#28 & \#29 [\#30](https://github.com/auth0/wp-auth0/pull/30) ([glena](https://github.com/glena))

-  Add option to enter custom CSS \#21 [\#27](https://github.com/auth0/wp-auth0/pull/27) ([glena](https://github.com/glena))

- Issues ready to merge [\#26](https://github.com/auth0/wp-auth0/pull/26) ([glena](https://github.com/glena))

- New popup widget & some small changes [\#23](https://github.com/auth0/wp-auth0/pull/23) ([glena](https://github.com/glena))

- A0 widget [\#16](https://github.com/auth0/wp-auth0/pull/16) ([glena](https://github.com/glena))

- New feature: Add a new config to allow people to access with the standar... [\#13](https://github.com/auth0/wp-auth0/pull/13) ([glena](https://github.com/glena))

- Fix wp submision problems [\#10](https://github.com/auth0/wp-auth0/pull/10) ([hrajchert](https://github.com/hrajchert))

- Added screenshots [\#9](https://github.com/auth0/wp-auth0/pull/9) ([hrajchert](https://github.com/hrajchert))

- Many improvements [\#8](https://github.com/auth0/wp-auth0/pull/8) ([hrajchert](https://github.com/hrajchert))

- Must check if user exists. [\#56](https://github.com/auth0/wp-auth0/pull/56) ([singularityjay](https://github.com/singularityjay))

- Fixed logging into an existing user when existing username exists \(on user creation\) [\#47](https://github.com/auth0/wp-auth0/pull/47) ([singularityjay](https://github.com/singularityjay))

- Fix sql session [\#41](https://github.com/auth0/wp-auth0/pull/41) ([glena](https://github.com/glena))

- Add title to back link [\#40](https://github.com/auth0/wp-auth0/pull/40) ([felixinx](https://github.com/felixinx))

- Fixes shortcodes \(\#33\) and Wordpress login \(\#34\) [\#32](https://github.com/auth0/wp-auth0/pull/32) ([sandrinodimattia](https://github.com/sandrinodimattia))

- Many improvements [\#7](https://github.com/auth0/wp-auth0/pull/7) ([hrajchert](https://github.com/hrajchert))

- Improvements in the plugin [\#6](https://github.com/auth0/wp-auth0/pull/6) ([hrajchert](https://github.com/hrajchert))



\* *This Change Log was automatically generated by [github_changelog_generator](https://github.com/skywinder/Github-Changelog-Generator)*