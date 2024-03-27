<?php
namespace JET_ABAF\Dashboard;

use JET_ABAF\Plugin;

/**
 * Dashboard interface manager
 */
class Manager {

	private $pages        = array();
	private $current_page = false;

	/**
	 * [__construct description]
	 * @param array $pages [description]
	 */
	public function __construct( $pages = array() ) {

		foreach ( $pages as $page ) {

			$this->pages[ $page->slug() ] = $page;

			if ( $page->is_setup_page() ) {
				Plugin::instance()->setup->register_setup_page( $page );
			}

			if ( $page->is_settings_page() ) {
				Plugin::instance()->setup->register_setup_success_page( $page );
			}

		}

		add_action( 'admin_menu', array( $this, 'register_pages' ) );

		if ( $this->is_dashboard_page() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
			$page = ! empty( $_GET['page'] ) ? esc_attr( $_GET['page'] ) : false;
			$this->current_page = $this->pages[ $page ];
		}

	}

	/**
	 * Check if is dashboard page
	 *
	 * @return boolean [description]
	 */
	public function is_dashboard_page() {

		$page = ! empty( $_GET['page'] ) ? esc_attr( $_GET['page'] ) : false;

		if ( ! $page ) {
			return false;
		} else {
			return isset( $this->pages[ $page ] );
		}

	}

	/**
	 * Check if passed page is currently dispalyed
	 *
	 * @return boolean [description]
	 */
	public function is_page_now( $page ) {

		if ( ! $this->is_dashboard_page() ) {
			return false;
		}

		return ( $page->slug() === $this->current_page->slug() );

	}

	/**
	 * Dashboard assets
	 *
	 * @param  [type] $hook [description]
	 * @return [type]       [description]
	 */
	public function assets( $hook ) {

		if ( ! function_exists( 'jet_engine' ) ) {
			return;
		}

		$ui_data = jet_engine()->framework->get_included_module_data( 'cherry-x-vue-ui.php' );
		$ui      = new \CX_Vue_UI( $ui_data );

		$ui->enqueue_assets();

		$this->current_page->enqueue_script( 'jet-abaf-dashboard-common', 'admin/common.js' );
		$this->current_page->assets();

		$config = $this->current_page->page_config();

		if ( $config->is_set() ) {
			wp_localize_script( $config->get( 'handle' ), 'JetABAFConfig', $config->get( 'config' ) );
		}

		add_action( 'admin_footer', array( $this, 'render_vue_templates' ) );

	}

	/**
	 * Render vue templates set for current apge
	 *
	 * @return [type] [description]
	 */
	public function render_vue_templates() {
		$this->current_page->render_vue_template( 'go-to-setup', 'common' );
		$this->current_page->render_vue_templates();
	}

	/**
	 * Register appointments
	 * @return [type] [description]
	 */
	public function register_pages() {

		$parent = false;

		foreach ( $this->pages as $page ) {

			if ( $page->is_hidden() ) {
				continue;
			}

			if ( ! $parent ) {

				$parent = $page->slug();

				add_menu_page(
					$page->title(),
					$page->title(),
					'manage_options',
					$page->slug(),
					array( $page, 'render' ),
					'data:image/svg+xml;base64,' . base64_encode('<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M20 1H4C2.34315 1 1 2.34315 1 4V20C1 21.6569 2.34315 23 4 23H20C21.6569 23 23 21.6569 23 20V4C23 2.34315 21.6569 1 20 1ZM4 0C1.79086 0 0 1.79086 0 4V20C0 22.2091 1.79086 24 4 24H20C22.2091 24 24 22.2091 24 20V4C24 1.79086 22.2091 0 20 0H4Z" fill="black"/><path fill-rule="evenodd" clip-rule="evenodd" d="M21.6293 6.00066C21.9402 5.98148 22.1176 6.38578 21.911 6.64277L20.0722 8.93035C19.8569 9.19824 19.4556 9.02698 19.4598 8.669L19.4708 7.74084C19.4722 7.61923 19.4216 7.50398 19.3343 7.42975L18.6676 6.86321C18.4105 6.6447 18.5378 6.19134 18.8619 6.17135L21.6293 6.00066ZM6.99835 12.008C6.99835 14.1993 5.20706 15.9751 2.99967 15.9751C2.44655 15.9751 2 15.5293 2 14.9827C2 14.4361 2.44655 13.9928 2.99967 13.9928C4.10336 13.9928 4.99901 13.1036 4.99901 12.008V9.03323C4.99901 8.48413 5.44556 8.04082 5.99868 8.04082C6.55179 8.04082 6.99835 8.48413 6.99835 9.03323V12.008ZM17.7765 12.008C17.7765 13.1036 18.6721 13.9928 19.7758 13.9928C20.329 13.9928 20.7755 14.4336 20.7755 14.9827C20.7755 15.5318 20.329 15.9751 19.7758 15.9751C17.5684 15.9751 15.7772 14.1993 15.7772 12.008V9.03323C15.7772 8.48413 16.2237 8.04082 16.7768 8.04082C17.33 8.04082 17.7765 8.48665 17.7765 9.03323V9.92237H18.5707C19.1238 9.92237 19.5729 10.3682 19.5729 10.9173C19.5729 11.4664 19.1238 11.9122 18.5707 11.9122H17.7765V12.008ZM15.2038 10.6176C15.2063 10.6151 15.2088 10.6151 15.2088 10.6151C14.8942 9.79393 14.3056 9.07355 13.4835 8.60001C11.5755 7.50181 9.13979 8.15166 8.04117 10.0508C6.94001 11.9475 7.59462 14.3731 9.50008 15.4688C10.9032 16.2749 12.593 16.1338 13.8261 15.2472L13.8184 15.2371C14.1026 15.0633 14.2904 14.751 14.2904 14.3958C14.2904 13.8492 13.8438 13.4059 13.2932 13.4059C13.0268 13.4059 12.7833 13.5092 12.6057 13.6805C12.0069 14.081 11.2102 14.1439 10.5378 13.7762L14.5644 11.9198C14.7978 11.8493 15.0059 11.6931 15.1353 11.4664C15.2926 11.1969 15.3078 10.8871 15.2038 10.6176ZM12.4864 10.3153C12.6057 10.3833 12.7122 10.4614 12.8112 10.5471L9.49754 12.0709C9.48993 11.7208 9.5762 11.3657 9.76395 11.0407C10.3145 10.0937 11.5324 9.76874 12.4864 10.3153Z" fill="#24292D"/></svg>')
				);

			} else {

				add_submenu_page(
					$parent,
					$page->title(),
					$page->title(),
					'manage_options',
					$page->slug(),
					array( $page, 'render' )
				);

			}
		}

	}

}
