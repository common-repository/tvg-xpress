<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * This class handles all the admin-related functionality for the Eimskip plugin.
 * It includes settings, menus, and other admin-specific features.
 *
 * @link       smartmedia.is
 * @since      1.0.0
 * @package    Tvgexpress
 * @subpackage Tvgexpress/admin
 */
class Tvgexpress_Admin_Settings extends Tvgexpress_Admin {
	/**
	 * The name of the plugin.
	 *
	 * This variable holds the name of the plugin. It is used for various purposes
	 * such as generating unique option keys, enqueueing scripts or styles, etc.
	 *
	 * @var string $plugin_name The name of the plugin.
	 */
	private $plugin_name;
	/**
	 * The version number of the plugin.
	 *
	 * This variable holds the version number of the plugin. It is used for tracking
	 * and managing plugin updates, enqueueing scripts or styles, and other version-related operations.
	 *
	 * @var string $version The version number of the plugin.
	 */
	private $version;
	/**
	 * Contruct function
	 *
	 * @since    1.0.0
	 * @param string $plugin_name Plugin name.
	 * @param string $version The Version of the plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Add the menu items to the admin menu
	 *
	 * @since    1.0.0
	 */
	public function add_admin_menu() {
		add_menu_page(
			'EIMSKIP',
			'EIMSKIP',
			'manage_options',
			'eimskip-settings',
			array( $this, 'display_tvg_admin_page' ),
			'dashicons-cloud'
		);

		add_submenu_page(
			'eimskip-settings',
			'Stillingar',
			'Stillingar',
			'manage_options',
			'eimskip-settings',
			array( $this, 'display_tvg_admin_page' )
		);

		add_submenu_page(
			'eimskip-settings',
			'API Keys',
			'API Keys',
			'manage_options',
			'eimskip-settings/api-key',
			array( $this, 'display_api_page' )
		);
	}
	/**
	 * Display the TVG admin page
	 *
	 * @since    1.0.0
	 */
	public function display_tvg_admin_page() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . '../admin/partials/tvgexpress-admin-display.php';
	}
	/**
	 * Display the API page
	 *
	 * @since    1.0.0
	 */
	public function display_api_page() {

		$settings = array(
			'title'  => 'API',
			'action' => 'process_api_form',
			'fields' => array(
				array(
					'name'  => 'tvg_api_username',
					'label' => __( 'Username' ),
					'value' => get_option( 'tvg_api_username' ),
				),
				array(
					'name'  => 'tvg_api_key',
					'label' => __( 'Password' ),
					'type'  => 'password',
					'value' => $this->decrypt( get_option( 'tvg_api_key' ) ),
				),
                array(
                    'name'  => 'tvg_xgateway_api_key',
                    'label' => __( 'Gateway API key' ),
                    'type'  => 'text',
                    'description' => 'XGateway API Key',
                    'value' => $this->decrypt( get_option( 'tvg_xgateway_api_key' ) ),
                ),
				array(
					'name'  => 'tvg_api_demo',
					'label' => __( 'Enable Test Mode', 'tvgxpress' ),
					'type'  => 'checkbox',
					'value' => get_option( 'tvg_api_demo' ),
				),
				array(
					'name'  => 'tvg_printnode_api_key',
					'label' => __( 'PrintNode API Key' ),
					'type'  => 'password',
					'value' => $this->decrypt( get_option( 'tvg_printnode_api_key' ) ),
				),
			),
		);

		$this->settings = $settings;
		require_once plugin_dir_path( dirname( __FILE__ ) ) . '../admin/partials/tvgexpress-admin-api-display.php';
	}
	/**
	 * Setup the sections for the settings page
	 *
	 * @since    1.0.0
	 */
	public function setup_sections() {
		add_settings_section( 'settings_section', __( 'Settings', 'tvgxpress' ), array( $this, 'section_callback' ), 'tvg-options' );
		add_settings_section( 'sender_section', __( 'Sender', 'tvgxpress' ), array( $this, 'section_callback' ), 'tvg-options' );
	}
	/**
	 * Section callback
	 *
	 * @since    1.0.0
	 * @param string $arguments Section Callback.
	 */
	public function section_callback( $arguments ) {
		switch ( $arguments['id'] ) {
			case 'settings_section':
				echo '<p>' . esc_html( __( 'General settings', 'tvgxpress' ) ) . '</p>';
				break;
		}
	}
	/**
	 * Setup fields
	 *
	 * @since    1.0.0
	 */
	public function setup_fields() {

		$prefix = 'tvg_';
		$fields = array(
			array(
				'uid'          => $prefix . 'status_update',
				'label'        => __( 'Create shipment automaticly', 'tvgxpress' ),
				'section'      => 'settings_section',
				'type'         => 'select',
				'options'      => array( '0' => __( 'Disabled' ) ) + wc_get_order_statuses(),
				'supplemental' => __( 'Status update', 'tvgxpress' ),
				'default'      => array( '0' ),
			),
			array(
				'uid'          => $prefix . 'kt',
				'label'        => __( 'Kennitölu field', 'tvgxpress' ),
				'section'      => 'settings_section',
				'type'         => 'text',
				'supplemental' => __( 'Ef notað er custom field fyrir kennitölu', 'tvgxpress' ),
				'default'      => '',
			),
			array(
				'uid'          => $prefix . 'sender_name',
				'label'        => __( 'Name', 'tvgxpress' ),
				'section'      => 'sender_section',
				'type'         => 'text',
				'supplemental' => __( 'Sender name', 'tvgxpress' ),
				'default'      => '',
			),
			array(
				'uid'          => $prefix . 'sender_kt',
				'label'        => 'Kennitala',
				'section'      => 'sender_section',
				'type'         => 'text',
				'supplemental' => __( 'Kennitala sendanda', 'tvgxpress' ),
				'default'      => '',
			),
			array(
				'uid'          => $prefix . 'sender_address',
				'label'        => __( 'Address', 'tvgxpress' ),
				'section'      => 'sender_section',
				'type'         => 'text',
				'supplemental' => __( 'Sender address', 'tvgxpress' ),
				'default'      => '',
			),
			array(
				'uid'          => $prefix . 'sender_city',
				'label'        => __( 'City', 'tvgxpress' ),
				'section'      => 'sender_section',
				'type'         => 'text',
				'supplemental' => __( 'Sender city', 'tvgxpress' ),
				'default'      => '',
			),
			array(
				'uid'          => $prefix . 'sender_postcode',
				'label'        => __( 'Postcode', 'tvgxpress' ),
				'section'      => 'sender_section',
				'type'         => 'text',
				'supplemental' => __( 'Sender postcode', 'tvgxpress' ),
				'default'      => '',
			),
			array(
				'uid'          => $prefix . 'sender_country_code',
				'label'        => __( 'Country code', 'tvgxpress' ),
				'section'      => 'sender_section',
				'type'         => 'text',
				'supplemental' => __( 'Sender country code', 'tvgxpress' ),
				'placeholder'  => 'IS',
				'default'      => 'IS',
			),
			array(
				'uid'          => $prefix . 'sender_contact',
				'label'        => __( 'Contact', 'tvgxpress' ),
				'section'      => 'sender_section',
				'type'         => 'text',
				'supplemental' => __( 'Sender contact', 'tvgxpress' ),
				'placeholder'  => '',
				'default'      => '',
			),
			array(
				'uid'          => $prefix . 'sender_email',
				'label'        => __( 'Email', 'tvgxpress' ),
				'section'      => 'sender_section',
				'type'         => 'text',
				'supplemental' => __( 'Sender email', 'tvgxpress' ),
				'placeholder'  => '',
				'default'      => '',
			),
			array(
				'uid'          => $prefix . 'sender_phone',
				'label'        => __( 'Phone', 'tvgxpress' ),
				'section'      => 'sender_section',
				'type'         => 'text',
				'supplemental' => __( 'Sender phone', 'tvgxpress' ),
				'placeholder'  => '',
				'default'      => '',
			),
		);

		$print_node_api_key = $this->decrypt( get_option( 'tvg_printnode_api_key' ) );
		if ( ! empty( $print_node_api_key ) ) {
			include_once __DIR__ . '/../../includes/class-tvgexpress-printnode.php';
			$print_node      = new Tvgexpress_PrintNode( $print_node_api_key );
			$printers        = $print_node->get_printers();
			$printer_options = array();
			foreach ( $printers as $printer ) {
				if ( ! empty( $printer->id ) ) {
					$printer_options[ $printer->id ] = $printer->name;
				}
			}

			$fields[] = array(
				'uid'          => $prefix . 'printer',
				'label'        => __( 'Printer', 'tvgxpress' ),
				'section'      => 'sender_section',
				'type'         => 'select',
				'options'      => $printer_options,
				'supplemental' => __( 'Printer', 'tvgxpress' ),
				'placeholder'  => '',
				'default'      => '',
			);
		}
		$this->add_fields( $fields, 'tvg-options' );

	}
	/**
	 * Add fields
	 *
	 * @since    1.0.0
	 * @param array  $fields All the fields.
	 * @param string $group The group of fields.
	 */
	public function add_fields( $fields, $group ) {
		// Lets go through each field in the array and set it up.
		foreach ( $fields as $field ) {
			add_settings_field( $field['uid'], $field['label'], array( $this, 'field_callback' ), 'tvg-options', $field['section'], $field );
			register_setting( $group, $field['uid'] );
		}
	}

	/**
	 * This handles all types of fields for the settings
	 *
	 * @since    1.0.0
	 * @param array $arguments Arguments param.
	 */
	public function field_callback( $arguments ) {

		// Set our $value to that of whats in the DB.
		$value = get_option( $arguments['uid'] );

		// Only set it to default if we get no value from the DB and a default for the field has been set.
		if ( ! $value ) {
			$value = $arguments['default'];
		}
		if ( ! isset( $arguments['placeholder'] ) ) {
			$arguments['placeholder'] = '';
		}
		// Lets do some setup based ont he type of element we are trying to display.
		switch ( $arguments['type'] ) {
			case 'text':
			case 'password':
			case 'number':
				printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', esc_html( $arguments['uid'] ), esc_html( $arguments['type'] ), esc_html( $arguments['placeholder'] ), esc_html( $value ) );
				break;
			case 'textarea':
				printf( '<textarea name="%1$s" id="%1$s" placeholder="%2$s" rows="5" cols="50">%3$s</textarea>', esc_html( $arguments['uid'] ), esc_html( $arguments['placeholder'] ), esc_textarea( $value ) );
				break;
			case 'select':
			case 'multiselect':
				if ( ! empty( $arguments['options'] ) && is_array( $arguments['options'] ) ) {
					$attributes     = '';
					$options_markup = '';
					foreach ( $arguments['options'] as $key => $label ) {
						$selected = ( is_array( $value ) ) ? $value[ array_search( $key, $value, true ) ] : '';

						$options_markup .= sprintf( '<option value="%s" %s>%s</option>', esc_html( $key ), selected( $selected, $key, false ), esc_html( $label ) );
					}
					if ( 'multiselect' === $arguments['type'] ) {
						$attributes = ' multiple="multiple" ';
					}
					printf( '<select name="%1$s[]" id="%1$s" %2$s>%3$s</select>', esc_html( $arguments['uid'] ), esc_html( $attributes ), wp_kses( $options_markup, $this->allowedhtml() ) );

				}
				break;
			case 'radio':
			case 'checkbox':
				if ( ! empty( $arguments['options'] ) && is_array( $arguments['options'] ) ) {
					$options_markup = '';
					$iterator       = 0;
					foreach ( $arguments['options'] as $key => $label ) {
						$iterator++;
						$is_checked = '';
						// This case handles if there is only one checkbox and we don't have anything saved yet.
						if ( isset( $value[ array_search( $key, $value, true ) ] ) ) {
							$is_checked = checked( $value[ array_search( $key, $value, true ) ], $key, false );
						} else {
							$is_checked = '';
						}
						$options_markup .= sprintf( '<label for="%1$s_%6$s"><input id="%1$s_%6$s" name="%1$s[]" type="%2$s" value="%3$s" %4$s /> %5$s</label><br/>', esc_html( $arguments['uid'] ), esc_html( $arguments['type'] ), esc_html( $key ), esc_html( $is_checked ), esc_html( $label ), esc_html( $iterator ) );
					}
					printf( '<fieldset>%s</fieldset>', wp_kses( $options_markup, $this->allowedhtml() ) );
				}
				break;
		}

		// If there is helper text, lets show it.
		if ( array_key_exists( 'helper', $arguments ) ) {
			$helper = $arguments['helper'];
			if ( $helper ) {
				printf( '<span class="helper"> %s</span>', esc_html( $helper ) );
			}
		}
		// If there is supplemental text lets show it.
		if ( array_key_exists( 'supplemental', $arguments ) ) {
			$supplemental = $arguments['supplemental'];
			if ( $supplemental ) {
				printf( '<p class="description">%s</p>', esc_html( $supplemental ) );
			}
		}
	}
	/**
	 * Callback function allowed html tags
	 */
	public function allowedhtml() {
		$allowed_html = array(
			'option' => array(
				'selected' => array(),
				'disabled' => array(),
				'value'    => array(),
			),
			'label'  => array(
				'for' => array(),
			),
			'input'  => array(
				'class'       => array(),
				'type'        => array(),
				'name'        => array(),
				'id'          => array(),
				'placeholder' => array(),
				'value'       => array(),
				'checked'     => array(),
			),
			'br'     => array(),
		);

		return $allowed_html;
	}
	/**
	 * Callback function for handling the submission of the API key form.
	 */
	public function submit_api_key() {

		// Verify the nonce !
		$nonce = isset( $_POST['api_form_nonce'] ) ? sanitize_key( $_POST['api_form_nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'api_form_action' ) ) {
			wp_die( 'Invalid nonce verification' );
		}

		if ( isset( $_POST['tvg_api_key'] ) ) {
			$api_key = sanitize_text_field( wp_unslash( $_POST['tvg_api_key'] ) );

			if ( ! empty( $api_key ) ) {
				$api_key = $this->encrypt( $api_key );
			}

			$this->update_or_insert_option( 'tvg_api_key', $api_key );
		}
        if ( isset( $_POST['tvg_xgateway_api_key'] ) ) {
            $api_key = sanitize_text_field( wp_unslash( $_POST['tvg_xgateway_api_key'] ) );

            if ( ! empty( $api_key ) ) {
                $api_key = $this->encrypt( $api_key );
            }

            $this->update_or_insert_option( 'tvg_xgateway_api_key', $api_key );
        }
		if ( isset( $_POST['tvg_printnode_api_key'] ) ) {
			$print_api_key = sanitize_text_field( wp_unslash( $_POST['tvg_printnode_api_key'] ) );

			if ( ! empty( $print_api_key ) ) {
				$print_api_key = $this->encrypt( $print_api_key );
			}

			$this->update_or_insert_option( 'tvg_printnode_api_key', $print_api_key );
		}
		if ( isset( $_POST['tvg_api_username'] ) ) {
			$this->update_or_insert_option( 'tvg_api_username', sanitize_text_field( wp_unslash( $_POST['tvg_api_username'] ) ) );
		}
		if ( isset( $_POST['tvg_api_demo'] ) ) {
			$this->update_or_insert_option( 'tvg_api_demo', sanitize_text_field( wp_unslash( $_POST['tvg_api_demo'] ) ) );
		}

		if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
			wp_safe_redirect( wp_unslash( $_SERVER['HTTP_REFERER'] ) . '&success=1' );
		} else {
			wp_safe_redirect( '&success=1' );
		}
	}
	/**
	 * Admin Notice message
	 *
	 * @param string $message Message to display.
	 */
	public function admin_notice( $message ) { ?>
		<div class="notice notice-success is-dismissible">
			<p><?php echo esc_html( $message ); ?></p>
		</div>
		<?php
	}
	/**
	 * Delete transient
	 */
	public function tvg_delete_transient() {
		delete_transient( 'tvg_shipping_options' );
		if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
			wp_safe_redirect( wp_unslash( $_SERVER['HTTP_REFERER'] ) . '&success=1' );
		} else {
			wp_safe_redirect( '&success=1' );
		}
	}
}
