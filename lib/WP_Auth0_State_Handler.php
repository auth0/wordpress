<?php

final class WP_Auth0_State_Handler {

  /**
   * Cookie name used to store unique state value
   *
   * @var string
   */
  const COOKIE_NAME = 'auth0_uniqid';

  /**
   *
   */
  const COOKIE_EXPIRES = MINUTE_IN_SECONDS;

  /**
   * Singleton class instance
   *
   * @var WP_Auth0_State_Handler|null
   */
  private static $_instance = null;

  /**
   * State nonce stored in a cookie
   *
   * @var string
   */
  private $_uniqid;

  /**
   * WP_Auth0_State_Handler constructor
   * Private to prevent new instances of this class
   */
  private function __construct() {
    $this->init();
  }

  /**
   * Private to prevent cloning
   */
  private function __clone() {}

  /**
   * Private to prevent serializing
   */
  private function __sleep() {}

  /**
   * Private to prevent unserializing
   */
  private function __wakeup() {}

  /**
   * Start-up process to make sure we have a unique ID stored
   */
  private function init() {
    if ( isset( $_COOKIE[ self::COOKIE_NAME ] ) ) {
      // Have a state cookie, don't want to generate a new one
      $this->_uniqid = $_COOKIE[ self::COOKIE_NAME ];
    } else {
      // No state cookie, need to create one
      $this->_uniqid = $this->generateNonce();
    }
  }

  /**
   * Get the internal instance of the singleton
   *
   * @return WP_Auth0_State_Handler
   */
  public static final function getInstance() {
    if ( null === self::$_instance ) {
      self::$_instance = new WP_Auth0_State_Handler();
    }
    return self::$_instance;
  }

  /**
   * Return the unique ID used for state validation
   *
   * @return string
   */
  public function get() {
    return $this->_uniqid;
  }

  /**
   * Check if the stored state matches a specific value
   *
   * @param $state
   *
   * @return bool
   */
  public function validate( $state ) {
    $valid = isset( $_COOKIE[ self::COOKIE_NAME ] ) ? $_COOKIE[ self::COOKIE_NAME ] === $state : FALSE;
    $this->reset();
    return $valid;
  }

  /**
   * Set the state cookie value
   *
   * @return bool
   */
  public function setCookie() {
    $_COOKIE[ self::COOKIE_NAME ] = $this->_uniqid;
    return setcookie( self::COOKIE_NAME, $this->_uniqid, time() + self::COOKIE_EXPIRES );
  }

  /**
   * Reset the state cookie value
   *
   * @return bool
   */
  public function reset() {
    return setcookie( self::COOKIE_NAME, '', 0 );
  }

  /**
   * Generate a pseudo-random ID (not cryptographically secure)
   *
   * @see https://stackoverflow.com/a/1846229/728480
   *
   * @return string
   */
  public function generateNonce() {
    return md5( uniqid( rand(), true ) );
  }
}