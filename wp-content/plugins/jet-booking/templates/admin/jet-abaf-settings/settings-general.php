<div>
	<cx-vui-select
		label="<?php _e( 'Booking orders post type', 'jet-booking' ); ?>"
		description="<?php _e( 'Select the post type, which will record and store the booking orders. It could be called `Orders`. Once a new order is placed, the record will appear in the corresponding database table within the chosen post type.', 'jet-booking' ); ?>"
		:options-list="postTypes"
		:wrapper-css="[ 'equalwidth' ]"
		:size="'fullwidth'"
		:value="generalSettings.related_post_type"
		@input="updateSetting( $event, 'related_post_type' )"
	></cx-vui-select>
	<cx-vui-select
		label="<?php _e( 'Booking instance post type', 'jet-booking' ); ?>"
		description="<?php _e( 'Select the post type containing the units to be booked (booking instances). Once selected, the related post IDs will be shown in the Bookings database table.', 'jet-booking' ); ?>"
		:options-list="postTypes"
		:wrapper-css="[ 'equalwidth' ]"
		:size="'fullwidth'"
		:value="generalSettings.apartment_post_type"
		@input="updateSetting( $event, 'apartment_post_type' )"
	></cx-vui-select>
	<?php if ( \JET_ABAF\Plugin::instance()->wc->has_woocommerce() ) : ?>
		<cx-vui-switcher
			label="<?php _e( 'WooCommerce integration', 'jet-booking' ); ?>"
			description="<?php _e( 'Enable to connect the booking system with WooCommerce checkout.', 'jet-booking' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:value="generalSettings.wc_integration"
			@input="updateSetting( $event, 'wc_integration' )"
		></cx-vui-switcher>
		<cx-vui-switcher
			v-if="settings.wc_integration"
			label="<?php _e( 'Two-way WC orders sync', 'jet-appointments-booking' ); ?>"
			description="<?php _e( 'If you enable this option, WC order status will be updated on booking status change (by default, if you update an booking status, related order will remain the same).', 'jet-booking' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:value="settings.wc_sync_orders"
			@input="updateSetting( $event, 'wc_sync_orders' )"
		></cx-vui-switcher>
	<?php else : ?>
		<cx-vui-component-wrapper
			label="<?php _e( 'WooCommerce integration', 'jet-booking' ); ?>"
			description="<?php _e( 'Enable to connect the booking system with WooCommerce checkout.', 'jet-booking' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
		>
			<span style="color:#C92C2C;">
				<?php _e( 'Please install and activate <b>WooCommerce</b> plugin.', 'jet-booking' ); ?>
			</span>
		</cx-vui-component-wrapper>
	<?php endif; ?>
	<cx-vui-select
		label="<?php _e( 'Filters storage type', 'jet-booking' ); ?>"
		description="<?php _e( 'Select the filter storage type for the searched date range.', 'jet-booking' ); ?>"
		:options-list="[
			{
				value: 'session',
				label: 'Session',
			},
			{
				value: 'cookies',
				label: 'Cookies',
			}
		]"
		:wrapper-css="[ 'equalwidth' ]"
		:size="'fullwidth'"
		:value="generalSettings.filters_store_type"
		@input="updateSetting( $event, 'filters_store_type' )"
	></cx-vui-select>
</div>
