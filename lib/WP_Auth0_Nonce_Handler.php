<?php

final class WP_Auth0_Nonce_Handler {

  /**
   * Cookie name used to store nonce
   *
   * @var string
   */
  const COOKIE_NAME = 'auth0_nonce';

  /**
   *
   */
  const COOKIE_EXPIRES = HOUR_IN_SECONDS;

  /**
   * Singleton class instance
   *
   * @var WP_Auth0_Nonce_Handler|null
   */
  private static $_instance = null;

  /**
   * Nonce stored in a cookie
   *
   * @var string
   */
  private $_uniqid;

  /**
   * WP_Auth0_Nonce_Handler constructor
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
   * Start-up process to make sure we have a nonce stored
   */
  private function init() {
    if ( isset( $_COOKIE[ self::COOKIE_NAME ] ) ) {
      // Have a nonce cookie, don't want to generate a new one
      $this->_uniqid = $_COOKIE[ self::COOKIE_NAME ];
    } else {
      // No nonce cookie, need to create one
      $this->_uniqid = $this->generateNonce();
    }
  }

  /**
   * Get the internal instance of the singleton
   *
   * @return WP_Auth0_Nonce_Handler
   */
  public static final function getInstance() {
    if ( null === self::$_instance ) {
      self::$_instance = new WP_Auth0_Nonce_Handler();
    }
    return self::$_instance;
  }

  /**
   * Return the unique ID used for nonce validation
   *
   * @return string
   */
  public function get() {
    return $this->_uniqid;
  }

  /**
   * Check if the stored nonce matches a specific value
   *
   * @param string $nonce - the nonce to validate against the stored value
   *
   * @return bool
   */
  public function validate( $nonce ) {
    $valid = isset( $_COOKIE[ self::COOKIE_NAME ] ) ? $_COOKIE[ self::COOKIE_NAME ] === $nonce : FALSE;
    $this->reset();
    return $valid;
  }

  /**
   * Set the nonce cookie value
   *
   * @return bool
   */
  public function setCookie() {
    $_COOKIE[ self::COOKIE_NAME ] = $this->_uniqid;
    return setcookie( self::COOKIE_NAME, $this->_uniqid, time() + self::COOKIE_EXPIRES, '/' );
  }

  /**
   * Reset the nonce cookie value
   *
   * @return bool
   */
  public function reset() {
    return setcookie( self::COOKIE_NAME, '', 0 );
  }

  /**
   * Generate a random ID
   * If using on PHP 7, it will be cryptographically secure
   *
   * @see https://secure.php.net/manual/en/function.random-bytes.php
   *
   * @param int $bytes - number of bytes to generate
   *
   * @return string
   */
  public function generateNonce( $bytes = 32 ) {
    $nonce_bytes = function_exists( 'random_bytes' ) ? random_bytes( $bytes ) : openssl_random_pseudo_bytes( $bytes );
    return bin2hex( $nonce_bytes );
  }
}