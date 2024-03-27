<div class="jet-abaf-bookings-list">
	<cx-vui-list-table
		:is-empty="! itemsList.length"
		empty-message="<?php esc_html_e( 'No bookings found', 'jet-booking' ); ?>"
	>
		<cx-vui-list-table-heading
			:slots="[ 'booking_id', 'apartment_id', 'apartment_unit', 'check_in_date', 'check_out_date', 'order_id', 'status' ]"
			slot="heading"
		>
			<span
				slot="booking_id"
				class="list-table-heading__cell-content list-table-heading__cell-clickable"
				:class="classColumn( 'booking_id' )"
				@click="sortColumn( 'booking_id' )"
			>
				<?php _e( 'ID', 'jet-booking' ); ?>
			</span>

			<span slot="apartment_id"><?php _e( 'Booked Instance', 'jet-booking' ); ?></span>
			<span slot="apartment_unit"><?php _e( 'Booked Unit ID', 'jet-booking' ); ?></span>

			<span
				slot="check_in_date"
				class="list-table-heading__cell-content list-table-heading__cell-clickable"
				:class="classColumn( 'check_in_date' )"
				@click="sortColumn( 'check_in_date' )"
			>
				<?php _e( 'Check In', 'jet-booking' ); ?>
			</span>

			<span
				slot="check_out_date"
				class="list-table-heading__cell-content list-table-heading__cell-clickable"
				:class="classColumn( 'check_out_date' )"
				@click="sortColumn( 'check_out_date' )"
			>
				<?php _e( 'Check Out', 'jet-booking' ); ?>
			</span>

			<span
				slot="order_id"
				class="list-table-heading__cell-content list-table-heading__cell-clickable"
				:class="classColumn( 'order_id' )"
				@click="sortColumn( 'order_id' )"
			>
				<?php _e( 'Related Order', 'jet-booking' ); ?>
			</span>

			<span
				slot="status"
				class="list-table-heading__cell-content list-table-heading__cell-clickable"
				:class="classColumn( 'status' )"
				@click="sortColumn( 'status' )"
			>
				<?php _e( 'Status', 'jet-booking' ); ?>
			</span>
		</cx-vui-list-table-heading>
		<cx-vui-list-table-item
			:slots="[ 'booking_id', 'apartment_id', 'apartment_unit', 'check_in_date', 'check_out_date', 'order_id', 'status' ]"
			slot="items"
			v-for="( item, index ) in itemsList"
			:key="item.booking_id + item.apartment_id"
		>
			<span slot="booking_id">{{ item.booking_id }}</span>
			<span slot="apartment_id">{{ getBookingLabel( item.apartment_id ) }}</span>
			<span slot="apartment_unit">{{ item.apartment_unit }}</span>
			<span slot="check_in_date">{{ item.check_in_date }}</span>
			<span slot="check_out_date">{{ item.check_out_date }}</span>
			<span slot="order_id">
				<a v-if="item.order_id" :href="getOrderLink( item.order_id )" target="_blank">#{{ item.order_id }}</a>
			</span>
			<span
				slot="status"
				:class="{
					'notice': true,
					'notice-alt': true,
					'notice-success': isFinished( item.status ),
					'notice-warning': isInProgress( item.status ),
					'notice-error': isInvalid( item.status ),
				}"
			>{{ statuses[ item.status ] }}</span>
			<div
				class="jet-abaf-actions"
				slot="status"
			>
				<cx-vui-button
					button-style="accent"
					size="mini"
					@click="showEditDialog( item, index )"
				><span slot="label"><?php esc_html_e( 'Edit', 'jet-appoinments-booking' ); ?></span></cx-vui-button>
				<cx-vui-button
					button-style="link-accent"
					size="link"
					@click="showDetailsDialog( item )"
				><span slot="label"><?php esc_html_e( 'Details', 'jet-appoinments-booking' ); ?></span></cx-vui-button>
				<cx-vui-button
					button-style="link-error"
					size="link"
					@click="showDeleteDialog( item.booking_id )"
				><span slot="label"><?php esc_html_e( 'Delete', 'jet-appoinments-booking' ); ?></span></cx-vui-button>
			</div>
		</cx-vui-list-table-item>
	</cx-vui-list-table>

	<cx-vui-pagination
		v-if="perPage < totalItems"
		:total="totalItems"
		:page-size="perPage"
		:current="pageNumber"
		@on-change="changePage"
	></cx-vui-pagination>

	<cx-vui-popup
		v-model="deleteDialog"
		body-width="460px"
		ok-label="<?php esc_html_e( 'Delete', 'jet-booking' ) ?>"
		cancel-label="<?php esc_html_e( 'Cancel', 'jet-booking' ) ?>"
		@on-cancel="deleteDialog = false"
		@on-ok="handleDelete"
	>
		<div class="cx-vui-subtitle" slot="title"><?php
			esc_html_e( 'Are you sure? Deleted booking can\'t be restored.', 'jet-booking' );
		?></div>
	</cx-vui-popup>
	<cx-vui-popup
		v-model="detailsDialog"
		body-width="400px"
		:footer="false"
	>
		<div class="cx-vui-subtitle" slot="title"><?php
			esc_html_e( 'Booking Details:', 'jet-booking' );
		?></div>
		<div class="jet-abaf-details" slot="content">
			<br>
			<div class="jet-abaf-details__fields">
				<template v-for="( itemValue, itemKey ) in currentItem">
					<div
						v-if="'check_in_date_timestamp' !== itemKey && 'check_out_date_timestamp' !== itemKey"
						:key="itemKey"
						:class="[ 'jet-abaf-details__field', 'jet-abaf-details__field-' + itemKey ]"
					>
						<div class="jet-abaf-details__label">{{ itemKey }}:</div>

						<div class="jet-abaf-details__content">
							<a v-if="'order_id' === itemKey && itemValue" :href="getOrderLink( itemValue )" target="_blank">
								#{{ itemValue }}
							</a>

							<span
								v-else-if="'status' === itemKey && itemValue"
								:class="{
									'notice': true,
									'notice-alt': true,
									'notice-success': isFinished( itemValue ),
									'notice-warning': isInProgress( itemValue ),
									'notice-error': isInvalid( itemValue ),
								}"
							>{{ statuses[ itemValue ] }}</span>

							<span v-else-if="'apartment_id' === itemKey && itemValue">{{ getBookingLabel( itemValue ) }}</span>

							<span v-else>{{ itemValue }}</span>
						</div>
					</div>
				</template>
			</div>

			<div class="jet-abaf-popup-actions">
				<cx-vui-button
					class="jet-abaf-popup-button-edit"
					@click="editDetailsItem( currentItem )"
					button-style="accent"
					size="mini"
				>
					<template slot="label">
						<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path
								d="M0.5 12.375V15.5H3.625L12.8417 6.28333L9.71667 3.15833L0.5 12.375ZM2.93333 13.8333H2.16667V13.0667L9.71667 5.51667L10.4833 6.28333L2.93333 13.8333ZM15.2583 2.69167L13.3083 0.741667C13.1417 0.575 12.9333 0.5 12.7167 0.5C12.5 0.5 12.2917 0.583333 12.1333 0.741667L10.6083 2.26667L13.7333 5.39167L15.2583 3.86667C15.5833 3.54167 15.5833 3.01667 15.2583 2.69167Z"
								fill="white"/>
						</svg>
						<?php _e( 'Edit', 'jet-booking' ); ?>
					</template>
				</cx-vui-button>

				<cx-vui-button
					class="jet-abaf-popup-button-delete"
					@click="deleteDetailsItem( currentItem.booking_id )"
					button-style="accent-border"
					size="mini"
				>
					<template slot="label">
						<svg width="12" height="16" viewBox="0 0 12 16" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path
								d="M0.999959 13.8333C0.999959 14.75 1.74996 15.5 2.66663 15.5H9.33329C10.25 15.5 11 14.75 11 13.8333V3.83333H0.999959V13.8333ZM2.66663 5.5H9.33329V13.8333H2.66663V5.5ZM8.91663 1.33333L8.08329 0.5H3.91663L3.08329 1.33333H0.166626V3H11.8333V1.33333H8.91663Z"
								fill="#007CBA"/>
						</svg>
						<?php _e( 'Delete', 'jet-booking' ); ?>
					</template>
				</cx-vui-button>
			</div>
		</div>
	</cx-vui-popup>
	<cx-vui-popup
		v-model="editDialog"
		body-width="500px"
		ok-label="<?php esc_html_e( 'Save', 'jet-booking' ) ?>"
		@on-cancel="editDialog = false"
		@on-ok="handleEdit"
	>
		<div class="cx-vui-subtitle" slot="title"><?php
			esc_html_e( 'Edit Booking:', 'jet-booking' );
		?></div>
		<div
			class="jet-abaf-bookings-error"
			slot="content"
			v-if="overlappingBookings"
			v-html="overlappingBookings"
		></div>
		<div class="jet-abaf-details" slot="content">
			<br>
			<div class="jet-abaf-details__booking">
				<div class="jet-abaf-details__booking-id">
					<div class="jet-abaf-details__label">Booking ID:</div>
					<div class="jet-abaf-details__content">{{ currentItem.booking_id }}</div>
				</div>

				<div class="jet-abaf-details__booking-order-id" v-if="currentItem.order_id">
					<div class="jet-abaf-details__label">Order ID:</div>
					<div class="jet-abaf-details__content">
						<a :href="getOrderLink( currentItem.order_id )" target="_blank">
							#{{ currentItem.order_id }}
						</a>
					</div>
				</div>
			</div>

			<div class="jet-abaf-details__field jet-abaf-details__field-status">
				<div class="jet-abaf-details__label">Status:</div>

				<div class="jet-abaf-details__content">
					<select v-model="currentItem.status">
						<option v-for="( label, value ) in statuses" :value="value" :key="value">
							{{ label }}
						</option>
					</select>
				</div>
			</div>

			<div class="jet-abaf-details__field jet-abaf-details__field-apartment_id">
				<div class="jet-abaf-details__label">Booking Item:</div>

				<div class="jet-abaf-details__content">
					<select  @change="onApartmentChange()" v-model="currentItem.apartment_id">
						<option v-for="( label, value ) in bookingInstances" :value="value" :key="value">
							{{ label }}
						</option>
					</select>
				</div>
			</div>

			<div :class="[ 'jet-abaf-details__booking-dates',  { 'jet-abaf-disabled': isDisabled } ]" ref="jetABAFDatePicker">
				<div class="jet-abaf-details__check-in-date">
					<div class="jet-abaf-details__label">Check in:</div>
					<div class="jet-abaf-details__content">
						<input type="text" v-model="currentItem.check_in_date" />
					</div>
				</div>

				<div class="jet-abaf-details__check-out-date">
					<div class="jet-abaf-details__label">Check out:</div>
					<div class="jet-abaf-details__content">
						<input type="text" v-model="currentItem.check_out_date" />
					</div>
				</div>
			</div>

			<div v-if="itemUnits.length" :class="[ 'jet-abaf-details__field jet-abaf-details__field-apartment_unit',  { 'jet-abaf-disabled': isDisabled } ]">
				<div class="jet-abaf-details__label">Booking Unit:</div>

				<div class="jet-abaf-details__content">
					<select v-model="currentItem.apartment_unit">
						<option v-for="unit in itemUnits" :value="unit.value" :key="unit.value">
							{{ unit.label }}
						</option>
					</select>
				</div>
			</div>

			<template v-for="( itemValue, itemKey ) in currentItem">
				<div
					v-if="beVisible( itemKey )"
					:key="itemKey"
					:class="[ 'jet-abaf-details__field', 'jet-abaf-details__field-' + itemKey ]"
				>
					<div class="jet-abaf-details__label">{{ itemKey }}:</div>

					<div class="jet-abaf-details__content">
						<input type="text" v-model="currentItem[ itemKey ]" />
					</div>
				</div>
			</template>
		</div>
	</cx-vui-popup>
</div>
