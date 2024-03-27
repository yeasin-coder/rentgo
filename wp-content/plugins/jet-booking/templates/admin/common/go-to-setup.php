<div class="jet-abaf-to-setup cx-vui-component cx-vui-component--equalwidth">
	<div class="cx-vui-component__meta">
		<div class="cx-vui-component__label" style="margin: 0 0 15px;"><?php
			esc_html_e( 'Start with some basic settings before using', 'jet-booking' );
		?></div>
		<cx-vui-button
			tag-name="a"
			:button-style="'accent'"
			:url="setupURL"
		>
			<span slot="label"><?php esc_html_e( 'Go to setup', 'jet-booking' ); ?></span>
		</cx-vui-button>
	</div>
</div>