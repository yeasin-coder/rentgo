<div class="jet-abaf-bookings-wrap">
	<header class="jet-abaf-header">
		<h1 class="jet-abaf-title">
			<?php _e( 'Bookings', 'jet-booking' ); ?>
		</h1>

		<jet-abaf-add-new-booking
			v-if="isSet"
			:class="{ 'jet-abaf-loading': isLoading }"
		></jet-abaf-add-new-booking>
	</header>

	<div
		v-if="isSet"
		:class="{ 'jet-abaf-loading': isLoading }"
	>
		<jet-abaf-bookings-filter></jet-abaf-bookings-filter>
		<jet-abaf-bookings-list></jet-abaf-bookings-list>
	</div>

	<div v-else class="cx-vui-panel">
		<jet-abaf-go-to-setup></jet-abaf-go-to-setup>
	</div>
</div>