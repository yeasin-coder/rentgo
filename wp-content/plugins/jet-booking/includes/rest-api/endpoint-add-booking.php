<?php

namespace JET_ABAF\Rest_API;

use JET_ABAF\Plugin;

class Endpoint_Add_Booking extends \Jet_Engine_Base_API_Endpoint {

	/**
	 * Name.
	 *
	 * Returns route name.
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @return string
	 */
	public function get_name() {
		return 'add-booking';
	}

	/**
	 * Callback.
	 *
	 * API callback.
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @param $request
	 *
	 * @return \WP_Error|\WP_HTTP_Response|\WP_REST_Response
	 */
	public function callback( $request ) {

		$params      = $request->get_params();
		$item        = ! empty( $params['item'] ) ? $params['item'] : [];
		$not_allowed = [ 'booking_id', 'apartment_unit' ];

		if ( empty( $item['check_in_date'] ) || empty( $item['check_out_date'] ) ) {
			return rest_ensure_response( [
				'success' => false,
				'data'    => __( 'Booking date is empty.', 'jet-booking' ),
			] );
		}

		foreach ( $not_allowed as $key ) {
			if ( isset( $item[ $key ] ) ) {
				unset( $item[ $key ] );
			}
		}

		if ( empty( $item ) ) {
			return rest_ensure_response( [
				'success' => false,
				'data'    => __( 'Booking could not be added.', 'jet-booking' ),
			] );
		}

		$item['check_in_date']  = strtotime( $item['check_in_date'] );
		$item['check_out_date'] = strtotime( $item['check_out_date'] );

		$is_available       = Plugin::instance()->db->booking_availability( $item );
		$is_dates_available = Plugin::instance()->db->is_booking_dates_available( $item );

		if ( ! $is_available && ! $is_dates_available ) {
			ob_start();

			echo __( 'Selected dates are not available.', 'jet-booking' ) . '<br>';

			if ( Plugin::instance()->db->latest_result ) {
				echo __( 'Overlapping bookings: ', 'jet-booking' );

				$result = [];

				foreach ( Plugin::instance()->db->latest_result as $ob ) {
					if ( ! empty( $ob['order_id'] ) ) {
						$result[] = sprintf( '<a href="%s" target="_blank">#%d</a>', get_edit_post_link( $ob['order_id'] ), $ob['order_id'] );
					} else {
						$result[] = '#' . $ob['booking_id'];
					}
				}

				echo implode( ', ', $result ) . '.';
			}

			return rest_ensure_response( [
				'success'              => false,
				'overlapping_bookings' => true,
				'html'                 => ob_get_clean(),
				'data'                 => __( 'Can`t add this item', 'jet-booking' ),
			] );
		}

		$order = ! empty( $params['order'] ) ? $params['order'] : [];

		if ( ! empty( $order ) ) {
			$item = wp_parse_args( $this->get_order_data( $order, $item ), $item );
		}

		Plugin::instance()->db->insert_booking( $item );

		return rest_ensure_response( [
			'success' => true,
		] );

	}

	/**
	 * Get order data.
	 *
	 * Returns list of order data parameters.
	 *
	 * @since  2.8.0
	 * @access public
	 *
	 * @param array $order   Initial order data list.
	 * @param array $booking Booking item parameters.
	 *
	 * @return array
	 */
	public function get_order_data( $order, $booking ) {

		if ( jet_abaf()->settings->get( 'wc_integration' ) ) {
			$wc_order = wc_create_order();

			if ( is_wp_error( $wc_order ) ) {
				return [];
			}

			$product_id = jet_abaf()->wc->get_product_id();
			$price      = jet_abaf()->wc->get_booking_price( $booking );

			$wc_order->add_product( wc_get_product( $product_id ), 1, [
				'subtotal' => $price,
				'total'    => $price,
			] );

			$wc_order->set_billing_address( [
				'first_name' => ! empty( $order['firstName'] ) ? $order['firstName'] : '',
				'last_name'  => ! empty( $order['lastName'] ) ? $order['lastName'] : '',
				'email'      => ! empty( $order['email'] ) ? $order['email'] : '',
				'phone'      => ! empty( $order['phone'] ) ? $order['phone'] : '',
			] );

			$wc_order->set_status( 'wc-on-hold' );
			$wc_order->calculate_totals();
			$wc_order->save();

			return [
				'order_id' => $wc_order->get_id(),
				'status'   => $wc_order->get_status(),
			];
		}

		$post_type        = jet_abaf()->settings->get( 'related_post_type' );
		$post_type_object = get_post_type_object( $post_type );

		$args = [
			'post_type'   => $post_type,
			'post_status' => ! empty( $order['orderStatus'] ) ? $order['orderStatus'] : 'draft',
		];

		if ( post_type_supports( $post_type, 'excerpt' ) ) {
			$args['post_excerpt'] = sprintf( __( 'This is %s post.', 'jet-booking' ), $post_type_object->labels->singular_name );
		}

		$post_id = wp_insert_post( $args );

		if ( ! $post_id || is_wp_error( $post_id ) ) {
			return [];
		}

		wp_update_post( [
			'ID'         => $post_id,
			'post_title' => $post_type_object->labels->singular_name . ' #' . $post_id,
			'post_name'  => $post_type_object->labels->singular_name . '-' . $post_id,
		] );

		return [ 'order_id' => $post_id ];

	}

	/**
	 * Permission callback.
	 *
	 * Check user access to current end-point.
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @return bool
	 */
	public function permission_callback( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Method.
	 *
	 * Returns endpoint request method - GET/POST/PUT/DELETE.
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @return string
	 */
	public function get_method() {
		return 'POST';
	}

	/**
	 * Arguments.
	 *
	 * Returns arguments config.
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @return array
	 */
	public function get_args() {
		return [];
	}

}