<?php

namespace JET_ABAF\Rest_API;

use JET_ABAF\Plugin;

class Endpoint_Update_ICal_Template extends \Jet_Engine_Base_API_Endpoint {

	public static $key = 'jet_booking_ical_template';

	public function get_name() {
		return 'update-ical-template';
	}

	public function callback( $request ) {

		$params   = $request->get_params();
		$template = ! empty( $params['template'] ) ? $params['template'] : [];

		update_option( self::$key, $template, false );

		return rest_ensure_response( [
			'success' => true,
		] );

	}

	public function permission_callback( $request ) {
		return current_user_can( 'manage_options' );
	}

	public function get_method() {
		return 'POST';
	}

}