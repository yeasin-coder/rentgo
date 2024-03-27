<?php

namespace JET_ABAF\Rest_API;

use JET_ABAF\Plugin;

class Endpoint_Calendars_List extends \Jet_Engine_Base_API_Endpoint {

	/**
	 * Returns route name
	 *
	 * @return string
	 */
	public function get_name() {
		return 'calendars-list';
	}

	/**
	 * Callback.
	 *
	 * Rest API callback.
	 *
	 * @since  2.7.0 Added iCal template variable handling.
	 * @access public
	 *
	 * @return \WP_REST_Response
	 */
	public function callback( $request ) {

		$calendars     = Plugin::instance()->ical->get_calendars();
		$ical_template = get_option( Endpoint_Update_ICal_Template::$key );

		return rest_ensure_response( [
			'success' => true,
			'data'    => [
				'calendars'     => $calendars,
				'ical_template' => $ical_template ? $ical_template : [],
			],
		] );

	}

	/**
	 * Check user access to current end-popint
	 *
	 * @return bool
	 */
	public function permission_callback( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Returns endpoint request method - GET/POST/PUT/DELTE
	 *
	 * @return string
	 */
	public function get_method() {
		return 'GET';
	}

}
