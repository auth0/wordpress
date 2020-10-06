<?php

// Use the Management API for user profile data.
add_filter( 'auth0_use_management_api_for_userinfo', '__return_true', 100 );

// Use the ID token for user profile data (recommended).
add_filter( 'auth0_use_management_api_for_userinfo', '__return_false', 101 );
