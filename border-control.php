<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wearesmile.com
 * @since             1.0.0
 * @package           Border_Control
 *
 * @wordpress-plugin
 * Plugin Name:       Border Control
 * Plugin URI:        https://wearesmile.com
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Author:            We are SMILE Ltd
 * Author URI:        https://wearesmile.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       border-control
 * Domain Path:       /languages
 * version:			  1.0.4
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'BORDER_CONTROL_VERSION', '1.0.3' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-border-control-activator.php
 */
function activate_border_control() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-border-control-activator.php';
	Border_Control_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-border-control-deactivator.php
 */
function deactivate_border_control() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-border-control-deactivator.php';
	Border_Control_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_border_control' );
register_deactivation_hook( __FILE__, 'deactivate_border_control' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-border-control.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_border_control() {

	$plugin = new Border_Control();
	$plugin->run();

}
run_border_control();
