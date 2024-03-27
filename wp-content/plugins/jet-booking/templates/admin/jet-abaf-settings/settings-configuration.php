<div>
	<cx-vui-select
		label="<?php _e( 'Booking period', 'jet-booking' ); ?>"
		description="<?php _e( 'Define how the booking period will be calculated – per night (without the last booked date) or per day (including the last booked date).</br><b>Note:</b> this option will affect price calculation.', 'jet-booking' ); ?>"
		:options-list="[
			{
				value: 'per_nights',
				label: 'Per Night (last booked date not included)',
			},
			{
				value: 'per_days',
				label: 'Per Day (last booked date is included)',
			}
		]"
		:wrapper-css="[ 'equalwidth' ]"
		:size="'fullwidth'"
		:value="configurationSettings.booking_period"
		@input="updateSetting( $event, 'booking_period' )"
	></cx-vui-select>

	<cx-vui-switcher
		label="<?php _e( 'Allow checkout only days', 'jet-booking' ); ?>"
		description="<?php _e( 'If this option is checked, the first day of the already booked period will be available for checkout only.', 'jet-booking' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		v-if="'per_nights' === configurationSettings.booking_period"
		:value="configurationSettings.allow_checkout_only"
		@input="updateSetting( $event, 'allow_checkout_only' )"
	></cx-vui-switcher>

	<jet-abaf-settings-common-config
		:settings="settings"
		@force-update="updateChildSetting( $event )"
	></jet-abaf-settings-common-config>
</div>
