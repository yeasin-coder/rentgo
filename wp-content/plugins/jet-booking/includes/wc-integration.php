<?php

namespace JET_ABAF;

/**
 * WooCommerce integration class
 *
 * @property WC_Order_Details_Builder details
 *
 * Class WC_Integration
 * @package JET_ABAF
 */
class WC_Integration {

	use Wc_Integration_Trait;

	private $is_enbaled     = false;
	private $product_id     = 0;
	private $price_adjusted = false;
	public  $product_key    = '_is_jet_booking';
	public  $data_key       = 'booking_data';
	public  $price_key      = 'wc_booking_price';
	public  $form_data_key  = 'booking_form_data';
	public  $form_id_key    = 'booking_form_id';
	public  $details;

	/**
	 * Constructor for the class
	 */
	public function __construct() {

		if ( ! $this->has_woocommerce() ) {
			add_action( 'jet-abaf/settings/before-write', [ $this, 'reset_setting' ] );
			return;
		}

		add_action( 'jet-abaf/settings/before-write', array( $this, 'maybe_create_booking_product' ) );

		$this->set_status();

		if ( ! $this->get_status() || ! $this->get_product_id() ) {
			return;
		}

		// Form-related
		add_action( 'jet-abaf/form/notification/success', array( $this, 'process_wc_notification' ), 10, 2 );
		add_action( 'jet-abaf/jet-fb/action/success', array( $this, 'process_wc_notification' ), 10, 2 );

		// Cart related
		add_filter( 'woocommerce_get_item_data', array( $this, 'add_formatted_cart_data' ), 10, 2 );
		add_filter( 'woocommerce_get_cart_contents', array( $this, 'set_booking_price' ) );
		add_filter( 'woocommerce_cart_item_name', [ $this, 'set_booking_item_name' ], 10, 2 );
		add_filter( 'woocommerce_checkout_get_value', array( $this, 'maybe_set_checkout_defaults' ), 10, 2 );

		// Order related
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'process_order' ), 10, 3 );
		add_action( 'woocommerce_store_api_checkout_order_processed', [ $this, 'process_order_by_api' ] );
		add_action( 'woocommerce_thankyou', array( $this, 'order_details' ), 0 );
		add_action( 'woocommerce_view_order', array( $this, 'order_details' ), 0 );
		add_action( 'woocommerce_email_order_meta', array( $this, 'email_order_details' ), 0, 3 );
		add_action( 'woocommerce_admin_order_data_after_shipping_address', array( $this, 'admin_order_details' ) );
		add_action( 'woocommerce_order_status_changed', array( $this, 'update_status_on_order_update' ), 10, 4 );
		add_filter( 'woocommerce_order_item_name', [ $this, 'set_booking_item_name' ], 10, 2 );

		if ( Plugin::instance()->settings->get( 'wc_sync_orders' ) ) {
			add_action( 'jet-booking/db/booking-updated', [ $this, 'update_order_on_status_update' ], 10 );
		}

		$this->details = new WC_Order_Details_Builder();

	}

	/**
	 * Has WooCommerce.
	 *
	 * Check if WooCommerce plugin is enabled.
	 *
	 * @since  2.8.0
	 * @access public
	 *
	 * @return boolean
	 */
	public function has_woocommerce() {
		return class_exists( '\WooCommerce' );
	}

	/**
	 * Reset setting.
	 *
	 * If WC is disabled, ensure wc_integration is also disabled.
	 *
	 * @since  2.8.0.
	 * @access public
	 *
	 * @param object $settings Global settings object instance.
	 *
	 * @return void
	 */
	public function reset_setting( $settings ) {

		if ( ! $settings->get( 'wc_integration' ) ) {
			return;
		}

		$settings->update( 'wc_integration', false, false );

	}

	/**
	 * Maybe set checkout defaults.
	 *
	 * Set checkout default fields values for checkout forms.
	 *
	 * @since  2.8.0 Refactored.
	 * @access public
	 *
	 * @param mixed  $value Initial value of the input.
	 * @param string $input Name of the input we want to set data for. e.g. billing_country.
	 *
	 * @return mixed The default value.
	 */
	public function maybe_set_checkout_defaults( $value, $input ) {
		if ( function_exists( 'WC' ) && WC()->session ) {
			$fields = WC()->session->get( 'jet_booking_fields' );

			if ( ! empty( $fields ) && ! empty( $fields[ $input ] ) ) {
				return $fields[ $input ];
			} else {
				return $value;
			}
		} else {
			return $value;
		}
	}

	/**
	 * Returns checkout fields list
	 */
	public function get_checkout_fields() {

		if ( ! $this->get_status() ) {
			return array();
		}

		$result = array(
			'billing_first_name',
			'billing_last_name',
			'billing_email',
			'billing_phone',
			'billing_company',
			'billing_country',
			'billing_address_1',
			'billing_address_2',
			'billing_city',
			'billing_state',
			'billing_postcode',
			'shipping_first_name',
			'shipping_last_name',
			'shipping_company',
			'shipping_country',
			'shipping_address_1',
			'shipping_address_2',
			'shipping_city',
			'shipping_state',
			'shipping_postcode',
			'order_comments',
		);

		return apply_filters( 'jet-booking/wc-integration/checkout-fields', $result );

	}

	/**
	 * Update order on status update.
	 *
	 * Update an order status on related booking update.
	 *
	 * @since  2.8.0
	 * @access public
	 *
	 * @param string|int $booking_id Booking ID.
	 *
	 * @return void
	 */
	public function update_order_on_status_update( $booking_id ) {

		$booking = jet_abaf()->db->get_booking_by( 'booking_id', $booking_id );

		if ( ! $booking || empty( $booking['status'] ) ) {
			return;
		}

		$order_id = $booking['order_id'] ?? false;

		if ( ! $order_id ) {
			return;
		}

		$order  = wc_get_order( $order_id );
		$status = $booking['status'];

		if ( ! $order || $order->get_status() === $status ) {
			return;
		}

		remove_action( 'woocommerce_order_status_changed', [ $this, 'update_status_on_order_update' ], 10 );

		$order->update_status( $status, sprintf( __( 'Booking #%d update.', 'jet-booking' ), $booking_id ), true );

	}

	/**
	 * Update an booking status on related order update
	 *
	 * @return [type] [description]
	 */
	public function update_status_on_order_update( $order_id, $old_status, $new_status, $order ) {

		$booking = $this->get_booking_by_order_id( $order_id );

		if ( ! $booking ) {
			return;
		}

		$this->set_order_data( $booking, $order_id, $order );

	}

	/**
	 * Process order by API.
	 *
	 * Process new order creation with new checkout block API.
	 *
	 * @since  2.7.1
	 * @access public
	 *
	 * @param object $order WC order instance.
	 *
	 * @return void
	 */
	public function process_order_by_api( $order ) {
		$this->process_order( $order->get_id(), [], $order );
	}

	/**
	 * Process new order creation
	 *
	 * @param  [type] $order [description]
	 * @param  [type] $data  [description]
	 *
	 * @return [type]        [description]
	 */
	public function process_order( $order_id, $data, $order ) {

		$cart = WC()->cart->get_cart_contents();

		foreach ( $cart as $item ) {
			if ( ! empty( $item[ $this->data_key ] ) ) {
				$this->set_order_data(
					$item[ $this->data_key ],
					$order_id,
					$order,
					$item
				);
			}
		}

	}

	/**
	 * Setup order data.
	 *
	 * @since  2.8.0 Added `wc_sync_orders` option handling.
	 * @access public
	 *
	 * @param array      $data     Booking item data.
	 * @param string|int $order_id Order ID.
	 * @param object     $order    Order instance.
	 * @param array      $cart_item
	 *
	 * @return void
	 */
	public function set_order_data( $data, $order_id, $order, $cart_item = [] ) {

		$booking_id = ! empty( $data['booking_id'] ) ? absint( $data['booking_id'] ) : false;

		if ( ! $booking_id ) {
			return;
		}

		if ( Plugin::instance()->settings->get( 'wc_sync_orders' ) ) {
			remove_action( 'jet-booking/db/booking-updated', [ $this, 'update_order_on_status_update' ], 10 );
		}

		Plugin::instance()->db->update_booking(
			$booking_id,
			[
				'order_id' => $order_id,
				'status'   => $order->get_status(),
			]
		);

		do_action( 'jet-booking/wc-integration/process-order', $order_id, $order, $cart_item );

	}

	/**
	 * Set booking name.
	 *
	 * Set booking item name for checkout, thank you page and e-mail order details.
	 *
	 * @since 2.0.0
	 * @since 2.6.0 Refactor code. Added thank you page and e-mail orders item name handling. Added link for items
	 *        names.
	 *
	 * @param string       $title Product name.
	 * @param array|object $item  WooCommerce item data list or Instance.
	 *
	 * @return string;
	 */
	public function set_booking_item_name( $title, $item ) {

		$booking = [];

		if ( ! empty( $item[ $this->data_key ] ) ) {
			$booking = $item[ $this->data_key ];
		} elseif ( is_object( $item ) && Plugin::instance()->settings->get( 'wc_product_id' ) === $item->get_product_id() ) {
			$booking = $this->get_booking_by_order_id( $item->get_order_id() );
		}

		$apartment_id = ! empty( $booking['apartment_id'] ) ? absint( $booking['apartment_id'] ) : false;
		$apartment_id = apply_filters( 'jet-booking/wc-integration/apartment-id', $apartment_id );

		if ( ! $apartment_id ) {
			return $title;
		}

		return sprintf(
			'%s: <a href="%s">%s</a>',
			$this->get_apartment_label(),
			get_permalink( $apartment_id ),
			get_the_title( $apartment_id )
		);

	}

	/**
	 * Set booking price.
	 *
	 * Set custom price per booking item.
	 *
	 * @since  2.8.0 Refactored.
	 * @access public
	 *
	 * @param array $cart_items List cart items.
	 *
	 * @return mixed
	 */
	public function set_booking_price( $cart_items ) {

		if ( $this->price_adjusted || empty( $cart_items ) ) {
			return $cart_items;
		}

		foreach ( $cart_items as $item ) {
			if ( ! empty( $item[ $this->data_key ] ) ) {
				$data  = $item[ $this->data_key ];
				$price = ! empty( $item[ $this->price_key ] ) ? $item[ $this->price_key ] : $this->get_booking_price( $data );

				if ( $price ) {
					$item['data']->set_price( floatval( $price ) );
				}

				$this->price_adjusted = true;
			}
		}

		return $cart_items;

	}

	/**
	 * Get booking price.
	 *
	 * Return booking item price for selected date range with seasonal price check.
	 *
	 * @since  2.8.0
	 * @access public
	 *
	 * @param array $data Booking item parameters.
	 *
	 * @return float|int
	 */
	public function get_booking_price( $data ) {

		$apartment_id = ! empty( $data['apartment_id'] ) ? $data['apartment_id'] : 0;
		$price        = get_post_meta( $apartment_id, '_apartment_price', true );
		$difference   = ceil( ( $data['check_out_date'] - $data['check_in_date'] ) / DAY_IN_SECONDS );

		if ( ! Plugin::instance()->engine_plugin->is_per_nights_booking() ) {
			$difference++;
		}

		$advanced_price_rates = new Advanced_Price_Rates( $apartment_id );
		$rates                = $advanced_price_rates->get_rates();

		if ( ! empty( $rates ) ) {
			foreach ( $rates as $rate ) {
				$duration = absint( $rate['duration'] );

				if ( $difference >= $duration ) {
					$price = $rate['value'];
				}
			}
		}

		return floatval( $price ) * $difference;

	}

	/**
	 * Add booking infor,ation into cart meta data
	 *
	 * @param [type] $item_data [description]
	 * @param [type] $cart_item [description]
	 */
	public function add_formatted_cart_data( $item_data, $cart_item ) {

		if ( ! empty( $cart_item[ $this->data_key ] ) ) {
			$item_data = array_merge(
				$item_data,
				$this->get_formatted_info(
					$cart_item[ $this->data_key ],
					$cart_item[ $this->form_data_key ],
					$cart_item[ $this->form_id_key ]
				)
			);
		}

		return $item_data;

	}

	public function order_details_template( $order_id, $template = 'order-details' ) {

		$details = $this->get_booking_order_details( $order_id );

		if ( ! $details ) {
			return;
		}

		include Plugin::instance()->get_template( $template . '.php' );

	}

	/**
	 * Show booking-related order details on order page
	 *
	 * @param  [type] $order_id [description]
	 *
	 * @return [type]           [description]
	 */
	public function order_details( $order_id ) {
		$this->order_details_template( $order_id );
	}

	/**
	 * Show booking-related order details on order page
	 *
	 * @param  [type] $order_id [description]
	 *
	 * @return [type]           [description]
	 */
	public function email_order_details( $order, $sent_to_admin, $plain_text ) {

		if ( $plain_text ) {
			$template = 'email-order-details-plain';
		} else {
			$template = 'email-order-details-html';
		}

		$this->order_details_template( $order->get_id(), $template );

	}

	/**
	 * Returns booking detail by order id
	 *
	 * @return [type] [description]
	 */
	public function get_booking_by_order_id( $order_id ) {

		$booking = Plugin::instance()->db->get_booking_by( 'order_id', $order_id );

		if ( ! $booking || ! $booking['apartment_id'] ) {
			return false;
		}

		return $booking;

	}

	/**
	 * Admin order details.
	 *
	 * Returns booking order details template in WooCommerce order view.
	 *
	 * @since  2.4.4
	 * @access public
	 *
	 * @param object $order WooCommerce order instance.
	 *
	 * @return void
	 */
	public function admin_order_details( $order ) {

		$details = $this->get_booking_order_details( $order->get_id() );

		if ( ! $details ) {
			return;
		}

		include JET_ABAF_PATH . 'templates/admin/order/details.php';

	}

	/**
	 * Booking order details.
	 *
	 * Returns sanitized booking order details.
	 *
	 * @since  2.4.4
	 * @access public
	 *
	 * @param int $order_id WooCommerce order ID.
	 *
	 * @return mixed
	 */
	public function get_booking_order_details( $order_id ) {

		$booking = $this->get_booking_by_order_id( $order_id );

		if ( ! $booking ) {
			return;
		}

		$details = apply_filters( 'jet-booking/wc-integration/pre-get-order-details', false, $order_id, $booking );

		if ( ! $details ) {
			$booking_title = get_the_title( $booking['apartment_id'] );
			$from          = ! empty( $booking['check_in_date'] ) ? absint( $booking['check_in_date'] ) : false;
			$to            = ! empty( $booking['check_out_date'] ) ? absint( $booking['check_out_date'] ) : false;

			if ( ! $from || ! $to ) {
				return;
			}

			$from = date_i18n( get_option( 'date_format' ), $from );
			$to   = date_i18n( get_option( 'date_format' ), $to );

			$details = array(
				array(
					'key'     => '',
					'display' => $booking_title,
				),
				array(
					'key'     => __( 'Check In', 'jet-booking' ),
					'display' => $from,
				),
				array(
					'key'     => __( 'Check Out', 'jet-booking' ),
					'display' => $to,
				),
			);

		}

		return apply_filters( 'jet-booking/wc-integration/order-details', $details, $order_id, $booking );

	}

	/**
	 * Get formatted booking information
	 *
	 * @return [type] [description]
	 */
	public function get_formatted_info( $data = array(), $form_data = array(), $form_id = null ) {

		$pre_cart_info = apply_filters(
			'jet-booking/wc-integration/pre-cart-info',
			false, $data, $form_data, $form_id
		);

		if ( $pre_cart_info ) {
			return $pre_cart_info;
		}

		$from   = ! empty( $data['check_in_date'] ) ? absint( $data['check_in_date'] ) : false;
		$to     = ! empty( $data['check_out_date'] ) ? absint( $data['check_out_date'] ) : false;
		$result = array();

		if ( ! $from || ! $to ) {
			return;
		}

		$result[] = array(
			'key'     => __( 'Check In', 'jet-bookings-booking' ),
			'display' => date_i18n( get_option( 'date_format' ), $from ),
		);

		$result[] = array(
			'key'     => __( 'Check Out', 'jet-bookings-booking' ),
			'display' => date_i18n( get_option( 'date_format' ), $to ),
		);

		return apply_filters( 'jet-booking/wc-integration/cart-info', $result, $data, $form_data, $form_id );

	}

	/**
	 * Returns apartment CPT label
	 *
	 * @return [type] [description]
	 */
	public function get_apartment_label() {

		$cpt = Plugin::instance()->settings->get( 'apartment_post_type' );

		if ( ! $cpt ) {
			return null;
		}

		$cpt_object = get_post_type_object( $cpt );

		if ( ! $cpt_object ) {
			return null;
		}

		return $cpt_object->labels->singular_name;

	}

	/**
	 * Check if we need to create new Appointment product
	 *
	 * @return [type] [description]
	 */
	public function maybe_create_booking_product( $settings ) {

		$new_status = $settings->get( 'wc_integration' );

		if ( ! $new_status ) {
			return;
		}

		$product_id = $this->get_product_id_from_db() ? $this->get_product_id_from_db() : $settings->get( 'wc_product_id' );
		$product    = get_post( $product_id );

		if ( ! $product || $product->post_status !== 'publish' ) {
			$product_id = $this->create_booking_product();
		}

		$settings->update( 'wc_product_id', $product_id, false );

	}

	/**
	 * Try to get previousle created product ID in db.
	 *
	 * @return [type] [description]
	 */
	public function get_product_id_from_db() {

		global $wpdb;

		$table      = $wpdb->postmeta;
		$key        = $this->product_key;
		$product_id = $wpdb->get_var(
			"SELECT `post_id` FROM $table WHERE `meta_key` = '$key' ORDER BY post_id DESC;"
		);

		if ( ! $product_id ) {
			return false;
		}

		if ( 'product' !== get_post_type( $product_id ) ) {
			return false;
		}

		return absint( $product_id );
	}

	/**
	 * Returns product name
	 *
	 * @return [type] [description]
	 */
	public function get_product_name() {

		return apply_filters(
			'jet-abaf/wc-integration/product-name',
			__( 'Booking', 'jet-booking' )
		);

	}

	/**
	 * Create new booking product
	 *
	 * @return [type] [description]
	 */
	public function create_booking_product() {

		$product = new \WC_Product_Simple( 0 );

		$product->set_name( $this->get_product_name() );
		$product->set_status( 'publish' );
		$product->set_price( 1 );
		$product->set_regular_price( 1 );
		$product->set_slug( sanitize_title( $this->get_product_name() ) );

		$product->save();

		$product_id = $product->get_id();

		if ( $product_id ) {
			update_post_meta( $product_id, $this->product_key, true );
		}

		return $product_id;

	}

	/**
	 * Set WC integration status
	 */
	public function set_status() {

		$is_enbaled       = Plugin::instance()->settings->get( 'wc_integration' );
		$product_id       = Plugin::instance()->settings->get( 'wc_product_id' );
		$this->is_enbaled = filter_var( $is_enbaled, FILTER_VALIDATE_BOOLEAN );
		$this->product_id = $product_id;

	}

	/**
	 * Return WC integration status
	 *
	 * @return [type] [description]
	 */
	public function get_status() {
		return $this->is_enbaled;
	}

	/**
	 * Return WC integration product
	 *
	 * @return [type] [description]
	 */
	public function get_product_id() {
		return $this->product_id;
	}

}
