<?php

namespace JET_ABAF\Elementor_Integration;

use JET_ABAF\Plugin;

class Manager {

	public function __construct() {

		if ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, '3.5.0', '>=' ) ) {
			add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
		} else {
			add_action( 'elementor/widgets/widgets_registered', [ $this, 'register_widgets' ] );
		}

		add_action( 'jet-engine/listings/preview-scripts', [ $this, 'preview_scripts' ] );
		add_action( 'jet-engine/elementor-views/dynamic-tags/register', [ $this, 'register_dynamic_tags' ] );
		add_filter( 'jet-engine/elementor-view/dynamic-link/generel-options', [ $this, 'register_dynamic_link_option' ] );

	}

	public function register_widgets( $widgets_manager ) {
		if ( method_exists( $widgets_manager, 'register' ) ) {
			$widgets_manager->register( new Widgets\Calendar() );
		} else {
			$widgets_manager->register_widget_type( new Widgets\Calendar() );
		}
	}

	/**
	 * Enqueue preview JS
	 */
	public function preview_scripts() {
		Plugin::instance()->engine_plugin->enqueue_deps( get_the_ID() );
	}

	public function register_dynamic_tags( $tags_module ) {
		$tags_module->register_tag( new Dynamic_Tags\Bookings_Count() );
		$tags_module->register_tag( new Dynamic_Tags\Price_Per_Night() );
		$tags_module->register_tag( new Dynamic_Tags\Units_Count() );
	}

	public function register_dynamic_link_option( $options ) {
		$options[ Plugin::instance()->google_cal->query_var ] = __( 'JetBooking: add booking to Google calendar', 'jet-booking' );
		return $options;
	}

	/**
	 * Add custom size units.
	 *
	 * Extend list of units with custom option.
	 *
	 * @since  2.6.3
	 * @access public
	 *
	 * @param array $units List of units.
	 *
	 * @return mixed
	 */
	public function add_custom_size_unit( $units ) {

		if ( version_compare( ELEMENTOR_VERSION, '3.10.0', '>=' ) ) {
			$units[] = 'custom';
		}

		return $units;

	}

}