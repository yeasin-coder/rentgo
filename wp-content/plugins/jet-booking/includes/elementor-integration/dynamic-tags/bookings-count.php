<?php

namespace JET_ABAF\Elementor_Integration\Dynamic_Tags;

use JET_ABAF\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Bookings_Count extends \Elementor\Core\DynamicTags\Tag {

	public function get_name() {
		return 'jet-bookings-count';
	}

	public function get_title() {
		return __( 'JetBooking: Bookings count', 'jet-booking' );
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
		return true;
	}

	protected function register_controls() {

		$this->add_control(
			'bookings_period',
			[
				'type'  => \Elementor\Controls_Manager::HEADING,
				'label' => __( 'Bookings Period', 'plugin-name' ),
			]
		);

		$this->add_control(
			'start_date',
			[
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label'       => __( 'Start Date', 'jet-booking' ),
				'label_block' => true,
				'dynamic'     => [
					'active' => true,
				],
			]
		);

		$this->add_control(
			'end_date',
			[
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label'       => __( 'End Date', 'jet-booking' ),
				'label_block' => true,
				'dynamic'     => [
					'active' => true,
				],
			]
		);

		$this->add_control(
			'period_tip',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw'  => __( 'Enter date in Universal time format: </br> `Y-m-d H:i:s` or `Y-m-d`. <b>Example:</b></br> `1996-04-09 00:00:00` or `1996-04-09`.', 'jet-booking' ),
			]
		);

	}

	public function render() {

		$from = $this->get_settings( 'start_date' );
		$to   = $this->get_settings( 'end_date' );

		if ( empty( $from ) ) {
			echo __( 'Please select date range.', 'jet-booking' );
			return;
		}

		if ( empty( $to ) ) {
			$to = $from;
		}

		$booking = [
			'apartment_id'   => get_the_ID(),
			'check_in_date'  => strtotime( $from ),
			'check_out_date' => strtotime( $to ),
		];

		$bookings = Plugin::instance()->db->get_booked_units( $booking );

		echo ! empty( $bookings ) ? count( $bookings ) : $this->get_settings( 'fallback' );

	}

}
