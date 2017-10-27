<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://wearesmile.com
 * @since      1.0.0
 *
 * @package    Border_Control
 * @subpackage Border_Control/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Border_Control
 * @subpackage Border_Control/includes
 * @author     We Are SMILE Ltd <digital@wearesmile.com>
 */
class Border_Control_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		if ( ! function_exists( 'populate_roles' ) ) :
			require_once( ABSPATH . 'wp-admin/includes/schema.php' );
		endif;
		populate_roles();
	}

}
