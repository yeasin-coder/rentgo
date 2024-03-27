<?php

namespace JET_ABAF\Elementor_Integration\Dynamic_Tags;

use JET_ABAF\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Units_Count extends \Elementor\Core\DynamicTags\Tag {

	public function get_name() {
		return 'jet-units-count';
	}

	public function get_title() {
		return __( 'JetBooking: Units count', 'jet-booking' );
	}

	public function get_group() {
		return \Jet_Engine_Dynamic_Tags_Module::JET_GROUP;
	}

	public function get_categories() {
		return [
			\Jet_Engine_Dynamic_Tags_Module::TEXT_CATEGORY,
			\Jet_Engine_Dynamic_Tags_Module::NUMBER_CATEGORY,
			\Jet_Engine_Dynamic_Tags_Module::POST_META_CATEGORY,
		];
	}

	public function is_settings_required() {
		return false;
	}

	public function render() {

		$units       = Plugin::instance()->db->get_apartment_units( get_the_ID() );
		$units_count = ! empty( $units ) ? count( $units ) : $this->get_settings( 'fallback' );

		echo sprintf( '<span data-post="%1$s" data-units-count="%2$s">%2$s</span>', get_the_ID(), $units_count );

	}

}
