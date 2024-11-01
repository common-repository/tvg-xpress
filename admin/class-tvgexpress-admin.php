<?php /**
	   * The admin-specific functionality of the plugin.
	   *
	   * This class handles all the admin-related functionality for the Eimskip plugin.
	   * It includes settings, menus, and other admin-specific features.
	   *
	   * @since      1.0.0
	   * @package    Tvgexpress
	   * @subpackage Tvgexpress/admin
	   */
class Tvgexpress_Admin {
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
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/tvgexpress-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/tvgexpress-admin.js', array( 'jquery' ), $this->version, false );
		wp_localize_script(
			$this->plugin_name,
			'tvg_ajax_change_shipment',
			array(
				'ajax_url'                       => admin_url( 'admin-ajax.php' ),
				'tvg_ajax_change_shipment_nonce' => wp_create_nonce( 'tvg_ajax_change_shipment' ),
			)
		);

		wp_localize_script(
			$this->plugin_name,
			'tvgexpress_printnode_action',
			array(
				'ajax_url'                          => admin_url( 'admin-ajax.php' ),
				'tvgexpress_printnode_action_nonce' => wp_create_nonce( 'tvgexpress_printnode_action' ),
			)
		);

	}
	/**
	 * Print shipping information
	 *
	 * @since    1.0.0
	 */
	public function print_shipping() {

		// Verify the nonce !
		$nonce = isset( $_POST['nonce'] ) ? sanitize_key( $_POST['nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'tvgexpress_printnode_action' ) ) {
			wp_die( 'Invalid nonce verification' );
		}

		if ( isset( $_POST['shipment_id'] ) ) {
			$shipment_number = sanitize_text_field( wp_unslash( $_POST['shipment_id'] ) );
		}
		if ( isset( $_POST['printer'] ) ) {
			$printer = sanitize_text_field( wp_unslash( $_POST['printer'] ) );
		}

		if ( ! empty( $shipment_number ) && ! empty( $printer ) ) {
			$print_node_api_key = $this->decrypt( get_option( 'tvg_printnode_api_key' ) );
			if ( ! empty( $print_node_api_key ) ) {
				$print_node = new Tvgexpress_PrintNode( $print_node_api_key );
				$service    = Tvgexpress_Service_Factory::loadDefault();
				$pdf        = $service->get_labels( $shipment_number, array( 'labelType' => 'PDF' ) );
				$print_node->set_printer( $printer )->print_pdf_base64( $pdf->response->label );

				echo wp_json_encode(
					array(
						'success'        => 1,
						'shipmentNumber' => esc_html( $shipment_number ),
						'printer'        => esc_html( $printer ),
					)
				);
				die();
			}
		}

		echo wp_json_encode( array( 'success' => 0 ) );
		die();
	}
	/**
	 * Display Meta box on order
	 *
	 * @since    1.0.0
	 */
	public function order_metabox() {

		add_meta_box(
			'order_shipment',
			__( 'EIMSKIP', 'tvgxpress' ),
			array( $this, 'display_metabox' ),
			'shop_order',
			'side',
			'default'
		);
	}
	/**
	 * Display correct metaBox
	 *
	 * @since    1.0.0
	 * @param array $post Post data.
	 * @return void
	 */
	public function display_metabox( $post ) {
		$order_id = $post->ID;
		$this->tvg_metbox_order_id( $order_id );
	}
	/**
	 * Display information on order
	 *
	 * @since    1.0.0
	 * @param int $order_id The Order Id.
	 * @return void
	 */
	protected function tvg_metbox_order_id( $order_id ) {
		$order         = wc_get_order( $order_id );
		$shipment_id   = $order->get_meta( 'shipmentID' );
		$tvg_method_id = $order->get_meta( 'tvg_method_id' );

		$service = Tvgexpress_Service_Factory::loadDefault();
		$options = new Tvgexpress_Options();
		if ( empty( $tvg_method_id ) || ! $options->is_valid_id( $tvg_method_id ) ) {
			echo "<p class='notice notice-error'>This order does not have shipping method from TVG</p>";

			$toselectbox_escaped = $options->to_selectbox();
			$allowed_html        = $options->allowedhtml();
			echo wp_kses( $toselectbox_escaped, $allowed_html );

			$create_url = add_query_arg(
				array(
					'action'   => 'tvgexpress_create_action',
					'order_id' => $order_id,
				),
				admin_url( 'admin-post.php' )
			);
			echo '<a data-order="' . esc_html( $order_id ) . '" class="button button-primary update_tvg_shipment" style="margin:0 auto; width:100%; text-align:center;" href="' . esc_url( $create_url ) . '">' . esc_html( __( 'Change Shipment', 'tvgxpress' ) ) . '</a>';

		} elseif ( ! empty( $order->get_meta( 'shipmentID' ) ) ) {
			echo '<strong>' . esc_html( __( 'Shipment number', 'tvgxpress' ) ) . ':</strong> #' . esc_html( $shipment_id ) . '<br/>';
			$create_url = add_query_arg(
				array(
					'action'   => 'tvgexpress_create_pdf_action',
					'order_id' => $order_id,
				),
				admin_url( 'admin-post.php' )
			);
			echo '<strong>' . esc_html( __( 'Pdf', 'tvgxpress' ) ) . ':</strong> <a href="' . esc_url( $create_url ) . '" target=_blank>' . esc_html( __( 'Open ticket', 'tvgxpress' ) ) . '</a><br/>';

			$print_node_api_key = $this->decrypt( get_option( 'tvg_printnode_api_key' ) );
			if ( ! empty( $print_node_api_key ) && false !== $print_node_api_key ) {
				$print_node = new Tvgexpress_PrintNode( $print_node_api_key );

				$printers = $print_node->get_printers();
				if ( ! empty( $printers ) && is_array( $printers ) ) {
					echo '<br/>';
					echo '<select name="printer" class="order-printer-tvg-select">';
					foreach ( $printers as $printer ) {
						echo '<option value="' . esc_html( $printer->id ) . '">' . esc_html( $printer->name ) . '</option>';
					}
					echo '</select>';
					echo '<br/><br/>';
					$print_node_url = wp_nonce_url(
						add_query_arg(
							array(
								'action'      => 'tvgexpress_printnode_action',
								'shipment_id' => $shipment_id,
							),
							admin_url( 'admin-post.php' )
						),
						'tvgexpress_printnode_action_nonce'
					);
					echo '<a class="button button-primary print-tvg-shipment" data-shipmentid="' . esc_html( $shipment_id ) . '" href="' . esc_url( $print_node_url ) . '" target=_blank>Prenta miða</a>';
					echo '<span class="printer-response"></span>';
				}
			}

			$shipmentstatushtml_safe = ( $service->get_shipment_status_html( $shipment_id ) );
			$allowed_html            = $service->allowedhtml();
			echo wp_kses( $shipmentstatushtml_safe, $allowed_html );

		} else {
			echo '<h3>' . esc_html( __( 'Stofna sendingu' ) ) . '</h3>';
			$create_url = add_query_arg(
				array(
					'action'   => 'tvgexpress_create_action',
					'order_id' => $order_id,
				),
				admin_url( 'admin-post.php' )
			);
			echo '<a class="button button-primary" style="width:100%; text-align:center;" href="' . esc_url( $create_url ) . '">' . esc_html( __( 'Stofna Sendingu', 'tvgxpress' ) ) . '</a>';
			echo '<br/><hr style="margin-top:20px"/>';
			echo '<h3>' . esc_html( __( 'Breyta sendingu' ) ) . '</h3>';
			$toselectbox_escaped = $options->to_selectbox();
			$allowed_html        = $options->allowedhtml();
			echo wp_kses( $toselectbox_escaped, $allowed_html );

			$create_url = add_query_arg(
				array(
					'action'   => 'tvgexpress_create_action',
					'order_id' => $order_id,
				),
				admin_url( 'admin-post.php' )
			);
			echo '<a data-order="' . esc_html( $order_id ) . '" class="button button-primary update_tvg_shipment" style="margin:0 auto; width:100%; text-align:center;" href="' . esc_url( $create_url ) . '">' . esc_html( __( 'Breyta sendingu', 'tvgxpress' ) ) . '</a>';
		}
		$error = get_post_meta( $order_id, 'tvg_error' );
		if ( ! empty( $error ) ) {
			if ( is_array( $error ) ) {
				print '<p style="padding:15px; background:#ff0000;color:white; font-weight:bold; word-wrap:break-word">' . esc_html( $error[0] ) . '</p>';
			}
		}
	}
	/**
	 * Change Shipment data
	 *
	 * @since    1.0.0
	 */
	public function change_shipment() {

		// Verify the nonce !
		$nonce = isset( $_POST['nonce'] ) ? sanitize_key( $_POST['nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'tvg_ajax_change_shipment' ) ) {
			wp_die( 'Invalid nonce verification' );
		}

		if ( isset( $_POST['tvg_method'] ) ) {
			$method = sanitize_text_field( wp_unslash( $_POST['tvg_method'] ) );
		}
		if ( isset( $_POST['tvg_box'] ) ) {
			$box = sanitize_text_field( wp_unslash( $_POST['tvg_box'] ) );
		}
		if ( isset( $_POST['order_id'] ) ) {
			$order_id = sanitize_text_field( wp_unslash( $_POST['order_id'] ) );
		}

		if ( ! empty( $method ) && ! empty( $order_id ) ) {
			$order = wc_get_order( $order_id );
			if ( ! empty( $order ) ) {
				$order->update_meta_data( 'tvg_method_id', $method );
				if ( 'DROPP' === $method ) {
					$order->update_meta_data( 'tvg_postbox_id', $box );
				}
				$order->save();
				ob_start();
				$this->tvg_metbox_order_id( $order_id );
				$html = ob_get_contents();
				ob_end_clean();
				echo wp_json_encode(
					array(
						'success' => 1,
						'html'    => $html,
					)
				);
				die();
			}
		}
		echo wp_json_encode( array( 'success' => 0 ) );
		die();
	}
	/**
	 * Custom fields
	 *
	 * @since    1.0.0
	 */
	public function tvg_shipping_custom_field() {

		global $woocommerce, $post;

		echo '<div class="options_group">';

		woocommerce_wp_checkbox(
			array(
				'id'          => '_tvg_skip_free_shipping',
				'label'       => 'Óvirkja Fría Sendingu EIMSKIPS',
				'class'       => '',
				'desc_tip'    => false,
				'description' => '',
			)
		);

		echo '</div>';

		echo '<div class="options_group">';

		woocommerce_wp_checkbox(
			array(
				'id'          => '_tvg_skip_shipping',
				'label'       => 'Óvirkja EIMSKIP sendingu',
				'class'       => '',
				'desc_tip'    => false,
				'description' => '',
			)
		);

		echo '</div>';
	}
	/**
	 * Custom fields save, $post_id is the post to update
	 *
	 * @param int $post_id The Post Id.
	 */
	public function tvg_shipping_custom_field_save( $post_id ) {

		// Verify the nonce !
		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_key( $_POST['_wpnonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'update-post_' . $post_id ) ) {
			// Nonce verification failed, handle the error or display an error message.
			wp_die( 'Invalid nonce verification' );
		}

		// Checkbox !
		$woocommerce_checkbox = isset( $_POST['_tvg_skip_free_shipping'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_tvg_skip_free_shipping', $woocommerce_checkbox );

		$woocommerce_checkbox = isset( $_POST['_tvg_skip_shipping'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_tvg_skip_shipping', $woocommerce_checkbox );
	}
	/**
	 * Get default key
	 */
	protected function get_default_key(): string {
		return 'das-ist-kein-geheimer-schluessel';
	}
	/**
	 * Get default salt
	 */
	protected function get_default_salt(): string {
		return 'das-ist-kein-geheimes-salz';
	}
	/**
	 * Encrypt data
	 *
	 * @param string $value The Value.
	 * @return string
	 */
	public function encrypt( $value ) {
		if ( ! extension_loaded( 'openssl' ) ) {
			return $value;
		}

		$key  = $this->get_default_key();
		$salt = $this->get_default_salt();

		$method = 'aes-256-ctr';
		$ivlen  = openssl_cipher_iv_length( $method );
		$iv     = openssl_random_pseudo_bytes( $ivlen );

		$raw_value = openssl_encrypt( $value . $salt, $method, $key, 0, $iv );
		if ( ! $raw_value ) {
			return false;
		}

		return base64_encode( $iv . $raw_value );
	}
	/**
	 * Decrypt data
	 *
	 * @param string $raw_value The raw value.
	 * @return bool True, false
	 */
	public function decrypt( $raw_value ) {
		if ( ! extension_loaded( 'openssl' ) ) {
			return $raw_value;
		}

		$key  = $this->get_default_key();
		$salt = $this->get_default_salt();

		$raw_value = base64_decode( $raw_value, true );

		$method = 'aes-256-ctr';
		$ivlen  = openssl_cipher_iv_length( $method );
		$iv     = substr( $raw_value, 0, $ivlen );

		$raw_value = substr( $raw_value, $ivlen );

		$value = openssl_decrypt( $raw_value, $method, $key, 0, $iv );
		if ( ! $value || substr( $value, - strlen( $salt ) ) !== $salt ) {
			return false;
		}

		return substr( $value, 0, - strlen( $salt ) );
	}
	/**
	 * Update or insert an option in the WordPress database.
	 *
	 * @param string $key   The option key.
	 * @param mixed  $value The option value.
	 * @return bool True on success, false on failure.
	 */
	public function update_or_insert_option( $key, $value ) {
		$key_exists = get_option( $key );
		if ( false !== $key_exists ) {
			return update_option( $key, $value );
		} else {
			return add_option( $key, $value );
		}
	}
	/**
	 * Ddisplay order shipping status
	 *
	 * @param order_id $order_id id of order.
	 */
	public function order_shipping_status( $order_id ) {

		$order         = wc_get_order( $order_id );
		$shipment_id   = $order->get_meta( 'shipmentID' );
		$tvg_method_id = $order->get_meta( 'tvg_method_id' );

		if ( ! empty( $shipment_id ) && ! empty( $tvg_method_id ) ) {
			$service = Tvgexpress_Service_Factory::loadDefault();

			$shipment = $service->get_shipment( $shipment_id );

			echo '<h2>TVG - Staða Sendingar</h2>';
			echo '<strong>Sendingarnúmer:</strong> #' . esc_html( $shipment_id ) . '<br/>';
			echo '<strong>Afhendingarstaður:</strong> ' . esc_html( $shipment->response->shipment->Receiver->Address->AddressName ) . '<br/><br/>';
			echo wp_kses( $service->get_shipment_status_html( $shipment_id ) );
		}

	}
}
