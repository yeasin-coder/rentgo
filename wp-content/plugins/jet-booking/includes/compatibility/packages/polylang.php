<?php

namespace JET_ABAF\Compatibility\Packages;

use JET_ABAF\Plugin;

class Polylang {

	public function __construct() {

		add_filter( 'jet-abaf/db/initial-apartment-id', [ $this, 'set_initial_booking_item_id' ] );
		add_filter( 'jet-booking/wc-integration/apartment-id', [ $this, 'set_booking_item_id' ] );
		add_filter( 'jet-abaf/dashboard/bookings-page/post-type-args', [ $this, 'set_additional_post_type_args' ] );

		add_filter( 'pll_copy_post_metas', [ $this, 'copy_post_metas' ] );

		add_action( 'jet-abaf/dashboard/bookings-page/before-page-config', [ $this, 'set_required_page_language' ] );

	}

	/**
	 * Set initial booking item id.
	 *
	 * Returns a booking item id for the default site language.
	 *
	 * @since  2.6.3
	 * @access public
	 *
	 * @param int|string $id Apartment post type ID.
	 *
	 * @return mixed|void
	 */
	public function set_initial_booking_item_id( $id ) {

		$default_lang = pll_default_language();
		$translations = pll_get_post_translations( $id );

		if ( empty( $translations ) ) {
			return $id;
		}

		if ( isset( $translations[ $default_lang ] ) && $id !== $translations[ $default_lang ] ) {
			$id = $translations[ $default_lang ];
		}

		return $id;

	}

	/**
	 * Set booking item id.
	 *
	 * Returns a booking item id for current site language.
	 *
	 * @since  2.5.5
	 * @access public
	 *
	 * @param int|string $id Apartment post type ID.
	 *
	 * @return mixed|void
	 */
	public function set_booking_item_id( $id ) {

		$current_language = pll_current_language();
		$translations = pll_get_post_translations( $id );

		if ( empty( $translations ) ) {
			return $id;
		}

		if ( isset( $translations[ $current_language ] ) && $id !== $translations[ $current_language ] ) {
			$id = $translations[ $current_language ];
		}

		return $id;

	}

	/**
	 * Set additional post type args.
	 *
	 * Returns booking post type arguments with additional option so we can see only default language posts.
	 *
	 * @since  2.6.3
	 * @access public
	 *
	 * @param array $args List of post arguments.
	 *
	 * @return mixed
	 */
	public function set_additional_post_type_args( $args ) {

		$args['lang'] = pll_default_language();

		return $args;

	}

	/**
	 * Copy post meta.
	 *
	 * Synchronizes post metas.
	 *
	 * @since  2.6.3
	 * @access public
	 *
	 * @param array $metas List of custom fields names.
	 *
	 * @return array
	 */
	public function copy_post_metas( $metas ) {

		$booking_metas = [
			'enable_config',
			'one_day_bookings',
			'weekly_bookings',
			'week_offset',
			'start_day_offset',
			'min_days',
			'max_days',
			'use_custom_schedule',
			'days_off',
			'disable_weekday_1',
			'check_in_weekday_1',
			'check_out_weekday_1',
			'disable_weekday_2',
			'check_in_weekday_2',
			'check_out_weekday_2',
			'disable_weekday_3',
			'check_in_weekday_3',
			'check_out_weekday_3',
			'disable_weekday_4',
			'check_in_weekday_4',
			'check_out_weekday_4',
			'disable_weekday_5',
			'check_in_weekday_5',
			'check_out_weekday_5',
			'disable_weekend_1',
			'check_in_weekend_1',
			'check_out_weekend_1',
			'disable_weekend_2',
			'check_in_weekend_2',
			'check_out_weekend_2',
			'_apartment_price',
			'_pricing_rates',
			'_seasonal_prices',
			'_weekend_prices',
		];

		return array_unique( array_merge( $metas, $booking_metas ) );

	}

	/**
	 * Set required page language.
	 *
	 * Switch language to default for proper posts display in bookings list.
	 *
	 * @since  2.6.3
	 * @access public
	 */
	public function set_required_page_language() {

		$default_lang = pll_default_language();

		if ( ! PLL()->curlang || PLL()->curlang->slug === $default_lang ) {
			return;
		}

		PLL()->curlang = PLL()->model->get_language( $default_lang );

	}

}

new Polylang();