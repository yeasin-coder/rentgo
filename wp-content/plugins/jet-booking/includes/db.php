<?php

namespace JET_ABAF;

/**
 * Database manager class
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define DB class
 */
class DB {

	/**
	 * Check if booking DB table already exists
	 *
	 * @var bool
	 */
	private $bookings_table_exists = null;

	/**
	 * Check if units DB table already exists
	 *
	 * @var bool
	 */
	private $units_table_exists = null;

	/**
	 * Stores latest queried result to use it
	 *
	 * @var null
	 */
	public $latest_result = null;

	/**
	 * Stores latest inserted booking item
	 *
	 * @var array
	 */
	public $inserted_booking = false;

	/**
	 *
	 */
	public $queried_booking = false;

	/**
	 * Constructor for the class
	 */
	public function __construct() {

		if ( ! empty( $_GET['jet_abaf_install_table'] ) ) {
			add_action( 'init', array( $this, 'install_table' ) );
		}

	}

	/**
	 * Check if booking table alredy exists
	 *
	 * @return boolean [description]
	 */
	public function is_bookings_table_exists() {

		if ( null !== $this->bookings_table_exists ) {
			return $this->bookings_table_exists;
		}

		$table = self::bookings_table();

		if ( $table === self::wpdb()->get_var( "SHOW TABLES LIKE '$table'" ) ) {
			$this->bookings_table_exists = true;
		} else {
			$this->bookings_table_exists = false;
		}

		return $this->bookings_table_exists;
	}

	/**
	 * Check if booking table alredy exists
	 *
	 * @return boolean [description]
	 */
	public function is_units_table_exists() {

		if ( null !== $this->units_table_exists ) {
			return $this->units_table_exists;
		}

		$table = self::units_table();

		if ( $table === self::wpdb()->get_var( "SHOW TABLES LIKE '$table'" ) ) {
			$this->units_table_exists = true;
		} else {
			$this->units_table_exists = false;
		}

		return $this->units_table_exists;

	}

	/**
	 * Check if all required DB tables are exists
	 *
	 * @return [type] [description]
	 */
	public function tables_exists() {
		return $this->is_bookings_table_exists() && $this->is_units_table_exists();
	}

	/**
	 * Try to recreate DB table by request
	 *
	 * @return void
	 */
	public function install_table() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$this->create_bookings_table();
		$this->create_units_table();

	}

	/**
	 * Returns WPDB instance
	 *
	 * @return [type] [description]
	 */
	public static function wpdb() {
		global $wpdb;
		return $wpdb;
	}

	/**
	 * Returns table name
	 *
	 * @return [type] [description]
	 */
	public static function bookings_table() {
		return self::wpdb()->prefix . 'jet_apartment_bookings';
	}

	/**
	 * Returns table name
	 *
	 * @return [type] [description]
	 */
	public static function units_table() {
		return self::wpdb()->prefix . 'jet_apartment_units';
	}

	/**
	 * Insert booking.
	 *
	 * @since  2.1.0
	 * @since  2.5.5 Added additional `apartment_id` handling.
	 * @access public.
	 *
	 * @param array $booking List of parameters.
	 *
	 * @return mixed
	 */
	public function insert_booking( $booking = array() ) {

		$default_fields = array(
			'apartment_id',
			'apartment_unit',
			'check_in_date',
			'check_out_date',
		);

		$fields   = array_merge( $default_fields, $this->get_additional_db_columns() );
		$format   = array_fill( 0, count( $fields ), '%s' );
		$defaults = array_fill( 0, count( $fields ), '' );
		$defaults = array_combine( $fields, $defaults );
		$booking  = wp_parse_args( $booking, $defaults );

		$booking['check_in_date'] = $booking['check_in_date'] + 1;
		$booking['apartment_id']  = $this->get_initial_booking_item_id( $booking['apartment_id'] );

		if ( empty( $booking['apartment_unit'] ) ) {
			$booking['apartment_unit'] = $this->get_available_unit( $booking );
		}

		if ( ! $this->is_booking_dates_available( $booking ) ) {
			return false;
		}

		$inserted = self::wpdb()->insert( self::bookings_table(), $booking, $format );

		if ( $inserted ) {
			$this->inserted_booking = $booking;
			return self::wpdb()->insert_id;
		} else {
			return false;
		}

	}

	/**
	 * Get initial apartment id.
	 *
	 * Returns initial booking apartment ID.
	 *
	 * @since  2.5.5
	 * @access public
	 *
	 * @param int|string $id Apartment post type ID.
	 *
	 * @return mixed|void
	 */
	public function get_initial_booking_item_id( $id ) {
		return apply_filters( 'jet-abaf/db/initial-apartment-id', $id );
	}

	/**
	 * Is booking dates available.
	 *
	 * Check if current booking dates is available.
	 *
	 * @since  2.7.1 Refactored.
	 * @access public
	 *
	 * @param array         $booking    Booking data.
	 * @param number|string $booking_id Booking ID.
	 *
	 * @return boolean
	 */
	public function is_booking_dates_available( $booking = [], $booking_id = 0 ) {

		$booked = $this->get_booked_items( $booking );

		if ( empty( $booked ) ) {
			return true;
		}

		foreach ( $booked as $index => $booking ) {
			if ( absint( $booking['booking_id'] ) === absint( $booking_id ) ) {
				unset( $booked[ $index ] );
			}
		}

		if ( empty( $booked ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Get available unit.
	 *
	 * Returns available unit for passed dates.
	 *
	 * @since  1.0.0
	 * @since  2.5.2 Move some logic to `get_booked_units()`.
	 * @access public
	 *
	 * @param array $booking Bookings parameters.
	 *
	 * @return mixed|null
	 */
	public function get_available_unit( $booking ) {

		$all_units = $this->get_apartment_units( $booking['apartment_id'] );

		if ( empty( $all_units ) ) {
			return null;
		}

		$booked_units = $this->get_booked_units( $booking );

		if ( empty( $booked_units ) ) {
			return $all_units[0]['unit_id'];
		}

		$skip_statuses   = Plugin::instance()->statuses->invalid_statuses();
		$skip_statuses[] = Plugin::instance()->statuses->temporary_status();

		foreach ( $all_units as $unit ) {
			$found = false;

			foreach ( $booked_units as $booked_unit ) {
				if ( ! isset( $booked_unit['status'] ) || ! in_array( $booked_unit['status'], $skip_statuses ) ) {
					if ( absint( $unit['unit_id'] ) === absint( $booked_unit['apartment_unit'] ) ) {
						$found = true;
					}
				}
			}

			if ( ! $found ) {
				return $unit['unit_id'];
			}
		}

		return null;

	}

	/**
	 * Get available units.
	 *
	 * Returns the list of available units for passed/selected dates.
	 *
	 * @since  2.5.2
	 * @access public
	 *
	 * @param $booking
	 *
	 * @return array|object|\stdClass[]|null
	 */
	public function get_available_units( $booking ) {

		$all_units = $this->get_apartment_units( $booking['apartment_id'] );

		if ( empty( $all_units ) ) {
			return null;
		}

		$booked_units = $this->get_booked_units( $booking );

		if ( empty( $booked_units ) ) {
			return $all_units;
		}

		$skip_statuses   = Plugin::instance()->statuses->invalid_statuses();
		$skip_statuses[] = Plugin::instance()->statuses->temporary_status();

		foreach ( $all_units as $key => $unit ) {
			foreach ( $booked_units as $booked_unit ) {
				if ( ! isset( $booked_unit['status'] ) || ! in_array( $booked_unit['status'], $skip_statuses ) ) {
					if ( absint( $unit['unit_id'] ) === absint( $booked_unit['apartment_unit'] ) ) {
						unset( $all_units[ $key ] );
					}
				}
			}
		}

		return $all_units;

	}

	/**
	 * Get booked units.
	 *
	 * Return list of apartment booked units for passed dates.
	 *
	 * @since  2.5.2
	 * @access public
	 *
	 * @param array $booking Bookings parameters.
	 *
	 * @return array
	 */
	public function get_booked_units( $booking ) {

		$bookings_table = self::bookings_table();
		$apartment_id   = $booking['apartment_id'];
		$from           = $booking['check_in_date'];
		$to             = $booking['check_out_date'];

		return self::wpdb()->get_results( "
			SELECT *
			FROM `{$bookings_table}`
			WHERE `apartment_id` = $apartment_id
			AND (
				( `check_in_date` >= $from AND `check_in_date` <= $to )
				OR ( `check_out_date` >= $from AND `check_out_date` <= $to )
				OR ( `check_in_date` < $from AND `check_out_date` >= $to )
			)
		", ARRAY_A );

	}

	/**
	 * Update booking.
	 *
	 * Update booking information in database.
	 *
	 * @since  2.7.0 Added `'jet-booking/db/booking-updated'` hook.
	 * @access public
	 *
	 * @param string|int $booking_id Booking ID.
	 * @param array      $data       Booking item data.
	 *
	 * @return void
	 */
	public function update_booking( $booking_id = 0, $data = [] ) {

		if ( ! empty( $data['check_in_date'] ) ) {
			$data['check_in_date']++;
		}

		self::wpdb()->update( self::bookings_table(), $data, [ 'booking_id' => $booking_id ] );

		do_action( 'jet-booking/db/booking-updated', $booking_id );

	}

	/**
	 * Delete booking by passed parameters
	 *
	 * @param  [type] $where [description]
	 *
	 * @return [type]        [description]
	 */
	public function delete_booking( $where = array() ) {
		self::wpdb()->delete( self::bookings_table(), $where );
	}

	/**
	 * Delete unit by passed parameters
	 *
	 * @param  [type] $where [description]
	 *
	 * @return [type]        [description]
	 */
	public function delete_unit( $where = array() ) {
		self::wpdb()->delete( self::units_table(), $where );
	}

	/**
	 * Update unit
	 *
	 * @return [type] [description]
	 */
	public function update_unit( $unit_id, $data ) {
		self::wpdb()->update(
			self::units_table(),
			$data,
			array( 'unit_id' => $unit_id )
		);
	}

	/**
	 * Get future bookings for apartment ID (or all future bookings if apartment ID is not passed)
	 *
	 * @param  [type] $apartment_id [description]
	 *
	 * @return [type]               [description]
	 */
	public function get_future_bookings( $apartment_id = null ) {

		$table = self::bookings_table();
		$now   = strtotime( 'now 00:00' );
		$query = "SELECT * FROM $table WHERE `check_out_date` > $now";

		if ( $apartment_id ) {
			$apartment_id = absint( $apartment_id );
			$query        .= " AND `apartment_id` = $apartment_id";
		}

		$query .= ";";

		return self::wpdb()->get_results( $query, ARRAY_A );

	}

	/**
	 * Returns all available units for apartment
	 *
	 * @return [type] [description]
	 */
	public function get_apartment_units( $apartment_id ) {
		return $this->query(
			array(
				'apartment_id' => $apartment_id,
			),
			self::units_table()
		);
	}

	/**
	 * Returns all available units for apartment
	 *
	 * @return [type] [description]
	 */
	public function get_apartment_unit( $apartment_id, $unit_id ) {
		return $this->query(
			array(
				'apartment_id' => $apartment_id,
				'unit_id'      => $unit_id,
			),
			self::units_table()
		);
	}

	/**
	 * Returns appointment detail by order id
	 *
	 * @return [type] [description]
	 */
	public function get_booking_by( $field = 'booking_id', $value = null ) {

		$booking = $this->query(
			array( $field => $value ),
			self::bookings_table()
		);

		if ( empty( $booking ) ) {
			return false;
		}

		$booking = $booking[0];

		return $booking;

	}

	/**
	 * Get booked apartments.
	 *
	 * Get already booked apartments for passed dates.
	 *
	 * @since  1.0.0
	 * @since  2.5.2 Added compatibility with checkout only option.
	 * @access public
	 *
	 * @param string $from Booking start date.
	 * @param string $to   Booking end date.
	 *
	 * @return array
	 */
	public function get_booked_apartments( $from, $to ) {

		$table       = self::bookings_table();
		$units_table = self::units_table();

		// Increase $from to 1 to avoid overlapping check-in and check-out dates.
		$from++;

		$skip_statuses   = Plugin::instance()->statuses->invalid_statuses();
		$skip_statuses[] = Plugin::instance()->statuses->temporary_status();

		$skip_statuses = implode( ', ', array_map( function ( $item ) {
			return '"' . trim( $item ) . '"';
		}, $skip_statuses ) );

		$booked = self::wpdb()->get_results( "
			SELECT apartment_id AS `apartment_id`, count( * ) AS `units`, check_in_date AS `check_in_date`
			FROM $table
			WHERE ( `check_in_date` BETWEEN $from AND $to
			OR `check_out_date` BETWEEN $from AND $to
			OR ( `check_in_date` <= $from AND `check_out_date` >= $to ) )
			AND `status` NOT IN ( $skip_statuses )
			GROUP BY apartment_id;
		", ARRAY_A );

		if ( empty( $booked ) ) {
			return array();
		}

		$available = self::wpdb()->get_results( "
			SELECT apartment_id AS `apartment_id`, count( * ) AS `units`
			FROM $units_table
			GROUP BY apartment_id;
		", ARRAY_A );

		if ( ! empty( $available ) ) {
			$tmp = array();
			foreach ( $available as $row ) {
				$tmp[ $row['apartment_id'] ] = $row['units'];
			}
			$available = $tmp;
		} else {
			$available = array();
		}

		$result = array();

		foreach ( $booked as $apartment ) {
			$ap_id = $apartment['apartment_id'];

			if ( Plugin::instance()->settings->checkout_only_allowed() ) {
				if ( date( 'Y-m-d', $to ) === date( 'Y-m-d', $apartment['check_in_date'] ) ) {
					$available[ $ap_id ] = ! empty( $available[ $ap_id ] ) ? $available[ $ap_id ] : 1;
					$apartment['units']  = 0;
				}
			}

			if ( empty( $available[ $ap_id ] ) ) {
				$result[] = $ap_id;
			} else {
				$booked          = absint( $apartment['units'] );
				$available_units = absint( $available[ $ap_id ] );

				if ( $booked >= $available_units ) {
					$result[] = $ap_id;
				}
			}
		}

		return $result;

	}

	/**
	 * Booking availability.
	 *
	 * Check if is booking instance available for new bookings.
	 *
	 * @since  2.5.0
	 * @since  2.7.1 Refactored.
	 * @access public
	 *
	 * @param array         $booking    Booking data.
	 * @param number|string $booking_id Booking ID.
	 *
	 * @return bool
	 */
	public function booking_availability( $booking = [], $booking_id = 0 ) {

		$booked = $this->get_booked_items( $booking );

		if ( empty( $booked ) ) {
			return true;
		}

		$this->latest_result = $booked;

		$units_table    = self::units_table();
		$apartment_id   = $booking['apartment_id'];
		$apartment_unit = $booking['apartment_unit'] ?? '';
		$count          = 0;
		$booked_units   = [];

		foreach ( $booked as $item ) {
			if ( absint( $item['booking_id'] ) === absint( $booking_id ) || in_array( absint( $item['apartment_unit'] ), $booked_units ) ) {
				continue;
			}

			if ( absint( $item['apartment_unit'] ) === absint( $apartment_unit ) ) {
				return false;
			}

			$booked_units[] = absint( $item['apartment_unit'] );

			$count++;
		}

		$available = self::wpdb()->get_results( "
			SELECT apartment_id AS `apartment_id`, count( * ) AS `units`
			FROM $units_table
			WHERE `apartment_id` = $apartment_id
			GROUP BY apartment_id;
		", ARRAY_A );

		if ( empty( $available ) && 0 < $count ) {
			return false;
		}

		if ( empty( $available ) && 0 === $count ) {
			return true;
		}

		if ( $count >= absint( $available[0]['units'] ) ) {
			return false;
		}

		return true;

	}

	/**
	 * Get booked items.
	 *
	 * Returns list of booked items.
	 *
	 * @since  2.7.1
	 * @access public
	 *
	 * @param array $booking Bookings data.
	 *
	 * @return array|object|\stdClass[]|null
	 */
	public function get_booked_items( $booking ) {

		$bookings_table = self::bookings_table();
		$apartment_id   = $booking['apartment_id'];
		$apartment_unit = $booking['apartment_unit'] ?? '';
		$from           = $booking['check_in_date'];
		$to             = $booking['check_out_date'];

		// Increase $from to 1 to avoid overlapping check-in and check-out dates.
		$from++;

		$query = "
			SELECT *
			FROM $bookings_table
			WHERE (
				`check_in_date` BETWEEN $from AND $to
				OR `check_out_date` BETWEEN $from AND $to
				OR ( `check_in_date` <= $from AND `check_out_date` >= $to )
			) AND `apartment_id` = $apartment_id
		";

		if ( ! empty( $apartment_unit ) ) {
			$query .= " AND `apartment_unit` = $apartment_unit";
		}

		$query .= ";";

		$booked          = self::wpdb()->get_results( $query, ARRAY_A );
		$skip_statuses   = Plugin::instance()->statuses->invalid_statuses();
		$skip_statuses[] = Plugin::instance()->statuses->temporary_status();

		foreach ( $booked as $index => $booking ) {
			if ( ! empty( $booking['status'] ) && in_array( $booking['status'], $skip_statuses ) ) {
				unset( $booked[ $index ] );
			}
		}

		return $booked;

	}

	/**
	 * Returns additional DB fields.
	 *
	 * @return array
	 */
	public function get_additional_db_columns() {
		return apply_filters( 'jet-abaf/db/additional-db-columns', [] );
	}

	/**
	 * Schema.
	 *
	 * Returns booking table columns schema.
	 *
	 * @since  2.8.0
	 * @access public
	 *
	 * @return string[]
	 */
	public function schema() {
		return [
			'booking_id'     => "bigint(20) NOT NULL AUTO_INCREMENT",
			'status'         => "text",
			'apartment_id'   => "bigint(20)",
			'apartment_unit' => "bigint(20)",
			'order_id'       => "bigint(20)",
			'check_in_date'  => "bigint(20)",
			'check_out_date' => "bigint(20)",
			'import_id'      => "text",
		];
	}

	/**
	 * Create booking table.
	 *
	 * Create database table for tracked information.
	 *
	 * @since  2.8.0 Refactored
	 * @access public
	 *
	 * @return void
	 */
	public function create_bookings_table( $delete_if_exists = false ) {

		if ( ! function_exists( 'dbDelta' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		}

		$table = self::bookings_table();

		if ( $delete_if_exists && $this->is_bookings_table_exists() ) {
			self::wpdb()->query( "DROP TABLE $table;" );
		}

		$default_columns = $this->schema();
		$columns_schema  = '';

		foreach ( $default_columns as $column => $desc ) {
			$columns_schema .= $column . ' ' . $desc . ',';
		}

		$additional_columns = array_unique( $this->get_additional_db_columns() );

		if ( is_array( $additional_columns ) && ! empty( $additional_columns ) ) {
			foreach ( $additional_columns as $column ) {
				$columns_schema .= $column . ' text,';
			}
		}

		$charset_collate = self::wpdb()->get_charset_collate();
		$sql             = "CREATE TABLE $table ( $columns_schema PRIMARY KEY ( booking_id ) ) $charset_collate;";

		dbDelta( $sql );

	}

	/**
	 * Create DB table for apartment units
	 *
	 * @return [type] [description]
	 */
	public function create_units_table( $delete_if_exists = false ) {

		if ( ! function_exists( 'dbDelta' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		}

		$table = self::units_table();

		if ( $delete_if_exists && $this->is_units_table_exists() ) {
			self::wpdb()->query( "DROP TABLE $table;" );
		}

		$charset_collate = self::wpdb()->get_charset_collate();

		$sql = "CREATE TABLE $table (
			unit_id bigint(20) NOT NULL AUTO_INCREMENT,
			apartment_id bigint(20),
			unit_title text,
			notes text,
			PRIMARY KEY (unit_id)
		) $charset_collate;";

		dbDelta( $sql );

	}

	/**
	 * Insert table columns.
	 *
	 * Insert new columns into existing bookings table
	 *
	 * @access public
	 *
	 * @param array $columns List of columns to insert.
	 *
	 * @return void
	 */
	public function insert_table_columns( $columns = [] ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$table          = self::bookings_table();
		$columns_schema = '';

		foreach ( $columns as $column ) {
			$columns_schema .= 'ADD ' . $column . ' text, ';
		}

		$columns_schema = rtrim( $columns_schema, ', ' );

		$sql = "ALTER TABLE $table $columns_schema;";

		self::wpdb()->query( $sql );

	}

	/**
	 * Delete table columns.
	 *
	 * Delete columns into existing bookings table
	 *
	 * @access public
	 *
	 * @param array $columns List of columns to delete.
	 *
	 * @return void
	 */
	public function delete_table_columns( $columns ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$table          = self::bookings_table();
		$columns_schema = '';

		foreach ( $columns as $column ) {
			$columns_schema .= 'DROP COLUMN ' . $column . ', ';
		}

		$columns_schema = rtrim( $columns_schema, ', ' );

		$sql = "ALTER TABLE $table $columns_schema;";

		self::wpdb()->query( $sql );

	}

	/**
	 * Get default fields.
	 *
	 * Returns default database fields list.
	 *
	 * @since  2.8.0 Added `order_id`, `import_id` fields.
	 * @access public
	 *
	 * @return string[]
	 */
	public function get_default_fields() {
		return [
			'booking_id',
			'status',
			'apartment_id',
			'apartment_unit',
			'order_id',
			'check_in_date',
			'check_out_date',
			'import_id',
		];
	}

	/**
	 * Update database with new columns
	 *
	 * @param  [type] $new_columns [description]
	 *
	 * @return [type]              [description]
	 */
	public function update_columns_diff( $new_columns = array() ) {

		$table           = self::bookings_table();
		$default_columns = $this->get_default_fields();

		$columns          = self::wpdb()->get_results( "SHOW COLUMNS FROM $table", ARRAY_A );
		$existing_columns = array();

		if ( empty( $columns ) ) {
			return false;
		}

		foreach ( $columns as $column ) {
			if ( ! in_array( $column['Field'], $default_columns ) ) {
				$existing_columns[] = $column['Field'];
			}
		}

		if ( empty( $new_columns ) && empty( $existing_columns ) ) {
			return;
		}

		$to_delete = array_diff( $existing_columns, $new_columns );
		$to_add    = array_diff( $new_columns, $existing_columns );

		if ( ! empty( $to_delete ) ) {
			$this->delete_table_columns( $to_delete );
		}

		if ( ! empty( $to_add ) ) {
			$this->insert_table_columns( $to_add );
		}

	}

	/**
	 * Add nested query arguments
	 *
	 * @param  [type]  $key    [description]
	 * @param  [type]  $value  [description]
	 * @param boolean $format [description]
	 *
	 * @return [type]          [description]
	 */
	public function get_sub_query( $key, $value, $format = false ) {

		$query = '';
		$glue  = '';

		if ( ! $format ) {

			if ( false !== strpos( $key, '!' ) ) {
				$format = '`%1$s` != \'%2$s\'';
				$key    = ltrim( $key, '!' );
			} else {
				$format = '`%1$s` = \'%2$s\'';
			}

		}

		foreach ( $value as $child ) {
			$query .= $glue;
			$query .= sprintf( $format, esc_sql( $key ), esc_sql( $child ) );
			$glue  = ' OR ';
		}

		return $query;

	}

	/**
	 * Add where args.
	 *
	 * Add where arguments to query.
	 *
	 * @since  2.8.0 Added new arguments handling for `>=` & `<=`.
	 * @access public
	 *
	 * @param array  $args Query arguments.
	 * @param string $rel  Query relation.
	 *
	 * @return string
	 */
	public function add_where_args( $args = [], $rel = 'AND' ) {

		$query = '';

		if ( ! empty( $args ) ) {
			$query .= ' WHERE ';
			$glue  = '';

			foreach ( $args as $key => $value ) {
				$format = '`%1$s` = \'%2$s\'';
				$query  .= $glue;

				if ( false !== strpos( $key, '!' ) ) {
					$key    = ltrim( $key, '!' );
					$format = '`%1$s` != \'%2$s\'';
				} elseif ( false !== strpos( $key, '>=' ) ) {
					$key    = rtrim( $key, '>=' );
					$format = '`%1$s` >= %2$d';
				} elseif ( false !== strpos( $key, '>' ) ) {
					$key    = rtrim( $key, '>' );
					$format = '`%1$s` > %2$d';
				} elseif ( false !== strpos( $key, '<=' ) ) {
					$key    = rtrim( $key, '<=' );
					$format = '`%1$s` <= %2$d';
				} elseif ( false !== strpos( $key, '<' ) ) {
					$key    = rtrim( $key, '<' );
					$format = '`%1$s` < %2$d';
				}

				if ( is_array( $value ) ) {
					$query .= sprintf( '( %s )', $this->get_sub_query( $key, $value, $format ) );
				} else {
					$query .= sprintf( $format, esc_sql( $key ), esc_sql( $value ) );
				}

				$glue = ' ' . $rel . ' ';
			}
		}

		return $query;

	}

	/**
	 * Check if booking DB column is exists
	 *
	 * @return [type] [description]
	 */
	public function column_exists( $column ) {

		$table = self::bookings_table();
		return self::wpdb()->query( "SHOW COLUMNS FROM `$table` LIKE '$column'" );

	}

	/**
	 * Add order arguments to query
	 *
	 * @param array $args [description]
	 */
	public function add_order_args( $order = array() ) {

		$query = '';

		if ( ! empty( $order['orderby'] ) ) {

			$orderby = $order['orderby'];
			$order   = ! empty( $order['order'] ) ? $order['order'] : 'desc';
			$order   = strtoupper( $order );
			$query   .= " ORDER BY $orderby $order";

		}

		return $query;

	}

	/**
	 * Return count of queried items
	 *
	 * @return [type] [description]
	 */
	public function count( $args = array(), $rel = 'AND' ) {

		$table = self::bookings_table();

		$query = "SELECT count(*) FROM $table";

		if ( ! $rel ) {
			$rel = 'AND';
		}

		if ( isset( $args['after'] ) ) {
			$after = $args['after'];
			unset( $args['after'] );
			$args['ID>'] = $after;
		}

		if ( isset( $args['before'] ) ) {
			$before = $args['before'];
			unset( $args['before'] );
			$args['ID<'] = $before;
		}

		$query .= $this->add_where_args( $args, $rel );

		return self::wpdb()->get_var( $query );

	}

	/**
	 * Check if booking already exists
	 *
	 * @param string $by_field [description]
	 * @param  [type] $value    [description]
	 *
	 * @return [type]           [description]
	 */
	public function booking_exists( $by_field = 'ID', $value = null ) {
		$count = $this->count( array( $by_field => $value ) );

		return ! empty( $count );
	}

	/**
	 * Query data from db table
	 *
	 * @return [type] [description]
	 */
	public function query( $args = array(), $table = null, $limit = 0, $offset = 0, $order = array(), $rel = 'AND' ) {

		if ( ! $table ) {
			$table = self::bookings_table();
		}

		$query = "SELECT * FROM $table";

		if ( ! $rel ) {
			$rel = 'AND';
		}

		if ( isset( $args['after'] ) ) {
			$after = $args['after'];
			unset( $args['after'] );
			$args['ID>'] = $after;
		}

		if ( isset( $args['before'] ) ) {
			$before = $args['before'];
			unset( $args['before'] );
			$args['ID<'] = $before;
		}

		$query .= $this->add_where_args( $args, $rel );
		$query .= $this->add_order_args( $order );

		if ( intval( $limit ) > 0 ) {
			$limit  = absint( $limit );
			$offset = absint( $offset );
			$query  .= " LIMIT $offset, $limit";
		}

		$raw = self::wpdb()->get_results( $query, ARRAY_A );

		return $raw;

	}

}
