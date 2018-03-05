<?php

class WP_Auth0_State_Handler {

  /**
   * @var string
   */
  protected $uniqid;

  /**
   * @var string
   */
  const COOKIE_NAME = 'auth0_uniqid';

  /**
   * @var int
   */
  protected $cookieExpiresIn = MINUTE_IN_SECONDS;

  /**
   * WP_Auth0_State_Handler constructor.
   */
  public function __construct() {
    $this->uniqid = self::generateNonce();
  }

  /**
   * Set the unique ID for state and return
   *
   * @return string
   */
  public function issue() {
    $this->store();
    return $this->uniqid;
  }

  /**
   * Set the state cookie value
   *
   * @return bool
   */
  protected function store() {
    return setcookie( self::COOKIE_NAME, $this->uniqid, time() + $this->cookieExpiresIn );
  }

  /**
   * Check if the stored state matches a specific value
   *
   * @param $state
   *
   * @return bool
   */
  public static function validate( $state ) {
    $valid = isset( $_COOKIE[ self::COOKIE_NAME ] ) ? $_COOKIE[ self::COOKIE_NAME ] === $state : FALSE;
    self::reset();
    return $valid;
  }

  /**
   * Reset the state cookie value
   *
   * @return bool
   */
  public static function reset() {
    return setcookie( self::COOKIE_NAME, '', 0 );
  }

  /**
   * Generate a pseudo-random ID (not cryptographically secure)
   *
   * @see https://stackoverflow.com/a/1846229/728480
   *
   * @return string
   */
  public static function generateNonce() {
    return md5( uniqid( rand(), true ) );
  }
}