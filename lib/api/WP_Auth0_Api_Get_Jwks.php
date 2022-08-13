<?php
/**
 * Contains WP_Auth0_Api_Get_Jwks.
 *
 * @package WP-Auth0
 *
 * @since 4.0.0
 */

/**
 * Class WP_Auth0_Api_Get_Jwks
 */
class WP_Auth0_Api_Get_Jwks extends WP_Auth0_Api_Abstract
{
    /**
     * Default value to return on failure.
     */
    public const RETURN_ON_FAILURE = [];

    /**
     * Make the JWKS call, and handle the response.
     *
     * @return array
     */
    public function call()
    {
        return $this
            ->set_path('.well-known/jwks.json')
            ->get()
            ->handle_response(__METHOD__);
    }

    /**
     * Handle API response.
     *
     * @param string $method - Method that called the API.
     *
     * @return array
     */
    protected function handle_response($method)
    {
        if ($this->handle_wp_error($method)) {
            return self::RETURN_ON_FAILURE;
        }

        if ($this->handle_failed_response($method)) {
            return self::RETURN_ON_FAILURE;
        }

        return json_decode($this->response_body, true);
    }
}
