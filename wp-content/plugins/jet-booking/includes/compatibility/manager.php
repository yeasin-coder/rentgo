<?php

namespace JET_ABAF\Compatibility;

class Manager {

	public function __construct() {
		add_action( 'init', [ $this, 'load_compatibility_packages' ] );
	}

	/**
	 * Load compatibility packages.
	 *
	 * Includes all available compatibility packages for provided plugins.
	 *
	 * @since  2.5.5
	 * @since  2.6.3 Added Polylang compatibility package.
	 * @access public
	 *
	 * @return void
	 */
	public function load_compatibility_packages() {

		$packages_list = [
			'polylang.php' => [
				'cb'   => 'class_exists',
				'args' => 'Polylang',
			],
			'wpml.php'     => [
				'cb'   => 'defined',
				'args' => 'WPML_ST_VERSION',
			],
		];

		foreach ( $packages_list as $file => $condition ) {
			if ( true === call_user_func( $condition['cb'], $condition['args'] ) ) {
				require JET_ABAF_PATH . 'includes/compatibility/packages/' . $file;
			}
		}

	}
}