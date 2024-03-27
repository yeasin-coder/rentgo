<?php

namespace JET_ABAF;

// If this file is called directly, abort.
use JET_ABAF\Form_Fields\Check_In_Out_Render;
use JET_ABAF\Vendor\Actions_Core\Smart_Notification_Trait;
use JET_ABAF\Price;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Plug-in code into JetEngine
 */
class Engine_Plugin {

	use Apartment_Booking_Trait;
	use Smart_Notification_Trait;

	private $done         = false;
	private $deps_added   = false;
	private $booked_dates = array();
	public  $default      = false;
	public  $config       = false;
	public  $namespace    = 'jet-form';

	public function __construct() {

		// Register field for booking form
		add_filter(
			'jet-engine/forms/booking/field-types',
			array( $this, 'register_dates_field' )
		);

		// Regsiter notification for booking form
		add_filter(
			'jet-engine/forms/booking/notification-types',
			array( $this, 'register_booking_notification' )
		);

		add_action(
			'jet-engine/forms/booking/notifications/fields-after',
			array( $this, 'notification_fields' )
		);

		add_filter(
			'jet-engine/calculated-data/ADVANCED_PRICE',
			array( $this, 'macros_advanced_price' ), 10, 2
		);

		add_action(
			'jet-engine/forms/edit-field/before',
			array( $this, 'edit_fields' )
		);

		$check_in_out = new Check_In_Out_Render();
		// Add form field template
		add_action(
			'jet-engine/forms/booking/field-template/check_in_out',
			array( $check_in_out, 'getFieldTemplate' ), 10, 3
		);

		// Register notification handler
		add_filter(
			'jet-engine/forms/booking/notification/apartment_booking',
			array( $this, 'do_action' ), 1, 2
		);

		add_filter(
			'jet-engine/forms/gateways/notifications-before',
			array( $this, 'before_form_gateway' ), 1, 2
		);

		add_filter(
			'jet-engine/forms/handler/query-args',
			[ $this, 'handler_query_args' ], 10, 3
		);

		add_action(
			'jet-engine/forms/gateways/on-payment-success',
			array( $this, 'on_gateway_success' ), 10, 3
		);

		add_action(
			'jet-engine/forms/editor/macros-list',
			array( $this, 'add_macros_list' ), 10, 0
		);

		add_action( 'wp_ajax_jet_booking_check_available_units_count', [ $this, 'check_available_units_count' ] );
		add_action( 'wp_ajax_nopriv_jet_booking_check_available_units_count', [ $this, 'check_available_units_count' ] );

		add_filter( 'jet-engine/listings/macros-list', [ $this, 'register_macros' ] );
		add_filter( 'jet-engine/listing/current-object-title', [ $this, 'get_current_booking_object_title' ], 10, 2 );
		add_filter( 'jet-engine/macros/current-meta', [ $this, 'get_current_booking_meta' ], 10, 3 );

	}

	/**
	 * Check available units count.
	 *
	 * Check available units count for passed/selected dates.
	 *
	 * @since  2.5.2
	 * @access public
	 *
	 * @return void
	 */
	public function check_available_units_count() {

		$booking   = $_POST['booking'] ?? [];
		$all_units = Plugin::instance()->db->get_apartment_units( $booking['apartment_id'] );

		if ( empty( $all_units ) || empty( $fields['check_out_date'] ) || empty( $fields['check_in_date'] ) ) {
			wp_send_json_error();
		}

		$booked_units = Plugin::instance()->db->get_booked_units( $booking );

		if ( empty( $booked_units ) ) {
			wp_send_json_success( [ 'count' => count( $all_units ) ] );
		}

		$available_units = Plugin::instance()->db->get_available_units( $booking );

		wp_send_json_success( [ 'count' => count( $available_units ) ] );

	}

	/**
	 * Set booking appointment notification before gateway
	 *
	 * @param  [type] $keep [description]
	 * @param  [type] $all  [description]
	 *
	 * @return [type]       [description]
	 */
	public function before_form_gateway( $keep, $all ) {

		foreach ( $all as $index => $notification ) {
			if ( 'apartment_booking' === $notification['type'] && ! in_array( $index, $keep ) ) {
				$keep[] = $index;
			}
		}

		return $keep;

	}

	public function handler_query_args( $query_args, $args, $handler_instance ) {
		$field_name = false;

		foreach ( $handler_instance->form_fields as $field ) {
			if ( 'check_in_out' === $field['type'] ) {
				$field_name = $field['name'];
			}
		}

		if ( $field_name ) {
			$query_args['new_date'] = $handler_instance->notifcations->data[ $field_name ];
		}

		return $query_args;
	}

	/**
	 * Finalize booking on internal JetEngine form gateway success
	 *
	 * @return [type] [description]
	 */
	public function on_gateway_success( $form_id, $settings, $form_data ) {

		$booking_id = $form_data['booking_id'];

		if ( $booking_id ) {
			Plugin::instance()->db->update_booking(
				$booking_id,
				array(
					'status' => 'completed',
				)
			);
		}

	}

	/**
	 * Register new dates fields
	 *
	 * @return [type] [description]
	 */
	public function register_dates_field( $fields ) {
		$fields['check_in_out'] = esc_html__( 'Check-in/check-out dates', 'jet-booking' );
		return $fields;
	}

	/**
	 * Register booking notifications
	 *
	 * @param  [type] $notifications [description]
	 *
	 * @return [type]                [description]
	 */
	public function register_booking_notification( $notifications ) {
		$notifications['apartment_booking'] = esc_html__( 'Apartment booking', 'jet-booking' );
		return $notifications;
	}

	/**
	 * Macros _advanced_apartment_price processing in the calculator field
	 *
	 */
	public function macros_advanced_price( $macros, $macros_matches ) {
		return $macros;
	}

	/**
	 * Render additional edit fields
	 *
	 * @return [type] [description]
	 */
	public function edit_fields() {
		?>
		<div class="jet-form-editor__row" v-if="'check_in_out' === currentItem.settings.type">
			<div class="jet-form-editor__row-label"><?php esc_html_e( 'Layout:', 'jet-booking' ); ?></div>
			<div class="jet-form-editor__row-control">
				<select type="text" v-model="currentItem.settings.cio_field_layout">
					<option value="single">
						<?php esc_html_e( 'Single field', 'jet-booking' ); ?>
					</option>
					<option value="separate">
						<?php esc_html_e( 'Separate fields for check in and check out dates', 'jet-booking' ); ?>
					</option>
				</select>
			</div>
		</div>
		<div class="jet-form-editor__row" v-if="'check_in_out' === currentItem.settings.type">
			<div class="jet-form-editor__row-label"><?php esc_html_e( 'Fields position:', 'jet-booking' ); ?></div>
			<div class="jet-form-editor__row-control">
				<select type="text" v-model="currentItem.settings.cio_fields_position">
					<option value="inline">
						<?php esc_html_e( 'Inline', 'jet-booking' ); ?>
					</option>
					<option value="list">
						<?php esc_html_e( 'List', 'jet-booking' ); ?>
					</option>
				</select>
				<div><i>* - For separate fields layout</i></div>
			</div>
		</div>
		<div class="jet-form-editor__row" v-if="'check_in_out' === currentItem.settings.type">
			<div class="jet-form-editor__row-label"><?php esc_html_e( 'Check In field label:', 'jet-booking' ); ?></div>
			<div class="jet-form-editor__row-control">
				<input type="text" v-model="currentItem.settings.first_field_label">
				<div>
					<i>
						* - if you are using separate fields for check in and check out dates,<br> you need to left
						default "Label" empty and use this option for field label
					</i>
				</div>
			</div>
		</div>
		<div class="jet-form-editor__row" v-if="'check_in_out' === currentItem.settings.type">
			<div class="jet-form-editor__row-label"><?php esc_html_e( 'Placeholder:', 'jet-booking' ); ?></div>
			<div class="jet-form-editor__row-control">
				<input type="text" v-model="currentItem.settings.first_field_placeholder">
			</div>
		</div>
		<div class="jet-form-editor__row" v-if="'check_in_out' === currentItem.settings.type">
			<div class="jet-form-editor__row-label"><?php esc_html_e( 'Check Out field label:', 'jet-booking' ); ?></div>
			<div class="jet-form-editor__row-control">
				<input
					type="text"
					placeholder="For separate fields layout"
					v-model="currentItem.settings.second_field_label"
				>
			</div>
		</div>
		<div class="jet-form-editor__row" v-if="'check_in_out' === currentItem.settings.type">
			<div class="jet-form-editor__row-label"><?php esc_html_e( 'Check Out field placeholder:', 'jet-booking' ); ?></div>
			<div class="jet-form-editor__row-control">
				<input
					type="text"
					placeholder="For separate fields layout"
					v-model="currentItem.settings.second_field_placeholder"
				>
			</div>
		</div>
		<div class="jet-form-editor__row" v-if="'check_in_out' === currentItem.settings.type">
			<div class="jet-form-editor__row-label"><?php esc_html_e( 'Date format:', 'jet-booking' ); ?></div>
			<div class="jet-form-editor__row-control">
				<select type="text" v-model="currentItem.settings.cio_fields_format">
					<option value="YYYY-MM-DD">YYYY-MM-DD</option>
					<option value="MM-DD-YYYY">MM-DD-YYYY</option>
					<option value="DD-MM-YYYY">DD-MM-YYYY</option>
				</select>
				<div><i>* - applies only for date in the form checkin/checkout fields</i></div>
				<div><i>* - for `MM-DD-YYYY` date format use the `/` date separator</i></div>
			</div>
		</div>
		<div class="jet-form-editor__row" v-if="'check_in_out' === currentItem.settings.type">
			<div class="jet-form-editor__row-label"><?php esc_html_e( 'Date field separator:', 'jet-booking' ); ?></div>
			<div class="jet-form-editor__row-control">
				<select type="text" v-model="currentItem.settings.cio_fields_separator">
					<option value="-">-</option>
					<option value=".">.</option>
					<option value="/">/</option>
					<option value="space">Space</option>
				</select>
			</div>
		</div>
		<div class="jet-form-editor__row" v-if="'check_in_out' === currentItem.settings.type">
			<div class="jet-form-editor__row-label"><?php esc_html_e( 'First day of the week:', 'jet-booking' ); ?></div>
			<div class="jet-form-editor__row-control">
				<select type="text" v-model="currentItem.settings.start_of_week">
					<option value="monday"><?php esc_html_e( 'Monday', 'jet-booking' ); ?></option>
					<option value="sunday"><?php esc_html_e( 'Sunday', 'jet-booking' ); ?></option>
				</select>
			</div>
		</div>
		<?php
	}

	/**
	 * Render additional notification fields
	 *
	 * @return [type] [description]
	 */
	public function notification_fields() {
		?>
		<div class="jet-form-editor__row" v-if="'apartment_booking' === currentItem.type">
			<div class="jet-form-editor__row-label">
				<?php esc_html_e( 'Apartment ID field:', 'jet-booking' ); ?>
			</div>
			<div class="jet-form-editor__row-control">
				<select v-model="currentItem.booking_apartment_field">
					<option value="">--</option>
					<option v-for="field in availableFields" :value="field">{{ field }}</option>
				</select>
			</div>
		</div>
		<div class="jet-form-editor__row" v-if="'apartment_booking' === currentItem.type">
			<div class="jet-form-editor__row-label">
				<?php esc_html_e( 'Check-in/Check-out date field:', 'jet-booking' ); ?>
			</div>
			<div class="jet-form-editor__row-control">
				<select v-model="currentItem.booking_dates_field">
					<option value="">--</option>
					<option v-for="field in availableFields" :value="field">{{ field }}</option>
				</select>
			</div>
		</div>

		<?php
		$columns = Plugin::instance()->db->get_additional_db_columns();

		if ( $columns ) {
			?>
			<div class="jet-form-editor__row" v-if="'apartment_booking' === currentItem.type">
				<div class="jet-form-editor__row-label">
					<?php _e( 'DB columns map:', 'jet-booking' ); ?>
					<div class="jet-form-editor__row-notice">
						<?php _e( 'Set up connection between form fields and additional database table columns. This allows you to save entered field data in the corresponding DB column.', 'jet-booking' ); ?>
					</div>
				</div>
				<div class="jet-form-editor__row-fields">
					<?php foreach ( $columns as $column ) : ?>
						<div class="jet-form-editor__row-map">
							<span><?php echo $column; ?></span>
							<input type="text" v-model="currentItem.db_columns_map_<?php echo $column; ?>">
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			<?php
		}

		if ( Plugin::instance()->settings->get( 'wc_integration' ) ) { ?>
			<div class="jet-form-editor__row" v-if="'apartment_booking' === currentItem.type">
				<div class="jet-form-editor__row-label">
					<?php esc_html_e( 'Disable WooCommerce integration', 'jet-booking' ); ?>
					<div class="jet-form-editor__row-notice">
						<?php esc_html_e( 'Check to disable WooCommerce integration and disconnect the booking system with WooCommerce checkout for current form.', 'jet-booking' ); ?>
					</div>
				</div>
				<div class="jet-form-editor__row-control">
					<input type="checkbox" v-model="currentItem.disable_wc_integration">
				</div>
			</div>
			<div
				class="jet-form-editor__row"
				v-if="'apartment_booking' === currentItem.type && ! currentItem.disable_wc_integration"
			>
				<div class="jet-form-editor__row-label">
					<?php esc_html_e( 'WooCommerce Price field:', 'jet-booking' ); ?>
					<div class="jet-form-editor__row-notice">
						<?php esc_html_e( 'Select field to get total price from. If not selected price will be get from post meta value.', 'jet-booking' ); ?>
					</div>
				</div>
				<div class="jet-form-editor__row-control">
					<select v-model="currentItem.booking_wc_price">
						<option value="">--</option>
						<option v-for="field in availableFields" :value="field">{{ field }}</option>
					</select>
				</div>
			</div>
			<div
				class="jet-form-editor__row"
				v-if="'apartment_booking' === currentItem.type && ! currentItem.disable_wc_integration"
			>
				<div class="jet-form-editor__row-label">
					<?php esc_html_e( 'WooCommerce order details:', 'jet-booking' ); ?>
					<div class="jet-form-editor__row-notice">
						<?php esc_html_e( 'Set up booking-related info you want to add to the WooCommerce orders and e-mails', 'jet-booking' ); ?>
					</div>
				</div>
				<div class="jet-form-editor__row-control">
					<button type="button" class="button button-secondary" id="jet-booking-wc-details">
						<?php esc_html_e( 'Set up', 'jet-booking' ); ?>
					</button>
				</div>
			</div>
			<div
				class="jet-form-editor__row"
				v-if="'apartment_booking' === currentItem.type && ! currentItem.disable_wc_integration"
			>
				<div class="jet-form-editor__row-label">
					<?php esc_html_e( 'WooCommerce checkout fields map:', 'jet-booking' ); ?>
					<div class="jet-form-editor__row-notice">
						<?php esc_html_e( 'Connect WooCommerce checkout fields to appropriate form fields. This allows you to pre-fill WooCommerce checkout fields after redirect to checkout.', 'jet-booking' ); ?>
					</div>
				</div>
				<div class="jet-form-editor__row-fields jet-wc-checkout-fields">
					<?php foreach ( Plugin::instance()->wc->get_checkout_fields() as $field ) { ?>
						<div class="jet-form-editor__row-map">
							<span><?php echo $field; ?></span>
							<select v-model="currentItem.wc_fields_map__<?php echo $field; ?>">
								<option value="">--</option>
								<option v-for="field in availableFields" :value="field">{{ field }}</option>
							</select>
						</div>
					<?php } ?>
				</div>
			</div>
			<?php
		}

	}

	/**
	 * Is per night bookings.
	 *
	 * Check if per nights booking mode enable.
	 *
	 * @since  2.8.0 Optimization.
	 * @access public
	 *
	 * @return boolean
	 */
	public function is_per_nights_booking() {

		$period = Plugin::instance()->settings->get( 'booking_period' );

		if ( ! $period || 'per_nights' === $period ) {
			return true;
		}

		return false;

	}

	public function enqueue_deps( $post_id ) {

		if ( ! $post_id || $this->deps_added ) {
			return;
		}

		do_action( 'jet-booking/assets/before' );

		ob_start();

		include JET_ABAF_PATH . 'assets/js/booking-init.js';

		$init_datepicker = ob_get_clean();
		$handle          = 'jquery-date-range-picker';

		wp_register_script(
			'jet-plugins',
			JET_ABAF_URL . 'assets/lib/jet-plugins/jet-plugins.js',
			[ 'jquery' ],
			'1.1.0',
			true
		);

		wp_register_script(
			'moment-js',
			JET_ABAF_URL . 'assets/lib/moment/js/moment.js',
			array(),
			'2.4.0',
			true
		);

		wp_enqueue_script(
			$handle,
			JET_ABAF_URL . 'assets/lib/jquery-date-range-picker/js/daterangepicker.min.js',
			[ 'jquery', 'moment-js', 'jet-plugins' ],
			JET_ABAF_VERSION,
			true
		);

		wp_add_inline_script( $handle, $init_datepicker );

		$weekly_bookings  = $this->get_config_option( $post_id, 'weekly_bookings' );
		$week_offset      = false;
		$one_day_bookings = false;

		if ( ! $weekly_bookings ) {
			$one_day_bookings = Plugin::instance()->settings->is_one_day_bookings( $post_id );
		} else {
			$week_offset = $this->get_config_option( $post_id, 'week_offset' );
		}

		if ( $weekly_bookings || $one_day_bookings ) {
			$this->default = false;
		}

		$css_url = add_query_arg(
			[ 'v' => JET_ABAF_VERSION ],
			JET_ABAF_URL . 'assets/lib/jquery-date-range-picker/css/daterangepicker.min.css'
		);

		$booked_dates    = $this->get_off_dates( $post_id );
		$apartment_price = new Price( $post_id );

		wp_localize_script( $handle, 'JetABAFData', apply_filters( 'jet-booking/assets/config', [
			'css_url'          => $css_url,
			'booked_dates'     => $booked_dates,
			'booked_next'      => $this->get_next_booked_dates( $booked_dates ),
			'checkout_only'    => Plugin::instance()->settings->checkout_only_allowed(),
			'days_off'         => $this->get_booking_days_off( $post_id ),
			'disabled_days'    => $this->get_days_by_rule( $post_id ),
			'check_in_days'    => $this->get_days_by_rule( $post_id, 'check_in' ),
			'check_out_days'   => $this->get_days_by_rule( $post_id, 'check_out' ),
			'custom_labels'    => Plugin::instance()->settings->get( 'use_custom_labels' ),
			'labels'           => apply_filters( 'jet-booking/compatibility/translate-labels', Plugin::instance()->settings->get_labels() ),
			'weekly_bookings'  => $weekly_bookings,
			'week_offset'      => $week_offset,
			'one_day_bookings' => $one_day_bookings,
			'per_nights'       => $this->is_per_nights_booking(),
			'start_day_offset' => $this->get_config_option( $post_id, 'start_day_offset' ),
			'min_days'         => ! empty( $this->get_config_option( $post_id, 'min_days' ) ) ? $this->get_config_option( $post_id, 'min_days' ) : ( $this->is_per_nights_booking() ? 1 : '' ),
			'max_days'         => $this->get_config_option( $post_id, 'max_days' ),
			'bace_price'       => [
				'price'         => $apartment_price->get_default_price(),
				'price_rates'   => $apartment_price->rates_price->get_rates(),
				'weekend_price' => $apartment_price->weekend_price->get_price(),
			],
			'seasonal_price'   => $apartment_price->seasonal_price->get_price(),
			'post_id'          => $post_id,
			'ajax_url'         => esc_url( admin_url( 'admin-ajax.php' ) ),
		] ) );

		do_action( 'jet-booking/assets/after' );

		$this->deps_added = true;

	}

	/**
	 * Get config option.
	 *
	 * Returns option for date range picker configuration.
	 *
	 * @since  2.6.0
	 * @access public
	 *
	 * @param integer $post_id Post ID.
	 * @param string  $key     Options name key.
	 *
	 * @return mixed
	 */
	public function get_config_option( $post_id, $key ) {

		$option = Plugin::instance()->settings->get( $key );

		if ( ! $this->config ) {
			$this->config = get_post_meta( $post_id, 'jet_abaf_configuration', true );
		}

		if ( isset( $this->config['config'] ) && $this->config['config']['enable_config'] ) {
			$option = $this->config['config'][ $key ] ?? $option;
		}

		return $option;

	}

	public function get_next_booked_dates( $booked_dates ) {

		$result = [];

		if ( ! Plugin::instance()->settings->checkout_only_allowed() ) {
			return $result;
		}

		foreach ( $booked_dates as $index => $date ) {
			$next_date = date( 'Y-m-d', strtotime( $date ) + DAY_IN_SECONDS );
			$prev_date = date( 'Y-m-d', strtotime( $date ) - DAY_IN_SECONDS );

			if ( ! in_array( $next_date, $booked_dates ) && ! in_array( $prev_date, $booked_dates ) ) {
				$result[] = $next_date;
			}
		}

		return $result;

	}

	public function ensure_ajax_js() {
		if ( wp_doing_ajax() ) {
			wp_scripts()->done[] = 'jquery';
			wp_scripts()->print_scripts( 'jquery-date-range-picker' );
		}
	}

	/**
	 * Schedule settings.
	 *
	 * Return custom schedule settings list for specific post or global.
	 *
	 * @since  2.5.0
	 * @since  2.8.0 Code refactor.
	 * @access public
	 *
	 * @param null $post_id       Booking post type ID.
	 * @param null $default_value Default schedule value.
	 * @param null $meta_key      Post type meta value key.
	 *
	 * @return mixed|void
	 */
	public function get_schedule_settings( $post_id = null, $default_value = null, $meta_key = null ) {

		$schedule         = null;
		$post_schedule    = get_post_meta( $post_id, 'jet_abaf_custom_schedule', true );
		$general_schedule = Plugin::instance()->settings->get( $meta_key ) ?? $default_value;

		if ( ! isset( $post_schedule['custom_schedule'] ) || ! $post_schedule['custom_schedule']['use_custom_schedule'] ) {
			$schedule = $general_schedule;
		} elseif ( isset( $post_schedule['custom_schedule'][ $meta_key ] ) ) {
			$schedule = $post_schedule['custom_schedule'][ $meta_key ] ?? $general_schedule;
		}

		return apply_filters( 'jet-abaf/calendar/custom-schedule', $schedule, $meta_key, $default_value, $post_id );

	}

	/**
	 * Get days by rule.
	 *
	 * Returns list of days by passed rule.
	 *
	 * @since  2.8.0
	 * @access public
	 *
	 * @param string|number $post_id Booking post type id.
	 * @param string        $type    Rule type.
	 *
	 * @return array
	 */
	public function get_days_by_rule( $post_id = null, $type = 'disable' ) {

		if ( ! $post_id ) {
			return [];
		}

		$days          = [];
		$post_schedule = get_post_meta( $post_id, 'jet_abaf_custom_schedule', true );

		$rules = [
			$type . '_weekend_2',
			$type . '_weekday_1',
			$type . '_weekday_2',
			$type . '_weekday_3',
			$type . '_weekday_4',
			$type . '_weekday_5',
			$type . '_weekend_1',
		];

		if ( ! isset( $post_schedule['custom_schedule'] ) || ! $post_schedule['custom_schedule']['use_custom_schedule'] ) {
			foreach ( $rules as $key => $value ) {
				if ( Plugin::instance()->settings->get( $value ) ) {
					$days[] = $key;
				}
			}
		} else {
			foreach ( $rules as $key => $value ) {
				if ( isset( $post_schedule['custom_schedule'][ $value ] ) && filter_var( $post_schedule['custom_schedule'][ $value ], FILTER_VALIDATE_BOOLEAN ) ) {
					$days[] = $key;
				}
			}
		}

		if ( 'disable' === $type ) {
			return $days;
		}

		$disabled_days = $this->get_days_by_rule( $post_id );

		if ( empty( $disabled_days ) ) {
			return $days;
		}

		foreach ( $disabled_days as $day ) {
			if ( ( $key = array_search( $day, $days ) ) !== false ) {
				unset( $days[ $key ] );
			}
		}

		return array_values( $days );

	}

	/**
	 * Booking days off.
	 *
	 * Returns booking days off - official days off.
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @param int $post_id Booking post type ID.
	 *
	 * @return array List of days off.
	 */
	public function get_booking_days_off( $post_id ) {

		$days_off = $this->get_schedule_settings( $post_id, null, 'days_off' );
		$dates    = [];

		if ( empty( $days_off ) ) {
			return $dates;
		}

		foreach ( $days_off as $day ) {
			$from = new \DateTime( date( 'F d, Y', $day['startTimeStamp'] ) );
			$to   = new \DateTime( date( 'F d, Y', $day['endTimeStamp'] ) );

			if ( $to->format( 'Y-m-d' ) === $from->format( 'Y-m-d' ) ) {
				$dates[] = $from->format( 'Y-m-d' );
			} else {
				$to     = $to->modify( '+1 day' );
				$period = new \DatePeriod( $from, new \DateInterval( 'P1D' ), $to );

				foreach ( $period as $key => $value ) {
					$dates[] = $value->format( 'Y-m-d' );
				}
			}
		}

		return $dates;

	}

	/**
	 * Booked dates.
	 *
	 * Returns booked dates list.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return array List of booked dates.
	 */
	public function get_booked_dates( $post_id ) {

		$bookings = Plugin::instance()->db->get_future_bookings( $post_id );

		if ( empty( $bookings ) ) {
			return [];
		}

		$units           = Plugin::instance()->db->get_apartment_units( $post_id );
		$units_num       = ! empty( $units ) ? count( $units ) : 0;
		$weekly_bookings = $this->get_config_option( $post_id, 'weekly_bookings' );
		$week_offset     = $this->get_config_option( $post_id, 'week_offset' );
		$skip_statuses   = Plugin::instance()->statuses->invalid_statuses();
		$skip_statuses[] = Plugin::instance()->statuses->temporary_status();
		$dates           = [];

		if ( ! $units_num || 1 === $units_num ) {
			foreach ( $bookings as $booking ) {
				if ( ! empty( $booking['status'] ) && in_array( $booking['status'], $skip_statuses ) ) {
					continue;
				}

				$from = new \DateTime( date( 'F d, Y', $booking['check_in_date'] ) );
				$to   = new \DateTime( date( 'F d, Y', $booking['check_out_date'] ) );

				if ( $weekly_bookings && ! $week_offset || ! $this->is_per_nights_booking() ) {
					$to = $to->modify( '+1 day' );
				}

				if ( $to->format( 'Y-m-d' ) === $from->format( 'Y-m-d' ) ) {
					$dates[] = $from->format( 'Y-m-d' );
				} else {
					$period = new \DatePeriod( $from, new \DateInterval( 'P1D' ), $to );

					foreach ( $period as $key => $value ) {
						$dates[] = $value->format( 'Y-m-d' );
					}
				}
			}
		} else {
			$booked_units = [];

			foreach ( $bookings as $booking ) {
				if ( ! empty( $booking['status'] ) && in_array( $booking['status'], $skip_statuses ) ) {
					continue;
				}

				$from = new \DateTime( date( 'F d, Y', $booking['check_in_date'] ) );
				$to   = new \DateTime( date( 'F d, Y', $booking['check_out_date'] ) );

				if ( $weekly_bookings && ! $week_offset || ! $this->is_per_nights_booking() ) {
					$to = $to->modify( '+1 day' );
				}

				if ( $to->format( 'Y-m-d' ) === $from->format( 'Y-m-d' ) ) {
					if ( empty( $booked_units[ $from->format( 'Y-m-d' ) ] ) ) {
						$booked_units[ $from->format( 'Y-m-d' ) ] = 1;
					} else {
						$booked_units[ $from->format( 'Y-m-d' ) ]++;
					}
				} else {
					$period = new \DatePeriod( $from, new \DateInterval( 'P1D' ), $to );

					foreach ( $period as $key => $value ) {
						if ( empty( $booked_units[ $value->format( 'Y-m-d' ) ] ) ) {
							$booked_units[ $value->format( 'Y-m-d' ) ] = 1;
						} else {
							$booked_units[ $value->format( 'Y-m-d' ) ]++;
						}
					}
				}
			}

			foreach ( $booked_units as $date => $booked_units_num ) {
				if ( $units_num <= $booked_units_num ) {
					$dates[] = $date;
				}
			}
		}

		return $dates;

	}

	/**
	 * Off dates.
	 *
	 * Returns off dates - official days off and booked dates.
	 *
	 * @since  2.5.0
	 * @since  2.5.5 Added additional `$post_id` handling.
	 * @access public
	 *
	 * @param int $post_id Booking post type ID.
	 *
	 * @return array|mixed
	 */
	public function get_off_dates( $post_id ) {

		$post_id = Plugin::instance()->db->get_initial_booking_item_id( $post_id );

		if ( isset( $this->booked_dates[ $post_id ] ) ) {
			return $this->booked_dates[ $post_id ];
		}

		$booked_dates = $this->get_booked_dates( $post_id );
		$days_off     = $this->get_booking_days_off( $post_id );

		if ( empty( $booked_dates ) && empty( $days_off ) ) {
			$this->booked_dates[ $post_id ] = [];

			return [];
		}

		$dates = array_merge( $booked_dates, $days_off );

		$this->booked_dates[ $post_id ] = $dates;

		return $dates;

	}

	/**
	 * Adds a macro description to the calculator field
	 *
	 * @return void
	 */
	function add_macros_list() {
		?>
		<br>
		<div><b><?php esc_html_e( 'Booking macros:', 'jet-booking' ); ?></b></div>
		<div><i>%ADVANCED_PRICE::_check_in_out%</i>
			- <?php esc_html_e( 'The macro will return the advanced rate times the number of days booked.', 'jet-booking' ); ?>
			<b>_check_in_out</b> <?php esc_html_e( ' - is the name of the field that returns the number of days booked.', 'jet-booking' ); ?>
		</div><br>
		<div><i>%META::_apartment_price%</i>
			- <?php esc_html_e( 'Macro returns price per 1 day / night', 'jet-booking' ); ?></div>
		<?php
	}

	/**
	 * Register macros.
	 *
	 * Registers and returns specific macros list for booking functionality.
	 *
	 * @since  2.7.0
	 * @access public
	 *
	 * @return array
	 */
	public function register_macros() {

		$macros_list['booking_unit_title'] = [
			'label' => __( 'JetBooking: Unit Title', 'jet-engine' ),
			'cb'    => [ $this, 'get_unit_title' ],
		];

		$macros_list['booking_status'] = [
			'label' => __( 'JetBooking: Status', 'jet-engine' ),
			'cb'    => [ $this, 'get_booking_status' ],
		];

		return $macros_list;

	}

	/**
	 * Get unit title.
	 *
	 * Returns unit name in units is set.
	 *
	 * @since  2.7.0
	 * @access public
	 *
	 * @return mixed|string
	 */
	public function get_unit_title() {

		$booking = jet_engine()->listings->data->get_current_object();

		if ( ! $booking ) {
			return '';
		}

		$apartment_id = ! empty( $booking->apartment_id ) ? absint( $booking->apartment_id ) : null;
		$unit_id      = ! empty( $booking->apartment_unit ) ? absint( $booking->apartment_unit ) : null;

		if ( ! $apartment_id || ! $unit_id ) {
			return '';
		}

		$unit = Plugin::instance()->db->get_apartment_unit( $apartment_id, $unit_id );

		if ( empty( $unit ) ) {
			return '';
		}

		return ! empty( $unit[0]['unit_title'] ) ? $unit[0]['unit_title'] : 'Unit-' . $unit_id;

	}

	/**
	 * Get booking status.
	 *
	 * Return current status of booking instance.
	 *
	 * @since  2.7.0
	 * @access public
	 *
	 * @return string
	 */
	public function get_booking_status() {

		$booking = jet_engine()->listings->data->get_current_object();

		if ( ! $booking ) {
			return '';
		}

		return ! empty( $booking->status ) ? $booking->status : Plugin::instance()->statuses->temporary_status();

	}

	/**
	 * Get current booking object title.
	 *
	 * Returns booking instance title of current booking item.
	 *
	 * @since  2.7.0
	 * @access public
	 *
	 * @param string $title  Current object title.
	 * @param object $object Current object instance.
	 *
	 * @return string
	 */
	public function get_current_booking_object_title( $title, $object ) {

		if ( ! $object || empty( $object->apartment_id ) ) {
			return $title;
		}

		return get_the_title( $object->apartment_id );

	}

	/**
	 * Get current booking meta.
	 *
	 * Returns meta value of booking instance for current booking item.
	 *
	 * @since  2.7.0
	 * @access public
	 *
	 * @param boolean $meta_value Initial value.
	 * @param object  $object     Current object instance.
	 * @param string  $meta_key   Meta field key name.
	 *
	 * @return mixed
	 */
	public function get_current_booking_meta( $meta_value, $object, $meta_key ) {

		if ( ! $object || empty( $object->apartment_id ) ) {
			return $meta_value;
		}

		return get_post_meta( $object->apartment_id, $meta_key, true );

	}

}
