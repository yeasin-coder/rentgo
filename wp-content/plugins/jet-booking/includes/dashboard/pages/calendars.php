<?php

namespace JET_ABAF\Dashboard\Pages;

use JET_ABAF\Dashboard\Helpers\Page_Config;
use JET_ABAF\Plugin;

/**
 * Base dashboard page
 */
class Calendars extends Base {

	/**
	 * Page slug
	 *
	 * @return string
	 */
	public function slug() {
		return 'jet-abaf-calendars';
	}

	/**
	 * Page title
	 *
	 * @return string
	 */
	public function title() {
		return esc_html__( 'Calendars', 'jet-booking' );
	}

	/**
	 * Render.
	 *
	 * Page render function.
	 *
	 * @since  2.7.0 Updated styles.
	 * @access public
	 *
	 * @return void
	 */
	public function render() {
		?>
		<style type="text/css">
			label {
				display: block;
			}

			label + label {
				margin-top: 10px;
			}

			.jet-booking-calendars-header {
				padding: 15px 0;
			}

			.cell--post_title {
				flex: 0 0 12%;
			}

			.cell--unit_title {
				flex: 0 0 12%;
			}

			.cell--export_url {
				flex: 0 0 30%;
				display: flex;
				justify-content: space-between;
				align-items: center;
			}

			.cell--import_url {
				flex: 0 0 46%;
				display: flex;
				justify-content: space-between;
				align-items: center;
			}

			.jet-abaf-links {
				flex: 0 0 60%;
				word-break: break-word;
			}

			.jet-abaf-actions button + button {
				margin: 0 0 0 10px;
			}

			.jet-abaf-loading {
				opacity: .6;
			}

			.cx-vue-list-table code {
				max-width: 100%;
				word-break: break-word;
			}

			.jet-abaf-synch-log b {
				word-break: break-word;
			}

			.cx-vui-component__meta {
				align-items: flex-end;
			}

			.cx-vui-component__meta .jet-engine-dash-help-link {
				display: inline-flex;
				align-items: center;
				text-decoration: none;
				font-weight: 700;
			}

			.cx-vui-component__meta svg {
				margin: -2px 5px 0 0;
			}

			.cx-vui-popup__body {
				display: flex;
				flex-direction: column;
				overflow: hidden;
				max-height: calc( 100% - 200px );
			}

			.cx-vui-popup__body input {
				margin: 0;
			}

			.cx-vui-popup__content {
				overflow-y: auto;
			}

			.jet-abaf-calendars-edit p {
				display: flex;
				gap: 5px;
				align-items: center;
			}

			.jet-abaf-calendars-edit p .dashicons {
				cursor: pointer;
			}

			.jet-abaf-calendars-edit p .dashicons:hover {
				color: #23282d;
			}
		</style>
		<div id="jet-abaf-ical-page"></div>
		<?php
	}

	/**
	 * Return  page config object
	 *
	 * @return [type] [description]
	 */
	public function page_config() {
		return new Page_Config(
			$this->slug(),
			array(
				'api' => Plugin::instance()->rest_api->get_urls( false ),
			)
		);
	}

	/**
	 * Page specific assets
	 *
	 * @return [type] [description]
	 */
	public function assets() {
		$this->enqueue_script( $this->slug(), 'admin/calendars.js' );
	}

	/**
	 * Set to true to hide page from admin menu
	 *
	 * @return boolean [description]
	 */
	public function is_hidden() {
		return ! Plugin::instance()->settings->get( 'ical_synch' );
	}

	/**
	 * Page components templates
	 *
	 * @return [type] [description]
	 */
	public function vue_templates() {
		return array(
			'calendars',
			'calendars-list',
		);
	}

}