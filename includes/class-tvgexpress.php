<?php /**
	   * The core plugin class.
	   *
	   * This is used to define internationalization, admin-specific hooks, and
	   * public-facing site hooks.
	   *
	   * Also maintains the unique identifier of this plugin as well as the current
	   * version of the plugin.
	   *
	   * @since      1.0.0
	   * @package    Tvgexpress
	   * @subpackage Tvgexpress/includes
	   * @author     Smartmedia <smartmedia@smartmedia.is>
	   */
class Tvgexpress {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Tvgexpress_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'TVGEXPRESS_VERSION' ) ) {
			$this->version = TVGEXPRESS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'tvgexpress';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_admin_settings_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Tvgexpress_Loader. Orchestrates the hooks of the plugin.
	 * - Tvgexpress_i18n. Defines internationalization functionality.
	 * - Tvgexpress_Admin. Defines all hooks for the admin area.
	 * - Tvgexpress_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'lib/laff/vendor/autoload.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/methods/class-tvgexpress-options.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/methods/class-tvgexpress-service-factory.php';
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tvgexpress-printnode.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tvgexpress-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tvgexpress-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-tvgexpress-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-tvgexpress-public.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/includes/class-tvgexpress-admin-settings.php';

		$this->loader = new Tvgexpress_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Tvgexpress_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Tvgexpress_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Tvgexpress_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'order_metabox' );
		$this->loader->add_action( 'wp_ajax_tvg_ajax_change_shipment', $plugin_admin, 'change_shipment' );
		$this->loader->add_action( 'woocommerce_product_options_shipping', $plugin_admin, 'tvg_shipping_custom_field' );
		$this->loader->add_action( 'woocommerce_process_product_meta', $plugin_admin, 'tvg_shipping_custom_field_save' );
		$this->loader->add_action( 'wp_ajax_tvgexpress_printnode_action', $plugin_admin, 'print_shipping' );
		$this->loader->add_action( 'woocommerce_order_details_after_customer_details', $plugin_admin, 'order_shipping_status' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Tvgexpress_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'woocommerce_after_shipping_rate', $plugin_public, 'action_after_shipping_rate', 20, 2 );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		/* Register TVG shipping methods */
		$this->loader->add_filter( 'woocommerce_shipping_methods', $plugin_public, 'add_shipping' );

		/* Order TVG shipping rates */
		$this->loader->add_filter( 'woocommerce_package_rates', $plugin_public, 'tvg_sort_shipping_rates' );

		/* Loads postbox selectbox */
		$this->loader->add_filter( 'woocommerce_cart_shipping_method_full_label', $plugin_public, 'get_label', 10, 2 );

		/*Save shipping method and postbox preference*/
		$this->loader->add_action( 'woocommerce_checkout_update_order_meta', $plugin_public, 'save_postbox' );

		$this->loader->add_action( 'woocommerce_checkout_create_order_shipping_item', $plugin_public, 'action_checkout_create_order_shipping_item', 10, 4 );
		$this->loader->add_action( 'woocommerce_order_item_display_meta_key', $plugin_public, 'tvg_change_order_item_meta_title', 20, 3 );

		$this->loader->add_action( 'woocommerce_checkout_process', $plugin_public, 'validate_postbox_select' );

		/*Automatic creation of shipping */
		$status_update = get_option( 'tvg_status_update' );
		if ( ! empty( $status_update ) ) {

			$status_update = substr( current( $status_update ), 3 );
			$this->loader->add_action( 'woocommerce_order_status_' . $status_update, $plugin_public, 'create_shipping', 10 );
		}

		/*Order page buttons */
		$this->loader->add_filter( 'woocommerce_admin_order_actions', $plugin_public, 'add_actions_button', 10, 2 );
		$this->loader->add_action( 'admin_head', $plugin_public, 'add_actions_button_css', 10 );

		/*Create Shipping */
		$this->loader->add_action( 'admin_post_tvgexpress_create_action', $plugin_public, 'create_shipping_action', 10 );
		/*Create Pdf Action */
		$this->loader->add_action( 'admin_post_tvgexpress_create_pdf_action', $plugin_public, 'create_pdf_action', 10 );

		/*
		 * Print shipping
		 * $this->loader->add_action( 'admin_post_tvgexpress_printnode_action',$plugin_public, 'print_shipping',10);
		 */
	}
	/**
	 * Define admin settings hooks
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_settings_hooks() {
		$plugin_admin = new Tvgexpress_Admin_Settings( $this->get_plugin_name(), $this->get_version() );

		// Lets add an action to setup the admin menu in the left nav.
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_menu' );

		// Add some actions to setup the settings we want on the wp admin page.
		$this->loader->add_action( 'admin_init', $plugin_admin, 'setup_sections' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'setup_fields' );

		$this->loader->add_action( 'admin_post_process_api_form', $plugin_admin, 'submit_api_key' );
		$this->loader->add_action( 'admin_post_process_update_form', $plugin_admin, 'submit_update_access_key' );

		$this->loader->add_action( 'admin_post_tvg_delete_transient', $plugin_admin, 'tvg_delete_transient' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Tvgexpress_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
