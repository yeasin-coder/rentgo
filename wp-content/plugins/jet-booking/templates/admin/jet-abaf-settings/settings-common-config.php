<div>
	<cx-vui-switcher
		label="<?php _e( 'One day bookings', 'jet-booking' ); ?>"
		description="<?php _e( 'If this option is checked only single days bookings are allowed. If Weekly bookings are enabled this option will not work.', 'jet-booking' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		v-if="'per_nights' !== settings.booking_period"
		:value="settings.one_day_bookings"
		@input="updateSetting( $event, 'one_day_bookings' )"
	></cx-vui-switcher>

	<cx-vui-switcher
		label="<?php _e( 'Weekly bookings', 'jet-booking' ); ?>"
		description="<?php _e( 'If this option is checked, only full-week bookings are allowed.', 'jet-booking' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		:value="settings.weekly_bookings"
		@input="updateSetting( $event, 'weekly_bookings' )"
	></cx-vui-switcher>

	<cx-vui-input
		label="<?php _e( 'Week days offset', 'jet-booking' ); ?>"
		description="<?php _e( 'Allows you to change the first booked day of the week.', 'jet-booking' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		size="fullwidth"
		:value="settings.week_offset"
		v-if="settings.weekly_bookings"
		@on-input-change="updateSetting( $event.target.value, 'week_offset' )"
		type="number"
	></cx-vui-input>

	<cx-vui-input
		label="<?php _e( 'Starting day offset', 'jet-booking' ); ?>"
		description="<?php _e( 'This string defines offset for the earliest date which is allowed for the user.', 'jet-booking' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		size="fullwidth"
		:value="settings.start_day_offset"
		@on-input-change="updateSetting( $event.target.value, 'start_day_offset' )"
		type="number"
	></cx-vui-input>

	<cx-vui-input
		label="<?php _e( 'Min days', 'jet-booking' ); ?>"
		description="<?php _e( 'This number defines the minimum days of the selected range if this is 0, means do not limit minimum days.', 'jet-booking' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		size="fullwidth"
		:value="settings.min_days"
		@on-input-change="updateSetting( $event.target.value, 'min_days' )"
		type="number"
	></cx-vui-input>

	<cx-vui-input
		label="<?php _e( 'Max days', 'jet-booking' ); ?>"
		description="<?php _e( 'This number defines the maximum days of the selected range if this is 0, means do not limit maximum days', 'jet-booking' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		size="fullwidth"
		:value="settings.max_days"
		@on-input-change="updateSetting( $event.target.value, 'max_days' )"
		type="number"
	></cx-vui-input>
</div>