<div class="jet-abaf-bookings-filter">
	<div class="cx-vui-panel">
		<div class="jet-abaf-navigation-row">
			<div>
				<cx-vui-button
					@click="setMode( 'all' )"
					:button-style="modeButtonStyle( 'all' )"
					size="mini"
				>
					<template slot="label">
						<?php _e( 'All', 'jet-booking' ); ?>
					</template>
				</cx-vui-button>

				<cx-vui-button
					@click="setMode( 'upcoming' )"
					:button-style="modeButtonStyle( 'upcoming' )"
					size="mini"
				>
					<template slot="label">
						<?php _e( 'Upcoming', 'jet-booking' ); ?>
					</template>
				</cx-vui-button>

				<cx-vui-button
					@click="setMode( 'past' )"
					:button-style="modeButtonStyle( 'past' )"
					size="mini"
				>
					<template slot="label">
						<?php _e( 'Past', 'jet-booking' ); ?>
					</template>
				</cx-vui-button>
			</div>

			<div>
				<cx-vui-button
					class="jet-abaf-show-filters"
					@click="expandFilters = ! expandFilters"
					button-style="link-accent"
					size="mini"
				>
					<svg
						slot="label"
						xmlns="http://www.w3.org/2000/svg"
						width="16"
						height="16"
						viewBox="0 0 24 24"
						style="margin:0 5px 0 0;">
						<path
							d="M19.479 2l-7.479 12.543v5.924l-1-.6v-5.324l-7.479-12.543h15.958zm3.521-2h-23l9 15.094v5.906l5 3v-8.906l9-15.094z"
							fill="currentColor"
						/>
					</svg>

					<span slot="label">
						<?php _e( 'Filters', 'jet-booking' ); ?>
					</span>
				</cx-vui-button>
			</div>
		</div>

		<div v-if="expandFilters" class="jet-abaf-filters-row">
			<template v-for="( filter, name ) in filters" :key="name">
				<cx-vui-select
					v-if="isVisible( name, filter, 'select' )"
					:label="filter.label"
					:wrapper-css="[ 'jet-abaf-filter' ]"
					:options-list="prepareObjectForOptions( filter.value )"
					:value="currentFilters[ name ]"
					@input="updateFilters( $event, name, filter.type )"
				></cx-vui-select>

				<cx-vui-component-wrapper
					v-else-if="isVisible( name, filter, 'date-picker' )"
					:wrapper-css="[ 'jet-abaf-filter' ]"
					:label="filter.label"
				>
					<vuejs-datepicker
						input-class="cx-vui-input size-fullwidth"
						:value="currentFilters[ name ]"
						:format="dateFormat"
						:monday-first="!! monday_first"
						placeholder="<?php _e( 'dd/mm/yyyy', 'jet-booking' ); ?>"
						@input="updateFilters( $event, name, filter.type )"
					></vuejs-datepicker>
					<span
						v-if="currentFilters[ name ]"
						class="jet-abaf-date-clear"
						@click="updateFilters( '', name, filter.type )"
					>
						<svg width="12" height="16" viewBox="0 0 12 16" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path
								d="M0.999998 13.8333C0.999998 14.75 1.75 15.5 2.66666 15.5H9.33333C10.25 15.5 11 14.75 11 13.8333V3.83333H0.999998V13.8333ZM2.66666 5.5H9.33333V13.8333H2.66666V5.5ZM8.91667 1.33333L8.08333 0.5H3.91666L3.08333 1.33333H0.166664V3H11.8333V1.33333H8.91667Z"
								fill="#D6336C"
							/>
						</svg>
					</span>
				</cx-vui-component-wrapper>
			</template>

			<cx-vui-button
				v-if="0 !== Object.keys( currentFilters ).length"
				class="jet-abaf-clear-filters"
				@click="clearFilter()"
				button-style="accent-border"
				size="mini"
			>
				<template slot="label">
					<?php _e( 'Clear', 'jet-booking' ); ?>
				</template>
			</cx-vui-button>
		</div>
	</div>
</div>