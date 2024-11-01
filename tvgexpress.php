<?php /**
	   * EIMSKIP
	   *
	   * EIMSKIP er hraðflutningaþjónusta til og frá Íslandi sem
	   * sérhæfir sig í þjónustu við vefverslanir.
	   * Öll afgreiðsla og meðferð vörunnar er í forgangi og afhendingartími frá
	   * því að varan kemur til landsins er mun skemmri en í almennum flugsendingum.
	   * EIMSKIP byggist á „Door-To-Door“ afhendingu: Varan er sótt, send,
	   * tollafgreidd og loks afhent viðtakanda.
	   *
	   * @link    smartmedia.is
	   * @since   1.0.0
	   * @package EIMSKIP
	   *
	   * @wordpress-plugin
	   * Plugin Name:       EIMSKIP
	   * Plugin URI:        https://eimskip.is/
	   * Description:       Connection to EIMSKIP in Iceland
	   * Version:           2.2.2
	   * Author:            smartmedia
	   * Author URI:        https://smartmedia.is
	   * License:           GPL-2.0+
	   * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
	   * Text Domain:       tvgxpress
	   * Domain Path:       /languages
	   */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'TVGEXPRESS_VERSION', '2.2.2' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-tvgexpress-activator.php
 *
 * @return void
 */
function activate_tvgexpress() {
	include_once plugin_dir_path( __FILE__ ) . 'includes/class-tvgexpress-activator.php';
	Tvgexpress_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-tvgexpress-deactivator.php
 *
 * @return void
 */
function deactivate_tvgexpress() {
	include_once plugin_dir_path( __FILE__ ) . 'includes/class-tvgexpress-deactivator.php';
	Tvgexpress_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_tvgexpress' );
register_deactivation_hook( __FILE__, 'deactivate_tvgexpress' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-tvgexpress.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since  1.0.0
 * @return void
 */
function run_tvgexpress() {
	$plugin = new Tvgexpress();
	$plugin->run();

}
run_tvgexpress();
