<div class="jet-abaf-bookings-add-new">
	<cx-vui-button
		button-style="accent"
		size="mini"
		@click="showAddDialog()"
	>
		<template slot="label"><?php _e( 'Add New', 'jet-booking' ); ?></template>
	</cx-vui-button>

	<cx-vui-popup
		v-model="addDialog"
		body-width="500px"
		ok-label="<?php _e( 'Add New', 'jet-booking' ) ?>"
		@on-cancel="addDialog = false"
		@on-ok="handleAdd"
	>
		<div class="cx-vui-subtitle" slot="title">
			<?php _e( 'Add New Booking:', 'jet-booking' ); ?>
		</div>

		<div
			class="jet-abaf-bookings-error"
			slot="content"
			v-if="overlappingBookings"
			v-html="overlappingBookings"
		></div>

		<div class="jet-abaf-details" slot="content">
			<br>

			<div class="jet-abaf-details__field jet-abaf-details__field-status">
				<div class="jet-abaf-details__label"><?php _e( 'Status:', 'jet-booking' ) ?></div>

				<div class="jet-abaf-details__content">
					<select v-model="newItem.status">
						<option v-for="( label, value ) in statuses" :value="value" :key="value">
							{{ label }}
						</option>
					</select>
				</div>
			</div>

			<div class="jet-abaf-details__field jet-abaf-details__field-apartment_id">
				<div class="jet-abaf-details__label"><?php _e( 'Booking Item:', 'jet-booking' ) ?></div>

				<div class="jet-abaf-details__content">
					<select  @change="onApartmentChange()" v-model="newItem.apartment_id">
						<option v-for="( label, value ) in bookingInstances" :value="value" :key="value">
							{{ label }}
						</option>
					</select>
				</div>
			</div>

			<div :class="[ 'jet-abaf-details__booking-dates',  { 'jet-abaf-disabled': isDisabled } ]" ref="jetABAFDatePicker">
				<div class="jet-abaf-details__check-in-date">
					<div class="jet-abaf-details__label"><?php _e( 'Check in:', 'jet-booking' ) ?></div>
					<div class="jet-abaf-details__content">
						<input type="text" v-model="newItem.check_in_date" />
					</div>
				</div>

				<div class="jet-abaf-details__check-out-date">
					<div class="jet-abaf-details__label"><?php _e( 'Check out:', 'jet-booking' ) ?></div>
					<div class="jet-abaf-details__content">
						<input type="text" v-model="newItem.check_out_date" />
					</div>
				</div>
			</div>

			<div class="jet-abaf-details__fields">
				<template v-for="field in fields">
					<div
						v-if="beVisible( field )"
						:key="field"
						:class="[ 'jet-abaf-details__field', 'jet-abaf-details__field-' + field ]"
					>
						<div class="jet-abaf-details__label">{{ field }}:</div>

						<div class="jet-abaf-details__content">
							<input type="text" v-model="newItem[ field ]" />
						</div>
					</div>
				</template>
			</div>

			<div v-if="orderPostType" class="jet-abaf-details__field">
				<div class="jet-abaf-details__label">
					<?php _e( 'Create Booking Order', 'jet-booking' ) ?>
				</div>

				<div class="jet-abaf-details__content">
					<cx-vui-switcher v-model="createOrder"></cx-vui-switcher>
				</div>
			</div>

			<div v-if="orderPostType && createOrder" class="jet-abaf-details__field">
				<div class="jet-abaf-details__label"><?php _e( 'Order Status:', 'jet-booking' ) ?></div>

				<div class="jet-abaf-details__content">
					<select  v-model="bookingOrderStatus">
						<option v-for="( label, value ) in orderPostTypeStatuses" :key="value" :value="value">
							{{ label }}
						</option>
					</select>
				</div>
			</div>

			<div v-if="wcIntegration" class="jet-abaf-details__field">
				<div class="jet-abaf-details__label">
					<?php _e( 'Create WC Order', 'jet-booking' ) ?>
				</div>

				<div class="jet-abaf-details__content">
					<cx-vui-switcher v-model="createOrder"></cx-vui-switcher>
				</div>
			</div>

			<div v-if="wcIntegration && createOrder" class="jet-abaf-details__fields">
				<div class="jet-abaf-details__field">
					<div class="jet-abaf-details__label">
						<?php _e( 'First Name:', 'jet-booking' ); ?>
					</div>

					<div class="jet-abaf-details__content">
						<input type="text" v-model.trim="wcOrderFirstName" />
					</div>
				</div>

				<div class="jet-abaf-details__field">
					<div class="jet-abaf-details__label">
						<?php _e( 'Last Name:', 'jet-booking' ); ?>
					</div>

					<div class="jet-abaf-details__content">
						<input type="text" v-model.trim="wcOrderLastName" />
					</div>
				</div>

				<div class="jet-abaf-details__field">
					<div class="jet-abaf-details__label">
						<?php _e( 'Email:', 'jet-booking' ); ?>
					</div>

					<div class="jet-abaf-details__content">
						<input type="email" v-model.trim="wcOrderEmail" />
					</div>
				</div>

				<div class="jet-abaf-details__field">
					<div class="jet-abaf-details__label">
						<?php _e( 'Phone:', 'jet-booking' ); ?>
					</div>

					<div class="jet-abaf-details__content">
						<input type="tel" v-model.trim="wcOrderPhone" />
					</div>
				</div>
			</div>
		</div>
	</cx-vui-popup>
</div>