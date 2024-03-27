<div :class="{ 'jet-abaf-loading': isLoading }">
	<div class="jet-booking-calendars-header">
		<cx-vui-button
			button-style="accent"
			size="mini"
			@click="showICalTemplateDialog()"
		>
			<span slot="label"><?php _e( 'iCalendar Template', 'jet-appoinments-booking' ); ?></span>
		</cx-vui-button>
	</div>

	<cx-vui-list-table
		:is-empty="! itemsList.length"
		empty-message="<?php _e( 'No calendars found', 'jet-booking' ); ?>"
	>
		<cx-vui-list-table-heading
			:slots="[ 'post_title', 'unit_title', 'export_url', 'import_url' ]"
			slot="heading"
		>
			<span slot="post_title"><?php _e( 'Post Title', 'jet-booking' ); ?></span>
			<span slot="unit_title"><?php _e( 'Unit Title', 'jet-booking' ); ?></span>
			<span slot="export_url"><?php _e( 'Export URL', 'jet-booking' ); ?></span>
			<span slot="import_url"><?php _e( 'External Calendars', 'jet-booking' ); ?></span>
		</cx-vui-list-table-heading>

		<cx-vui-list-table-item
			:slots="[ 'post_title', 'unit_title', 'export_url', 'import_url' ]"
			slot="items"
			v-for="( item, index ) in itemsList"
			:key="item.post_id + item.unit_id"
		>
			<span slot="post_title">{{ item.title }}</span>
			<span slot="unit_title">{{ item.unit_title }}</span>
			<code slot="export_url">{{ item.export_url }}</code>

			<div slot="import_url" class="jet-abaf-links">
				<ul v-if="item.import_url && item.import_url.length">
					<li v-for="url in item.import_url" :key="url">
						<a :href="url">{{ url }}</a>
					</li>
				</ul>
				<div v-else>--</div>
			</div>

			<div slot="import_url" class="jet-abaf-actions">
				<cx-vui-button
					v-if="item.import_url && item.import_url.length"
					button-style="accent-border"
					size="mini"
					@click="showSynchDialog( item )"
				>
					<span slot="label"><?php _e( 'Synch', 'jet-appoinments-booking' ); ?></span>
				</cx-vui-button>

				<cx-vui-button
					button-style="accent"
					size="mini"
					@click="showEditDialog( item, index )"
				>
					<span slot="label"><?php _e( 'Edit Calendars', 'jet-appoinments-booking' ); ?></span>
				</cx-vui-button>
			</div>
		</cx-vui-list-table-item>
	</cx-vui-list-table>

	<cx-vui-popup
		v-model="editDialog"
		body-width="400px"
		ok-label="<?php _e( 'Save', 'jet-booking' ) ?>"
		@on-cancel="editDialog = false"
		@on-ok="handleEdit"
	>
		<div class="cx-vui-subtitle" slot="title"><?php _e( 'Edit Calendars:', 'jet-booking' ); ?></div>
		<div class="jet-abaf-calendars jet-abaf-calendars-edit" slot="content">
			<br>
			<p v-for="( url, index ) in currentItem.import_url">
				<input
					type="url"
					placeholder="https://calendar-link.com"
					v-model="currentItem.import_url[ index ]"
					:style="{width: '100%'}"
				>
				<span class="dashicons dashicons-trash" @click="removeURL( index )"></span>
			</p>
			<a href="#" @click.prevent="addURL" :style="{ textDecoration: 'none' }">
				<b><?php _e( '+ New URL', 'jet-booking' ); ?></b>
			</a>
		</div>
	</cx-vui-popup>

	<cx-vui-popup
		v-model="synchDialog"
		body-width="600px"
		cancel-label="<?php _e( 'Close', 'jet-booking' ) ?>"
		@on-cancel="synchDialog = false"
		:show-ok="false"
	>
		<div class="cx-vui-subtitle" slot="title"><?php _e( 'Synchronizing Calendars:', 'jet-booking' ); ?></div>
		<div class="jet-abaf-calendars" slot="content">
			<div v-if="! synchLog"><?php _e( 'Processing...', 'jet-booking' ); ?></div>
			<div v-else v-html="synchLog" class="jet-abaf-synch-log"></div>
		</div>
	</cx-vui-popup>

	<cx-vui-popup
		v-model="iCalTemplateDialog"
		body-width="400px"
		ok-label="<?php _e( 'Save', 'jet-booking' ) ?>"
		@on-cancel="editDialog = false"
		@on-ok="handleICalTemplate"
	>
		<div class="cx-vui-subtitle" slot="title"><?php _e( 'iCalendar Template:', 'jet-booking' ); ?></div>
		<div class="jet-abaf-calendars" slot="content">
			<label>
				<?php _e( 'Summary', 'jet-booking' ); ?>
				<input
					type="text"
					v-model="iCalTemplate.summary"
					:style="{width: '100%'}"
				>
			</label>

			<label>
				<?php _e( 'Description', 'jet-booking' ); ?>
				<textarea
					v-model="iCalTemplate.description"
					:style="{width: '100%'}"
					rows="5"
				></textarea>
			</label>

			<div class="cx-vui-component__meta">
				<a href="<?php echo add_query_arg( array( 'page' => 'jet-engine#macros_generator' ), admin_url( 'admin.php' ) ); ?>" target="_blank" class="jet-engine-dash-help-link">
					<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.4413 7.39906C10.9421 6.89828 11.1925 6.29734 11.1925 5.59624C11.1925 4.71987 10.8795 3.9687 10.2535 3.34272C9.62754 2.71674 8.87637 2.40376 8 2.40376C7.12363 2.40376 6.37246 2.71674 5.74648 3.34272C5.1205 3.9687 4.80751 4.71987 4.80751 5.59624H6.38498C6.38498 5.17058 6.54773 4.79499 6.87324 4.46948C7.19875 4.14398 7.57434 3.98122 8 3.98122C8.42566 3.98122 8.80125 4.14398 9.12676 4.46948C9.45227 4.79499 9.61502 5.17058 9.61502 5.59624C9.61502 6.02191 9.45227 6.3975 9.12676 6.723L8.15024 7.73709C7.52426 8.41315 7.21127 9.16432 7.21127 9.99061V10.4038H8.78873C8.78873 9.57747 9.10172 8.82629 9.7277 8.15024L10.4413 7.39906ZM8.78873 13.5962V12.0188H7.21127V13.5962H8.78873ZM2.32864 2.3662C3.9061 0.788732 5.79656 0 8 0C10.2034 0 12.0814 0.788732 13.6338 2.3662C15.2113 3.91862 16 5.79656 16 8C16 10.2034 15.2113 12.0939 13.6338 13.6714C12.0814 15.2238 10.2034 16 8 16C5.79656 16 3.9061 15.2238 2.32864 13.6714C0.776213 12.0939 0 10.2034 0 8C0 5.79656 0.776213 3.91862 2.32864 2.3662Z" fill="#007CBA"></path></svg>
					Need some dynamic? Generate macros.
				</a>
			</div>
		</div>
	</cx-vui-popup>
</div>