<?php

namespace JET_ABAF;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Plug-in code into JetSmartFilters
 */
class Filters_Plugin {

	public function __construct() {

		add_filter(
			'jet-smart-filters/query/final-query',
			array( $this, 'set_booking_params' )
		);

		// before JSF version 3.0.0
		add_action(
			'jet-smart-filters/post-type/filter-notes-after',
			array( $this, 'add_booking_notes' )
		);

		// after JSF version 3.0.0
		add_action(
			'jet-smart-filters/admin/register-dynamic-query',
			array( $this, 'add_booking_dynamic_query' )
		);
	}

	public function add_booking_notes() {
		echo '<p><b>' . esc_html__( 'JetBooking:', 'jet-booking' ) . '</b></p>';
		echo '<ul>';
		printf( '<li><b>checkin_checkout</b>: %s</li>', esc_html__( 'filter available instances by checkin/checkout dates (allowed only for Date Range filter);', 'jet-booking' ) );
		echo '</ul>';
	}

	public function add_booking_dynamic_query( $dynamic_query_manager ) {
		$dynamic_query_manager->register_items( array(
			'checkin_checkout' => __( 'JetBooking: checkin_checkout - filter available instances by checkin/checkout dates (allowed only for Date Range filter)', 'jet-booking' ),
		) );
	}

	/**
	 * Check if booking var presented in query - unset it and add apartments unavailable for this period into query
	 */
	public function set_booking_params( $query ) {

		if ( empty( $query['meta_query'] ) ) {
			return $query;
		}

		$store_type = Plugin::instance()->settings->get( 'filters_store_type' );

		foreach ( $query['meta_query'] as $index => $meta_query ) {
			if ( isset( $meta_query['key'] ) && ( 'chekin_checkout' === $meta_query['key'] || 'checkin_checkout' === $meta_query['key'] ) ) {
				$from = $meta_query['value'][0];
				$to   = $meta_query['value'][1];

				unset( $query['meta_query'][ $index ] );

				if ( 'session' === $store_type ) {
					Plugin::instance()->session->set( 'searched_dates', $from . ' - ' . $to );
				} else {
					Plugin::instance()->cookies->set( 'searched_dates', $from . ' - ' . $to );
				}

				$exclude = $this->get_unavailable_apartments( $from, $to );

				if ( $exclude ) {
					$query['post__not_in'] = $exclude;
				}
			}
		}

		return $query;

	}

	/**
	 * Get unavailable apartments.
	 *
	 * @since 2.0.0
	 * @since 2.6.1 New handling.
	 *
	 * @param string $from Range start date in timestamp.
	 * @param string $to   Range end date in timestamp.
	 *
	 * @return array
	 */
	public function get_unavailable_apartments( $from, $to ) {

		$posts = Plugin::instance()->utils->get_booking_posts();

		if ( empty( $posts ) ) {
			return [];
		}

		$booked_apartments = [];

		foreach ( $posts as $post ) {
			$invalid_dates = Plugin::instance()->utils->get_invalid_dates_in_range( $from, $to, $post->ID );

			if ( ! empty( $invalid_dates ) ) {
				$booked_apartments[] = $post->ID;
			}
		}

		return $booked_apartments;

	}

}