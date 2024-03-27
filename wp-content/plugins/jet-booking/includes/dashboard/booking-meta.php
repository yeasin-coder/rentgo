<?php
namespace JET_ABAF\Dashboard;

use JET_ABAF\Plugin;

/**
 * Post meta manager class
 */

#[\AllowDynamicProperties]
class Booking_Meta {

	public $column    = null;

	public function __construct() {

		$this->apartment_post_type = Plugin::instance()->settings->get( 'apartment_post_type' );

		if ( $this->apartment_post_type ) {

			add_action(
				'add_meta_boxes_' . $this->apartment_post_type,
				array( $this, 'register_apartments_meta_box' )
			);

			$this->units_manager = new Units_Manager( $this->apartment_post_type );

		}

	}

	/**
	 * Register
	 * @return [type] [description]
	 */
	public function register_apartments_meta_box() {
		add_meta_box(
			'jet-abaf',
			esc_html__( 'Upcoming bookings', 'jet-booking' ),
			array( $this, 'render_apartments_meta_box' ),
			null,
			'normal',
			'high'
		);
	}

	/**
	 * Render apartments meta box.
	 *
	 * @since  2.0.0
	 * @since  2.5.5 Added additional `$post_id` handling.
	 * @access public.
	 *
	 * @param object $post WP_Post instance of CPT.
	 *
	 * @return mixed
	 */
	public function render_apartments_meta_box( $post ) {

		$post_id  = Plugin::instance()->db->get_initial_booking_item_id( $post->ID );
		$bookings = Plugin::instance()->db->get_future_bookings( $post_id );

		if ( empty( $bookings ) ) {
			echo '<p style="text-align: center;">There are no upcoming bookings</p>';
			return;
		}

		$columns = array_keys( $bookings[0] );

		?>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr><?php
					foreach ( $columns as $column ) {
						if ( 'apartment_id' !== $column ) {
							echo '<th>' . $column . '</th>';
						}
					}
				?></tr>
			</thead>
			<tbody><?php
				foreach ( $bookings as $row ) {
					?>
					<tr><?php
						foreach ( $row as $key => $column ) {
							if ( 'apartment_id' !== $key ) {

								if ( $this->column && $this->column === $key ) {
									$column = sprintf(
										'<a href="%1$s" target="_blank">#%2$s</a>',
										get_edit_post_link( $column ),
										get_the_title( $column )
									);
								}

								if ( in_array( $key, array( 'check_in_date', 'check_out_date' ) ) ) {
									$column = date_i18n( get_option( 'date_format' ), $column );
								}

								echo '<td>' . $column . '</td>';
							}
						}
					?></tr>
					<?php
				}
			?></tbody>
		</table>
		<?php

	}

}
