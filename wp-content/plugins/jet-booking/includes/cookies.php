<?php

namespace JET_ABAF;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Cookies manager
 */
class Cookies {

	public $key = 'jet_booking';

	public function __construct() {
	}

	/**
	 * Set.
	 *
	 * Set cookies value.
	 *
	 * @since  2.4.4
	 * @access public
	 *
	 * @param string $key   Cookies name.
	 * @param string $value Cookies value.
	 */
	public function set( $key, $value ) {

		$name   = $this->key . '_' . $key;
		$expire = time() + YEAR_IN_SECONDS;
		$secure = ( false !== strstr( get_option( 'home' ), 'https:' ) && is_ssl() );

		setcookie(
			$name,
			$value,
			$expire,
			COOKIEPATH ? COOKIEPATH : '/',
			COOKIE_DOMAIN,
			$secure,
			true
		);

		$_COOKIE[ $name ] = $value;

	}

	/**
	 * Get.
	 *
	 * Get cookies value.
	 *
	 * @since  2.4.4
	 * @access public
	 *
	 * @param string $key Cookies name.
	 *
	 * @return mixed
	 */
	public function get( $key ) {

		$name = $this->key . '_' . $key;

		if ( empty( $_COOKIE[ $name ] ) ) {
			$_COOKIE[ $name ] = '';
		}

		return isset( $_COOKIE[ $name ] ) ? $_COOKIE[ $name ] : false;

	}

}