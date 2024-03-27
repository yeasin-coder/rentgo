<div>
	<h3 class="cx-vui-subtitle"><?php esc_html_e( 'Booking Settings', 'jet-booking' ); ?></h3>
	<br>
	<div class="cx-vui-panel">
		<cx-vui-tabs
			:in-panel="false"
			:value="initialTab"
			layout="vertical"
		>
			<cx-vui-tabs-panel
				name="general"
				label="<?php esc_html_e( 'General', 'jet-booking' ); ?>"
				key="general"
			>
				<keep-alive>
					<jet-abaf-settings-general
						:settings="settings"
						@force-update="onUpdateSettings( $event, true )"
					></jet-abaf-settings-general>
				</keep-alive>
			</cx-vui-tabs-panel>
			<cx-vui-tabs-panel
				name="labels"
				label="<?php esc_html_e( 'Labels', 'jet-booking' ); ?>"
				key="labels"
			>
				<keep-alive>
					<jet-abaf-settings-labels
						:settings="settings"
						@force-update="onUpdateSettings( $event, true )"
					></jet-abaf-settings-labels>
				</keep-alive>
			</cx-vui-tabs-panel>
			<cx-vui-tabs-panel
				name="advanced"
				label="<?php esc_html_e( 'Advanced', 'jet-booking' ); ?>"
				key="advanced"
			>
				<keep-alive>
					<jet-abaf-settings-advanced
						:settings="settings"
						@force-update="onUpdateSettings( $event, true )"
					></jet-abaf-settings-advanced>
				</keep-alive>
			</cx-vui-tabs-panel>
			<cx-vui-tabs-panel
				name="configuration"
				label="<?php esc_html_e( 'Configuration', 'jet-booking' ); ?>"
				key="configuration"
			>
				<keep-alive>
					<jet-abaf-settings-configuration
						:settings="settings"
						@force-update="onUpdateSettings( $event, true )"
					></jet-abaf-settings-configuration>
				</keep-alive>
			</cx-vui-tabs-panel>
			<cx-vui-tabs-panel
				name="schedule"
				label="<?php esc_html_e( 'Schedule', 'jet-booking' ); ?>"
				key="schedule"
			>
				<keep-alive>
					<jet-abaf-settings-schedule
						:settings="settings"
						@force-update="onUpdateSettings( $event, true )"
					></jet-abaf-settings-schedule>
				</keep-alive>
			</cx-vui-tabs-panel>
			<cx-vui-tabs-panel
				name="tools"
				label="<?php esc_html_e( 'Tools', 'jet-booking' ); ?>"
				key="tools"
			>
				<keep-alive>
					<jet-abaf-settings-tools
						:settings="settings"
						:dbTablesExists="dbTablesExists"
						@force-update="onUpdateSettings( $event, true )"
					></jet-abaf-settings-tools>
				</keep-alive>
			</cx-vui-tabs-panel>
		</cx-vui-tabs>
	</div>
</div>