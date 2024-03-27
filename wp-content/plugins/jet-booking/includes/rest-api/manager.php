<?php

namespace JET_ABAF\Rest_API;

use JET_ABAF\Plugin;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Manager {

	/**
	 * Register hooks
	 */
	public function __construct() {
		add_action( 'jet-engine/rest-api/init-endpoints', array( $this, 'init_rest' ) );
	}

	/**
	 * Init rest.
	 *
	 * Initialize Rest API endpoints.
	 *
	 * @since  2.0.0
	 * @since  2.5.4 Added new `Endpoint_Booked_Dates` endpoint.
	 * @since  2.7.0 Added new `Endpoint_Update_ICal_Template` endpoint.
	 * @access public
	 *
	 * @param object $api_manager Rest API instance.
	 */
	public function init_rest( $api_manager ) {

		$api_manager->register_endpoint( new Endpoint_Bookings_List() );
		$api_manager->register_endpoint( new Endpoint_Add_Booking() );
		$api_manager->register_endpoint( new Endpoint_Delete_Booking() );
		$api_manager->register_endpoint( new Endpoint_Update_Booking() );
		$api_manager->register_endpoint( new Endpoint_Booked_Dates() );

		if ( Plugin::instance()->settings->get( 'ical_synch' ) ) {
			$api_manager->register_endpoint( new Endpoint_Calendars_List() );
			$api_manager->register_endpoint( new Endpoint_Update_Calendar() );
			$api_manager->register_endpoint( new Endpoint_Synch_Calendar() );
			$api_manager->register_endpoint( new Endpoint_Update_ICal_Template() );
		}

	}

	/**
	 * Get urls.
	 *
	 * Returns all registered Rest API URLs
	 *
	 * @since  2.0.0
	 * @since  2.5.4 Added new route `booked_dates`.
	 * @since  2.7.0 Added new route `update-ical-template`.
	 * @access public
	 *
	 * @param bool $full Url type.
	 *
	 * @return array
	 */
	public function get_urls( $full = true ) {

		$res = [
			'bookings_list'  => jet_engine()->api->get_route( 'bookings-list', $full ),
			'add_booking'    => jet_engine()->api->get_route( 'add-booking', $full ),
			'delete_booking' => jet_engine()->api->get_route( 'delete-booking', $full ),
			'update_booking' => jet_engine()->api->get_route( 'update-booking', $full ),
			'booked_dates'   => jet_engine()->api->get_route( 'booked-dates', $full ),
		];

		if ( Plugin::instance()->settings->get( 'ical_synch' ) ) {
			$res['calendars_list']       = jet_engine()->api->get_route( 'calendars-list', $full );
			$res['update_calendar']      = jet_engine()->api->get_route( 'update-calendar', $full );
			$res['synch_calendar']       = jet_engine()->api->get_route( 'synch-calendar', $full );
			$res['update_ical_template'] = jet_engine()->api->get_route( 'update-ical-template', $full );
		}

		return $res;

	}

}

