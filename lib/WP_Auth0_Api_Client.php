<?php

class WP_Auth0_Api_Client {

    protected static function get_info_headers() {
        global $wp_version;

        return array(
            'User-Agent' => 'PHP/' . phpversion(),
            'Auth0-Client', 'PHP/WordPress/' . $wp_version
        );
    }

    public static function get_token($domain, $client_id, $client_secret, $grantType = 'client_credentials', $extraBody = null)
    {
        if (!is_array($extraBody)) {
            $body = array();
        }
        else {
            $body = $extraBody;
        }

        $endpoint = "https://" . $domain . "/";

        $body['client_id'] = $client_id;
        $body['client_secret'] = $client_secret;
        $body['grant_type'] = $grantType;

        $headers = self::get_info_headers();
        $headers['content-type'] = 'application/x-www-form-urlencoded';


        $response = wp_remote_post( $endpoint . 'oauth/token', array(
            'headers' => $headers,
            'body' => $body
        ));

        if ($response instanceof WP_Error) {
            WP_Auth0::insertAuth0Error('WP_Auth0_Api_Client::get_token',$response);
            error_log($response->get_error_message());
        }

        return $response;

    }

    public static function get_user_info($domain, $access_token) {

        $endpoint = "https://" . $domain . "/";

        $headers = self::get_info_headers();

        return wp_remote_get( $endpoint . 'userinfo/?access_token=' . $access_token , array(
            'headers' => $headers
        ));

    }

} 