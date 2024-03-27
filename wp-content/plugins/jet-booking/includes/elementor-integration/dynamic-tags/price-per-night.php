<?php

namespace JET_ABAF\Elementor_Integration\Dynamic_Tags;

use JET_ABAF\Price;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Price_Per_Night extends \Elementor\Core\DynamicTags\Tag {

	public function get_name() {
		return 'jet-price-per-night';
	}

	public function get_title() {
		return __( 'JetBooking: Price per day/night', 'jet-booking' );
	}

	public function get_group() {
		return \Jet_Engine_Dynamic_Tags_Module::JET_GROUP;
	}

	public function get_categories() {
		return array(
			\Jet_Engine_Dynamic_Tags_Module::TEXT_CATEGORY,
			\Jet_Engine_Dynamic_Tags_Module::NUMBER_CATEGORY,
			\Jet_Engine_Dynamic_Tags_Module::POST_META_CATEGORY,
		);
	}

	public function is_settings_required() {
		return true;
	}

	protected function register_controls() {

		$this->add_control(
			'show_price',
			[
				'type'    => \Elementor\Controls_Manager::SELECT,
				'label'   => __( 'Show price', 'jet-booking' ),
				'default' => 'default',
				'options' => [
					'default' => __( 'Default', 'jet-booking' ),
					'min'     => __( 'Min price', 'jet-booking' ),
					'max'     => __( 'Max price', 'jet-booking' ),
					'range'   => __( 'Prices range', 'jet-booking' ),
				],
			]
		);

		$this->add_control(
			'change_dynamically',
			[
				'type'        => \Elementor\Controls_Manager::SWITCHER,
				'label'       => __( 'Change Dynamically', 'jet-booking' ),
				'default'     => 'yes',
				'description' => __( 'Change price dynamically on check-in check-out dates select. Will work correctly only when appropriate form presented on the page', 'jet-booking' ),
			]
		);

		$this->add_control(
			'currency_sign',
			[
				'type'    => \Elementor\Controls_Manager::TEXT,
				'label'   => __( 'Currency sign', 'jet-booking' ),
				'default' => '$',
			]
		);

		$this->add_control(
			'currency_sign_position',
			[
				'type'    => \Elementor\Controls_Manager::SELECT,
				'label'   => __( 'Currency sign position', 'jet-booking' ),
				'default' => 'before',
				'options' => [
					'before' => __( 'Before price', 'jet-booking' ),
					'after'  => __( 'After price', 'jet-booking' ),
				],
			]
		);

	}

	public function render() {

		$show_price         = $this->get_settings( 'show_price' );
		$change_dynamically = filter_var( $this->get_settings( 'change_dynamically' ), FILTER_VALIDATE_BOOLEAN );
		$currency_sign      = $this->get_settings( 'currency_sign' );
		$currency_sign_pos  = $this->get_settings( 'currency_sign_position' );
		$price              = new Price( get_the_ID() );

		echo $price->get_price_for_display( [
			'show_price'             => $show_price,
			'change_dynamically'     => $change_dynamically,
			'currency_sign'          => $currency_sign,
			'currency_sign_position' => $currency_sign_pos,
		] );

	}

}
