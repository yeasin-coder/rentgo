<?php

namespace JET_ABAF\Rest_API;

use JET_ABAF\Plugin;

class Endpoint_Bookings_List extends \Jet_Engine_Base_API_Endpoint {

	/**
	 * Returns route name
	 *
	 * @return string
	 */
	public function get_name() {
		return 'bookings-list';
	}

	public function callback( $request ) {

		$params   = $request->get_params();
		$offset   = ! empty( $params['offset'] ) ? absint( $params['offset'] ) : 0;
		$per_page = ! empty( $params['per_page'] ) ? absint( $params['per_page'] ) : 50;
		$filters  = ! empty( $params['filters'] ) ? json_decode( $params['filters'], true ) : [];
		$filters  = ( ! empty( $filters ) && is_array( $filters ) ) ? array_filter( $filters ) : [];
		$sort     = ! empty( $params['sort'] ) ? json_decode( $params['sort'], true ) : [];
		$sort     = ( ! empty( $sort ) && is_array( $sort ) ) ? array_filter( $sort ) : [ 'orderby' => 'booking_id', 'order' => 'DESC', ];
		$mode     = ! empty( $params['mode'] ) ? $params['mode'] : 'all';

		switch ( $mode ) {
			case 'upcoming':
				$filters['check_in_date>'] = strtotime( date( 'Y-m-d' ) );
				break;

			case 'past':
				$filters['check_in_date<'] = strtotime( date( 'Y-m-d' ) );
				break;
		}

		if ( ! empty( $filters['check_in_date'] ) && ! is_int( $filters['check_in_date'] ) ) {
			$filters['check_in_date'] = strtotime( $filters['check_in_date'] ) + 1;
		}

		if ( ! empty( $filters['check_out_date'] ) && ! is_int( $filters['check_out_date'] ) ) {
			$filters['check_out_date'] = strtotime( $filters['check_out_date'] );
		}

		if ( ! empty( $filters['check_in_date'] ) && ! empty( $filters['check_out_date'] ) ) {
			$filters['check_in_date>=']  = $filters['check_in_date'];
			$filters['check_out_date<='] = $filters['check_out_date'];

			unset( $filters['check_in_date'] );
			unset( $filters['check_out_date'] );
		}

		$bookings = Plugin::instance()->db->query(
			$filters,
			null,
			$per_page,
			$offset,
			$sort
		);

		$bookings = apply_filters( 'jet-booking/rest-api/bookings-list/bookings', $bookings );

		if ( empty( $bookings ) ) {
			$bookings = [];
		}

		return rest_ensure_response( [
			'success' => true,
			'data'    => $this->format_dates( $bookings ),
			'total'   => intval( jet_abaf()->db->count( $filters ) ),
		] );

	}

	/**
	 * Format dates.
	 *
	 * Transform dates to human readable format and add additional parameters to booked item.
	 *
	 * @since  2.0.0
	 * @since  2.5.4 Added timestamp dates.
	 * @access public
	 *
	 * @param array $bookings List of all bookings.
	 *
	 * @return array
	 */
	public function format_dates( $bookings = [] ) {

		$date_format = get_option( 'date_format', 'F j, Y' );

		return array_map( function ( $booking ) use ( $date_format ) {
			$booking['check_in_date_timestamp']  = $booking['check_in_date'];
			$booking['check_in_date']            = date_i18n( $date_format, $booking['check_in_date'] );
			$booking['check_out_date_timestamp'] = $booking['check_out_date'];
			$booking['check_out_date']           = date_i18n( $date_format, $booking['check_out_date'] );
			$booking['status']                   = ( ! empty( $booking['status'] ) ) ? $booking['status'] : 'pending';

			return $booking;
		}, $bookings );

	}

	/**
	 * Check user access to current end-point.
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

	public function get_args() {
		return [
			'offset'   => [
				'default'  => 0,
				'required' => false,
			],
			'per_page' => [
				'default'  => 50,
				'required' => false,
			],
			'filters'  => [
				'default'  => [],
				'required' => false,
			],
			'mode'     => [
				'default'  => 'all',
				'required' => false,
			],
		];
	}

}
