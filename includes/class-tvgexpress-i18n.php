<?php /**
	   * Define the internationalization functionality.
	   *
	   * Loads and defines the internationalization files for this plugin
	   * so that it is ready for translation.
	   *
	   * @since      1.0.0
	   * @package    Tvgexpress
	   * @subpackage Tvgexpress/includes
	   * @author     Smartmedia <smartmedia@smartmedia.is>
	   */
class Tvgexpress_I18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'tvgxpress',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
