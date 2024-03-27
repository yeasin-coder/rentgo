<?php
namespace JET_ABAF\Rest_API;

use JET_ABAF\Plugin;

class Endpoint_Update_Booking extends \Jet_Engine_Base_API_Endpoint {

	/**
	 * Returns route name
	 *
	 * @return string
	 */
	public function get_name() {
		return 'update-booking';
	}

	/**
	 * API callback
	 *
	 * @return void
	 */
	public function callback( $request ) {

		$params  = $request->get_params();
		$item_id = ! empty( $params['id'] ) ? absint( $params['id'] ) : 0;
		$item    = ! empty( $params['item'] ) ? $params['item'] : array();

		$not_allowed = [
			'booking_id',
			'order_id',
			'check_in_date_timestamp',
			'check_out_date_timestamp',
		];

		if ( empty( $item['check_in_date'] ) || empty( $item['check_out_date'] ) ) {
			return rest_ensure_response( array(
				'success' => false,
				'data'    => esc_html__( 'Incorrect item data', 'jet-booking' ),
			) );
		}

		foreach ( $not_allowed as $key ) {
			if ( isset( $item[ $key ] ) ) {
				unset( $item[ $key ] );
			}
		}

		if ( empty( $item ) ) {
			return rest_ensure_response( array(
				'success' => false,
				'data'    => esc_html__( 'No data to update', 'jet-booking' ),
			) );
		}

		$item['check_in_date']  = strtotime( $item['check_in_date'] );
		$item['check_out_date'] = strtotime( $item['check_out_date'] );

		$is_available       = Plugin::instance()->db->booking_availability( $item, $item_id );
		$is_dates_available = Plugin::instance()->db->is_booking_dates_available( $item, $item_id );

		if ( ! $is_available && ! $is_dates_available ) {
			ob_start();

			echo __( 'Selected dates are not available.', 'jet-booking' ) . '<br>';

			if ( Plugin::instance()->db->latest_result ) {
				echo __( 'Overlapping bookings: ', 'jet-booking' );

				$result = [];

				foreach ( Plugin::instance()->db->latest_result as $ob ) {
					if ( absint( $ob['booking_id'] ) !== $item_id ) {
						if ( ! empty( $ob['order_id'] ) ) {
							$result[] = sprintf( '<a href="%s" target="_blank">#%d</a>', get_edit_post_link( $ob['order_id'] ), $ob['order_id'] );
						} else {
							$result[] = '#' . $ob['booking_id'];
						}
					}
				}

				echo implode( ', ', $result ) . '.';
			}

			return rest_ensure_response( array(
				'success'              => false,
				'overlapping_bookings' => true,
				'html'                 => ob_get_clean(),
				'data'                 => esc_html__( 'Can`t update this item', 'jet-booking' ),
			) );

		}

		Plugin::instance()->db->update_booking( $item_id, $item );

		return rest_ensure_response( array(
			'success' => true,
		) );

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
		return 'POST';
	}

	/**
	 * Get query param. Regex with query parameters
	 *
	 * @return string
	 */
	public function get_query_params() {
		return '(?P<id>[\d]+)';
	}

}