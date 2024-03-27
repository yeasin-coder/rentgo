<?php

namespace JET_ABAF;

/**
 * Upgrader class
 */
class Upgrade {

	/**
	 * Settings.
	 *
	 * @var array
	 */
	public $settings = [];

	public function __construct() {

		$this->to_2_0();

		$this->settings = Plugin::instance()->settings->get_all();

		$db_updater = jet_engine()->framework->get_included_module_data( 'cherry-x-db-updater.php' );

		new \CX_DB_Updater(
			[
				'path'      => $db_updater['path'],
				'url'       => $db_updater['url'],
				'slug'      => 'jet-booking',
				'version'   => '2.8.0',
				'callbacks' => [
					'2.5.0' => [
						[ $this, 'update_db_2_5_0' ],
					],
					'2.6.0' => [
						[ $this, 'update_db_2_6_0' ],
					],
					'2.8.0' => [
						[ $this, 'update_db_2_8_0' ],
					],
				],
				'labels'    => [
					'start_update' => __( 'Start Update', 'jet-booking' ),
					'data_update'  => __( 'Data Update', 'jet-booking' ),
					'messages'     => [
						'error'   => __( 'Module DB Updater init error in %s - version and slug is required arguments', 'jet-booking' ),
						'update'  => __( 'We need to update your database to the latest version.', 'jet-booking' ),
						'updated' => __( 'DB Update complete, thank you for updating to the latest version!', 'jet-booking' ),
					],
				],
			]
		);

	}

	/**
	 * Update db updater to 2.5.0.
	 */
	public function update_db_2_5_0() {

		$schedule_settings = [
			'days_off',
			'disable_weekday_1',
			'disable_weekday_2',
			'disable_weekday_3',
			'disable_weekday_4',
			'disable_weekday_5',
			'disable_weekend_1',
			'disable_weekend_2',
		];

		if ( $this->settings ) {
			foreach ( $schedule_settings as $setting ) {
				if ( ! isset( $this->settings[ $setting ] ) ) {
					$default_setting = Plugin::instance()->settings->get( $setting );

					Plugin::instance()->settings->update( $setting, $default_setting );
				}
			}
		}

	}

	/**
	 * Update db updater to 2.6.0.
	 */
	public function update_db_2_6_0() {

		$new_settings = [
			'start_day_offset',
			'min_days',
			'max_days',
		];

		if ( $this->settings ) {
			foreach ( $new_settings as $setting ) {
				if ( ! isset( $this->settings[ $setting ] ) ) {
					$default_setting = Plugin::instance()->settings->get( $setting );

					Plugin::instance()->settings->update( $setting, $default_setting );
				}
			}
		}

	}

	/**
	 * Update db updater to 2.8.0.
	 */
	public function update_db_2_8_0() {

		$schedule_settings = [
			'check_in_weekday_1',
			'check_out_weekday_1',
			'check_in_weekday_2',
			'check_out_weekday_2',
			'check_in_weekday_3',
			'check_out_weekday_3',
			'check_in_weekday_4',
			'check_out_weekday_4',
			'check_in_weekday_5',
			'check_out_weekday_5',
			'check_in_weekend_1',
			'check_out_weekend_1',
			'check_in_weekend_2',
			'check_out_weekend_2',
		];

		$table = jet_abaf()->db::bookings_table();

		if ( jet_abaf()->db->column_exists( 'order_id' ) ) {
			jet_abaf()->db::wpdb()->query( "ALTER TABLE $table MODIFY COLUMN order_id bigint(20);" );
		} else {
			jet_abaf()->db::wpdb()->query( "ALTER TABLE $table ADD order_id bigint(20);" );
		}

		$orders_column = jet_abaf()->settings->get( 'related_post_type_column' );

		if ( $orders_column && 'order_id' !== $orders_column && jet_abaf()->db->column_exists( $orders_column ) ) {
			jet_abaf()->db::wpdb()->query( "UPDATE $table SET order_id = $orders_column WHERE order_id IS NULL OR TRIM( order_id ) = '';" );
			jet_abaf()->db->delete_table_columns( [ $orders_column ] );

			$additional_columns = jet_abaf()->settings->get( 'additional_columns' );

			foreach ( $additional_columns as $key => $column ) {
				if ( ! empty( $column['column'] ) && $column['column'] === $orders_column ) {
					unset( $additional_columns[ $key ] );
				}
			}

			jet_abaf()->settings->update( 'additional_columns', $additional_columns, false );
		}

		if ( $this->settings ) {
			foreach ( $schedule_settings as $setting ) {
				if ( ! isset( $this->settings[ $setting ] ) ) {
					$default_setting = Plugin::instance()->settings->get( $setting );

					Plugin::instance()->settings->update( $setting, $default_setting );
				}
			}
		}

	}

	/**
	 * Check DB requirements for 2.0 version and show upgrade notice
	 *
	 * @return [type] [description]
	 */
	public function to_2_0() {
		add_action( 'admin_init', function () {

			if ( ! Plugin::instance()->db->is_bookings_table_exists() ) {
				return;
			}

			if ( ! Plugin::instance()->db->column_exists( 'status' ) ) {
				Plugin::instance()->db->insert_table_columns( array( 'status' ) );
			}

			if ( Plugin::instance()->dashboard->is_dashboard_page() ) {
				if ( ! Plugin::instance()->db->column_exists( 'import_id' ) ) {
					Plugin::instance()->db->insert_table_columns( array( 'import_id' ) );
				}
			}

		} );
	}

}
