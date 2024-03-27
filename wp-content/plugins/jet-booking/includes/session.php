<?php

namespace JET_ABAF;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Session manager
 */
class Session {

	public $key = 'jet_booking';

	public function __construct() {
		add_action( 'parse_request', [ $this, 'init_session' ] );
	}

	/**
	 * Initialize session.
	 */
	public function init_session( $wp ) {
		$this->start_session();
	}

	/**
	 * Maybe start session.
	 */
	public function start_session() {

		if ( headers_sent() ) {
			return;
		}

		if ( ! session_id() ) {
			session_start();
		}

	}

	/**
	 * Set session value
	 */
	public function set( $key, $value ) {

		$this->start_session();

		if ( empty( $_SESSION[ $this->key ] ) ) {
			$_SESSION[ $this->key ] = array();
		}

		$_SESSION[ $this->key ][ $key ] = $value;

	}

	/**
	 * Get session value
	 */
	public function get( $key, $default = false ) {

		$this->start_session();

		if ( empty( $_SESSION[ $this->key ] ) ) {
			$_SESSION[ $this->key ] = array();
		}

		return isset( $_SESSION[ $this->key ][ $key ] ) ? $_SESSION[ $this->key ][ $key ] : $default;

	}

}
