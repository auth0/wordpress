<?php

class WP_Auth0_Api_Client {

    public static function get_token($domain, $client_id, $client_secret, $grantType = 'client_credentials')
    {
        $endpoint = "https://" . $domain . "/";
        $body = array(
            'client_id' => $client_id,
            'client_secret' =>$client_secret,
            'grant_type' => $grantType
        );

        $headers = array(
            'content-type' => 'application/x-www-form-urlencoded'
        );


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

} 