<?php

namespace JET_ABAF\Elementor_Integration\Widgets;

use JET_ABAF\Plugin;
use JET_ABAF\Render\Calendar as Calendar_Renderer;

if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

class Calendar extends \Elementor\Widget_Base {

	public function get_name() {
		return 'jet-booking-calendar';
	}

	public function get_title() {
		return esc_html__( 'Booking Availability Calendar', 'jet-booking' );
	}

	public function get_icon() {
		return 'jet-engine-icon-listing-calendar';
	}

	public function get_categories() {
		return array( 'jet-listing-elements' );
	}

	public function get_help_url() {
		return 'https://crocoblock.com/knowledge-base/article-category/jetbooking/';
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_general',
			array(
				'label' => esc_html__( 'Content', 'jet-booking' ),
			)
		);

		$this->add_control(
			'select_dates',
			array(
				'label'       => esc_html__( 'Allow to select dates', 'jet-booking' ),
				'description' => esc_html__( 'Find booking form on the page and set selected dates into check in/out field(s)', 'jet-booking' ),
				'type'        => \Elementor\Controls_Manager::SWITCHER,
				'default'     => 'yes',
			)
		);

		$this->add_control(
			'scroll_to_form',
			[
				'type'        => \Elementor\Controls_Manager::SWITCHER,
				'label'       => __( 'Scroll to the form', 'jet-booking' ),
				'description' => __( 'Scroll page to the start of the booking form on dates select', 'jet-booking' ),
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_calendar_style',
			[
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				'label' => __( 'General', 'jet-booking' ),
			]
		);

		$this->add_control(
			'month_container_heading',
			[
				'type'  => \Elementor\Controls_Manager::HEADING,
				'label' => __( 'Month Container', 'jet-booking' ),
			]
		);

		$this->add_control(
			'gap_color',
			[
				'type'      => \Elementor\Controls_Manager::COLOR,
				'label'     => __( 'Gap Line Color', 'jet-booking' ),
				'selectors' => [
					$this->css_selector( ' .date-picker-wrapper .gap:before' ) => 'border-left-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'container_bg_color',
			[
				'type'      => \Elementor\Controls_Manager::COLOR,
				'label'     => __( 'Background', 'jet-booking' ),
				'selectors' => [
					$this->css_selector( ' .date-picker-wrapper' ) => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'container_padding',
			[
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'label'      => __( 'Padding', 'jet-booking' ),
				'size_units' => Plugin::instance()->elementor->add_custom_size_unit( [ 'px', 'em', '%' ] ),
				'selectors'  => [
					$this->css_selector( ' .date-picker-wrapper' ) => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_control(
			'month_tables_heading',
			[
				'type'      => \Elementor\Controls_Manager::HEADING,
				'label'     => __( 'Month Tables', 'jet-booking' ),
				'separator' => 'before',
			]
		);

		$this->add_control(
			'table_bg_color',
			[
				'type'      => \Elementor\Controls_Manager::COLOR,
				'label'     => __( 'Background', 'jet-booking' ),
				'selectors' => [
					$this->css_selector( ' .month1' ) => 'background-color: {{VALUE}}',
					$this->css_selector( ' .month2' ) => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name'     => 'table_border',
				'selector' => $this->css_selector( ' .month1' ) . ', ' . $this->css_selector( ' .month2' ),
			]
		);

		$this->add_responsive_control(
			'table_border_radius',
			[
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'label'      => __( 'Border Radius', 'jet-booking' ),
				'size_units' => Plugin::instance()->elementor->add_custom_size_unit( [ 'px', 'em', '%' ] ),
				'selectors'  => [
					$this->css_selector( ' .month1' ) => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					$this->css_selector( ' .month2' ) => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'table_box_shadow',
				'selector' => $this->css_selector( ' .month1' ) . ', ' . $this->css_selector( ' .month2' ),
			]
		);

		$this->add_responsive_control(
			'table_padding',
			[
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'label'      => __( 'Padding', 'jet-booking' ),
				'size_units' => Plugin::instance()->elementor->add_custom_size_unit( [ 'px', 'em', '%' ] ),
				'selectors'  => [
					$this->css_selector( ' .month1' ) => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					$this->css_selector( ' .month2' ) => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_heading_style',
			[
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				'label' => __( 'Headers', 'jet-booking' ),
			]
		);

		$this->add_control(
			'month_names_heading',
			[
				'type'  => \Elementor\Controls_Manager::HEADING,
				'label' => __( 'Month Names', 'jet-booking' ),
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'month_names_typography',
				'selector' => '{{WRAPPER}} thead .caption .month-name .month-element, {{WRAPPER}} thead .caption .month-name .month-element',
			]
		);

		$this->add_control(
			'month_names_color',
			[
				'type'      => \Elementor\Controls_Manager::COLOR,
				'label'     => __( 'Color', 'jet-booking' ),
				'selectors' => [
					$this->css_selector( ' thead .caption .month-name' ) => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'month_names_bg_color',
			[
				'type'      => \Elementor\Controls_Manager::COLOR,
				'label'     => esc_html__( 'Background', 'jet-booking' ),
				'selectors' => [
					$this->css_selector( '  thead .caption .month-name' ) => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name'     => 'month_names_border',
				'selector' => $this->css_selector( ' thead .caption .month-name' ),
			]
		);

		$this->add_responsive_control(
			'month_names_border_radius',
			[
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'label'      => __( 'Border Radius', 'jet-booking' ),
				'size_units' => Plugin::instance()->elementor->add_custom_size_unit( [ 'px', 'em', '%' ] ),
				'selectors'  => [
					$this->css_selector( ' thead .caption .month-name' ) => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'month_names_padding',
			[
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'label'      => __( 'Padding', 'jet-booking' ),
				'size_units' => Plugin::instance()->elementor->add_custom_size_unit( [ 'px', 'em', '%' ] ),
				'selectors'  => [
					$this->css_selector( ' thead .caption .month-name' ) => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'month_switcher_heading',
			[
				'type'      => \Elementor\Controls_Manager::HEADING,
				'label'     => __( 'Month Switcher', 'jet-booking' ),
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'month_switcher_font_size',
			[
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'label'      => __( 'Font size', 'jet-booking' ),
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 30,
						'max' => 200,
					],
				],
				'selectors'  => [
					$this->css_selector( ' thead .caption .prev' ) => 'font-size: {{SIZE}}{{UNIT}}',
					$this->css_selector( ' thead .caption .next' ) => 'font-size: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_responsive_control(
			'month_switcher_size',
			[
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'label'      => __( 'Box size', 'jet-booking' ),
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 30,
						'max' => 200,
					],
				],
				'selectors'  => [
					$this->css_selector( ' thead .caption .prev' )             => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					$this->css_selector( ' thead .caption .next' )             => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					$this->css_selector( ' thead .caption > div:first-child' ) => 'flex: 0 0 {{SIZE}}{{UNIT}}',
					$this->css_selector( ' thead .caption > div:last-child' )  => 'flex: 0 0 {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_month_switcher_style' );

		$this->start_controls_tab(
			'month_switcher_normal',
			[
				'label' => __( 'Normal', 'jet-booking' ),
			]
		);

		$this->add_control(
			'month_switcher_color',
			[
				'type'      => \Elementor\Controls_Manager::COLOR,
				'label'     => __( 'Color', 'jet-booking' ),
				'selectors' => [
					$this->css_selector( ' thead .caption .prev' ) => 'color: {{VALUE}}',
					$this->css_selector( ' thead .caption .next' ) => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'month_switcher_bg_color',
			[
				'type'      => \Elementor\Controls_Manager::COLOR,
				'label'     => __( 'Background', 'jet-booking' ),
				'selectors' => [
					$this->css_selector( ' thead .caption .prev' ) => 'background-color: {{VALUE}}',
					$this->css_selector( ' thead .caption .next' ) => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'month_switcher_hover',
			[
				'label' => __( 'Hover', 'jet-booking' ),
			]
		);

		$this->add_control(
			'month_switcher_color_hover',
			[
				'type'      => \Elementor\Controls_Manager::COLOR,
				'label'     => __( 'Color', 'jet-booking' ),
				'selectors' => [
					$this->css_selector( ' thead .caption .prev:hover' ) => 'color: {{VALUE}}',
					$this->css_selector( ' thead .caption .next:hover' ) => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'month_switcher_bg_color_hover',
			[
				'type'      => \Elementor\Controls_Manager::COLOR,
				'label'     => __( 'Background', 'jet-booking' ),
				'selectors' => [
					$this->css_selector( ' thead .caption .prev:hover' ) => 'background-color: {{VALUE}}',
					$this->css_selector( ' thead .caption .next:hover' ) => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'month_switcher_border_color_hover',
			[
				'type'      => \Elementor\Controls_Manager::COLOR,
				'label'     => __( 'Border Color', 'jet-booking' ),
				'selectors' => [
					$this->css_selector( ' thead .caption .prev:hover' ) => 'border-color: {{VALUE}}',
					$this->css_selector( ' thead .caption .next:hover' ) => 'border-color: {{VALUE}}',
				],
				'condition' => [
					'month_switcher_border_border!' => '',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name'      => 'month_switcher_border',
				'separator' => 'before',
				'selector'  => $this->css_selector( ' thead .caption .prev' ) . ', ' . $this->css_selector( ' thead .caption .next' ),
			]
		);

		$this->add_responsive_control(
			'month_switcher_border_radius',
			[
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'label'      => __( 'Border Radius', 'jet-booking' ),
				'size_units' => Plugin::instance()->elementor->add_custom_size_unit( [ 'px', 'em', '%' ] ),
				'selectors'  => [
					$this->css_selector( ' thead .caption .prev' ) => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					$this->css_selector( ' thead .caption .next' ) => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'week_days_heading',
			[
				'type'      => \Elementor\Controls_Manager::HEADING,
				'label'     => __( 'Week Days', 'jet-booking' ),
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'week_days_typography',
				'selector' => $this->css_selector( ' thead .week-name' ),
			]
		);

		$this->add_control(
			'week_days_color',
			[
				'type'      => \Elementor\Controls_Manager::COLOR,
				'label'     => __( 'Color', 'jet-booking' ),
				'selectors' => [
					$this->css_selector( ' thead .week-name' ) => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'week_days_bg_color',
			[
				'type'      => \Elementor\Controls_Manager::COLOR,
				'label'     => __( 'Background', 'jet-booking' ),
				'selectors' => [
					$this->css_selector( ' thead .week-name' ) => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name'     => 'week_days_border',
				'label'    => __( 'Border', 'jet-booking' ),
				'selector' => $this->css_selector( ' thead .week-name th' ),
			]
		);

		$this->add_responsive_control(
			'week_days_padding',
			[
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'label'      => __( 'Padding', 'jet-booking' ),
				'size_units' => Plugin::instance()->elementor->add_custom_size_unit( [ 'px', 'em', '%' ] ),
				'selectors'  => [
					$this->css_selector( ' thead .week-name th' ) => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_days_style',
			[
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				'label' => __( 'Days', 'jet-booking' ),
			]
		);

		$this->add_control(
			'cell_heading',
			[
				'type'  => \Elementor\Controls_Manager::HEADING,
				'label' => __( 'Cell', 'jet-booking' ),
			]
		);

		$this->add_control(
			'calendar_days_cell_bg_color',
			[
				'type'      => \Elementor\Controls_Manager::COLOR,
				'label'     => __( 'Background', 'jet-booking' ),
				'selectors' => [
					$this->css_selector( ' tbody td' ) => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name'     => 'calendar_days_cell_border',
				'selector' => $this->css_selector( ' tbody td' ),
			]
		);

		$this->add_responsive_control(
			'calendar_days_cell_padding',
			[
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'label'      => __( 'Padding', 'jet-booking' ),
				'size_units' => Plugin::instance()->elementor->add_custom_size_unit( [ 'px', 'em', '%' ] ),
				'selectors'  => [
					$this->css_selector( ' tbody td' ) => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'days_heading',
			[
				'type'      => \Elementor\Controls_Manager::HEADING,
				'label'     => __( 'Days', 'jet-booking' ),
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'calendar_days_typography',
				'selector' => $this->css_selector( ' tbody .day' ),
			]
		);

		$this->add_control(
			'calendar_days_color',
			[
				'type'      => \Elementor\Controls_Manager::COLOR,
				'label'     => __( 'Color', 'jet-booking' ),
				'selectors' => [
					$this->css_selector( ' tbody .day.toMonth.valid' ) => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'calendar_days_day_bg_color',
			[
				'type'      => \Elementor\Controls_Manager::COLOR,
				'label'     => __( 'Background', 'jet-booking' ),
				'selectors' => [
					$this->css_selector( ' tbody .day' ) => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_days_style' );

		$this->start_controls_tab(
			'calendar_days_inactive',
			[
				'label' => __( 'Inactive', 'jet-booking' ),
			]
		);

		$this->add_control(
			'calendar_days_inactive_color',
			[
				'type'      => \Elementor\Controls_Manager::COLOR,
				'label'     => __( 'Color', 'jet-booking' ),
				'selectors' => [
					$this->css_selector( ' tbody div.day.invalid' ) => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'calendar_days_inactive_bg_color',
			[
				'type'      => \Elementor\Controls_Manager::COLOR,
				'label'     => __( 'Background', 'jet-booking' ),
				'selectors' => [
					$this->css_selector( ' tbody div.day.invalid' ) => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'calendar_days_inactive_border_color',
			[
				'type'      => \Elementor\Controls_Manager::COLOR,
				'label'     => __( 'Border Color', 'jet-booking' ),
				'selectors' => [
					$this->css_selector( ' tbody div.day.invalid' ) => 'border-color: {{VALUE}}',
				],
				'condition' => [
					'calendar_days_day_border_border!' => '',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'calendar_days_today',
			[
				'label' => __( 'Today', 'jet-booking' ),
			]
		);

		$this->add_control(
			'calendar_days_today_color',
			[
				'type'      => \Elementor\Controls_Manager::COLOR,
				'label'     => __( 'Color', 'jet-booking' ),
				'selectors' => [
					$this->css_selector( ' tbody .day.real-today:not(.invalid)' ) => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'calendar_days_today_bg_color',
			[
				'type'      => \Elementor\Controls_Manager::COLOR,
				'label'     => __( 'Background', 'jet-booking' ),
				'selectors' => [
					$this->css_selector( ' tbody .day.real-today:not(.invalid)' ) => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'calendar_days_today_border_color',
			[
				'label'     => __( 'Border Color', 'jet-booking' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					$this->css_selector( ' tbody .day.real-today:not(.invalid)' ) => 'border-color: {{VALUE}}',
				],
				'condition' => [
					'calendar_days_day_border_border!' => '',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'calendar_days_edges',
			[
				'label' => __( 'Start/End', 'jet-booking' ),
			]
		);

		$this->add_control(
			'calendar_days_edges_color',
			[
				'type'      => \Elementor\Controls_Manager::COLOR,
				'label'     => __( 'Color', 'jet-booking' ),
				'selectors' => [
					$this->css_selector( ' tbody div.day.first-date-selected' ) => 'color: {{VALUE}} !important;',
					$this->css_selector( ' tbody div.day.last-date-selected' )  => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'calendar_days_edges_bg_color',
			[
				'type'      => \Elementor\Controls_Manager::COLOR,
				'label'     => __( 'Background', 'jet-booking' ),
				'selectors' => [
					$this->css_selector( ' tbody div.day.first-date-selected' ) => 'background-color: {{VALUE}} !important;',
					$this->css_selector( ' tbody div.day.last-date-selected' )  => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'calendar_days_edges_border_color',
			[
				'type'      => \Elementor\Controls_Manager::COLOR,
				'label'     => __( 'Border Color', 'jet-booking' ),
				'selectors' => [
					$this->css_selector( ' tbody div.day.first-date-selected' ) => 'border-color: {{VALUE}} !important;',
					$this->css_selector( ' tbody div.day.last-date-selected' )  => 'border-color: {{VALUE}} !important;',
				],
				'condition' => [
					'calendar_days_day_border_border!' => '',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'calendar_days_trace',
			[
				'label' => __( 'Trace', 'jet-booking' ),
			]
		);

		$this->add_control(
			'calendar_days_trace_color',
			[
				'type'      => \Elementor\Controls_Manager::COLOR,
				'label'     => __( 'Color', 'jet-booking' ),
				'selectors' => [
					$this->css_selector( ' tbody div.valid.day.checked' )           => 'color: {{VALUE}}',
					$this->css_selector( ' tbody div.valid.day.hovering' )          => 'color: {{VALUE}}',
					$this->css_selector( ' tbody div.valid.day.has-tooltip:hover' ) => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'calendar_days_trace_bg_color',
			[
				'type'      => \Elementor\Controls_Manager::COLOR,
				'label'     => __( 'Background', 'jet-booking' ),
				'selectors' => [
					$this->css_selector( ' tbody div.valid.day.checked' )                    => 'background-color: {{VALUE}}',
					$this->css_selector( ' tbody div.valid.day.hovering.has-tooltip:hover' ) => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'calendar_days_trace_border_color',
			[
				'label'     => __( 'Border Color', 'jet-booking' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					$this->css_selector( ' tbody div.day.checked' )                    => 'border-color: {{VALUE}}',
					$this->css_selector( ' tbody div.day.hovering.has-tooltip:hover' ) => 'border-color: {{VALUE}}',
				],
				'condition' => [
					'calendar_days_day_border_border!' => '',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name'      => 'calendar_days_day_border',
				'separator' => 'before',
				'selector'  => $this->css_selector( ' tbody .day' ),
			]
		);

		$this->add_responsive_control(
			'calendar_days_border_radius',
			[
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'label'      => __( 'Border Radius', 'jet-booking' ),
				'size_units' => Plugin::instance()->elementor->add_custom_size_unit( [ 'px', 'em', '%' ] ),
				'selectors'  => [
					$this->css_selector( ' tbody .day' ) => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'calendar_days_day_padding',
			[
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'label'      => __( 'Padding', 'jet-booking' ),
				'size_units' => Plugin::instance()->elementor->add_custom_size_unit( [ 'px', 'em', '%' ] ),
				'selectors'  => [
					$this->css_selector( ' tbody .day' ) => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

	}

	/**
	 * Returns CSS selector for nested element
	 *
	 * @param  [type] $el [description]
	 *
	 * @return [type]     [description]
	 */
	public function css_selector( $el = null ) {
		return sprintf( '{{WRAPPER}} .%1$s%2$s', $this->get_name(), $el );
	}

	protected function render() {
		$renderer = new Calendar_Renderer( $this->get_settings() );
		$renderer->render();
	}

}