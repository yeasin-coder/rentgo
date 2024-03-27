<div class="jet-abaf-units-manager">
	<cx-vui-button
		button-style="accent"
		:loading="loading"
		@click="loadUnits"
		v-if="!loaded"
	>
		<span slot="label"><?php esc_html_e( 'Manage Units', 'jet-booking' ); ?></span>
	</cx-vui-button>
	<div v-else>
		<div class="cx-vui-subtitle"><?php esc_html_e( 'Available Units', 'jet-booking' ); ?></div>
		<cx-vui-list-table
			:is-empty="! unitsList.length"
			empty-message="No units were found for this apartment"
		>
			<cx-vui-list-table-heading
				:slots="[ 'unit_id', 'name', 'actions' ]"
				slot="heading"
			>
				<div slot="unit_id" style="width: 50px;"><?php esc_html_e( 'Unit ID', 'jet-booking' ); ?></div>
				<div slot="name"><?php esc_html_e( 'Nam', 'jet-booking' ); ?>e</div>
				<div slot="actions"><?php esc_html_e( 'Actions', 'jet-booking' ); ?></div>
			</cx-vui-list-table-heading>
			<cx-vui-list-table-item
				:slots="[ 'unit_id', 'name', 'actions' ]"
				slot="items"
				v-for="unit in unitsList"
				:key="unit.unit_id"
			>
				<div slot="unit_id" style="width: 50px;">{{ unit.unit_id }}</div>
				<div slot="name">
					<cx-vui-input
						v-if="unitToEdit && unit.unit_id === unitToEdit.unit_id"
						:wrapper-css="[ 'equalwidth' ]"
						size="fullwidth"
						:prevent-wrap="true"
						:autofocus="true"
						v-model="unitToEdit.unit_title"
						@on-keyup.stop.enter="saveUnit"
					></cx-vui-input>
					<div v-else>
						{{ unit.unit_title }}
					</div>
				</div>
				<div class="jet-abaf-unit-actions" slot="actions">
					<cx-vui-button
						button-style="link-accent"
						size="link"
						@click="saveUnit"
						v-if="unitToEdit && unit.unit_id === unitToEdit.unit_id"
					>
						<span slot="label"><?php esc_html_e( 'Save', 'jet-booking' ); ?></span>
					</cx-vui-button>
					<cx-vui-button
						v-else
						button-style="link-accent"
						size="link"
						@click="unitToEdit = unit"
					>
						<span slot="label"><?php esc_html_e( 'Edit', 'jet-booking' ); ?></span>
					</cx-vui-button><div class="jet-abaf-delete-unit">
						<cx-vui-button
							button-style="link-error"
							size="link"
							@click="unitToEdit = null"
							v-if="unitToEdit && unit.unit_id === unitToEdit.unit_id"
						>
							<span slot="label"><?php esc_html_e( 'Cancel', 'jet-booking' ); ?></span>
						</cx-vui-button>
						<cx-vui-button
							button-style="link-error"
							size="link"
							@click="unitToDelete = unit.unit_id"
							v-else
						>
							<span slot="label"><?php esc_html_e( 'Delete', 'jet-booking' ); ?></span>
						</cx-vui-button>
						<div
							class="cx-vui-tooltip"
							v-if="unit.unit_id === unitToDelete"
						>
							<?php esc_html_e( 'Are you sure?', 'jet-booking' ); ?>
							<br><span
								class="cx-vui-repeater-item__confrim-del"
								@click="deleteUnit"
							><?php esc_html_e( 'Yes', 'jet-booking' ); ?></span>
							/
							<span
								class="cx-vui-repeater-item__cancel-del"
								@click="unitToDelete = null"
							><?php esc_html_e( 'No', 'jet-booking' ); ?></span>
						</div>
					</div>
				</div>
			</cx-vui-list-table-item>
		</cx-vui-list-table>
		<br>
		<div class="cx-vui-subtitle"><?php esc_html_e( 'Add Units', 'jet-booking' ); ?></div>
		<div class="cx-vui-panel">
			<cx-vui-input
				label="Number"
				description="Enter number of created units"
				:wrapper-css="[ 'equalwidth' ]"
				size="fullwidth"
				type="number"
				v-model="newUnitsNum"
			></cx-vui-input>
			<cx-vui-input
				label="Title"
				description="Enter title of created units. If empty - apartment title will be used"
				:wrapper-css="[ 'equalwidth' ]"
				size="fullwidth"
				v-model="newUnitsTitle"
			></cx-vui-input>
		</div>
		<cx-vui-button
			button-style="accent"
			@click="insertUnits"
		>
			<span slot="label"><?php esc_html_e( 'Add Units', 'jet-booking' ); ?></span>
		</cx-vui-button>
	</div>
</div>
