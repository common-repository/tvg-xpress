<?php /**
	   * The public function of the plugin
	   *
	   * @link       smartmedia.is
	   * @since      1.0.0
	   * @package    Tvgexpress
	   * @subpackage Tvgexpress/admin
	   */
class Tvgexpress_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/tvgexpress-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		 wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/tvgexpress-public.js', array( 'jquery' ), $this->version, false );
	}

	public function add_shipping( $methods ) {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tvgexpress-methods.php';

		/*
		 * Commented out, old code
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/methods/class-tvgexpress-options.php';
		$options = (new Tvg_Options())->getOptions();
		foreach($options as $option){
			$method = new Tvgexpress_Methods();
			$methods['tvg_'.$option['title']] = $method;
			break;
		}
		//$tvgMethod = new Tvgexpress_Methods(); !
		//$tvgMethod->title = "overWrite"; !
		//$methods['your_shipping_method'] = $tvgMethod; !
		*/
		$methods['tvg_shipping'] = 'Tvgexpress_Methods';
		return $methods;
	}

	public function get_label( $label, $method ) {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/methods/class-tvgexpress-options.php';

		if ( 'DROPP' === $method->id ) {
			$tvg_options = new Tvgexpress_Options();
			$boxes       = $tvg_options->get_postboxes();
			$options     = '';
			foreach ( $boxes as $box ) {
				if ( true === $box['active'] ) {
					$open = $box['open']['weekdays'];
					if ( ( gmdate( 'N', strtotime( time() ) ) >= 6 ) ) {
						$open = $box['open']['weekend'];
					}
					$options .= sprintf( '<option value="%s">%s</option>', $box['postboxId'], $box['name'] . ' (' . $open . ')' );
				}
			}
			$select = ' <div>
			<select name="tvg_postbox_id" class="tvg_postbox_select">
			<option value="">Velja Stað</option>
			' . $options . '
			</select></div>';
			$label .= $select;
		}

		return $label;
	}



	public function save_postbox( $order_id ) {
		// Verify the nonce !
		$nonce = isset( $_REQUEST['woocommerce-process-checkout-nonce'] ) ? sanitize_key( $_REQUEST['woocommerce-process-checkout-nonce'] ) : '';
		$nonce_wpnonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_key( $_REQUEST['_wpnonce'] ) : '';
		$nonce_value    = wc_get_var( $nonce, wc_get_var( $nonce_wpnonce, '' ) );

		if ( empty( $nonce_value ) || ! wp_verify_nonce( $nonce_value, 'woocommerce-process_checkout' ) ) {
			// nonce failed, do not save postbox information.
			$donothing = true;
		} else {

			if ( ! empty( $_POST['tvg_postbox_id'] ) ) {
				update_post_meta( $order_id, 'tvg_postbox_id', sanitize_text_field( wp_unslash( $_POST['tvg_postbox_id'] ) ) );
			}
			if ( isset( $_POST['shipping_method'] ) && ! empty( $_POST['shipping_method'] ) ) {
				if ( isset( $_POST['shipping_method'][0] ) ) {
					update_post_meta( $order_id, 'tvg_method_id', sanitize_text_field( wp_unslash( $_POST['shipping_method'][0] ) ) );
				}
			}
		}
	}

	public function action_checkout_create_order_shipping_item( $item, $package_key, $package, $order ) {
		// Verify the nonce !
		$nonce = isset( $_REQUEST['woocommerce-process-checkout-nonce'] ) ? sanitize_key( $_REQUEST['woocommerce-process-checkout-nonce'] ) : '';
		$nonce_wpnonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_key( $_REQUEST['_wpnonce'] ) : '';
		$nonce_value    = wc_get_var( $nonce, wc_get_var( $nonce_wpnonce, '' ) );

		if ( empty( $nonce_value ) || ! wp_verify_nonce( $nonce_value, 'woocommerce-process_checkout' ) ) {
			// nonce failed, do not save postbox information.
			$donothing = true;
		} else {

			if ( isset( $_POST['shipping_method'] ) ) {
				if ( isset( $_POST['shipping_method'][0] ) ) {
					if ( 'DROPP' === $_POST['shipping_method'][0] ) {
						if ( ! empty( $_POST['tvg_postbox_id'] ) ) {

							$postbox_id = sanitize_text_field( wp_unslash( $_POST['tvg_postbox_id'] ) );

							$option_model = new Tvgexpress_Options();
							$postboxes = $option_model->get_postboxes();

							foreach ( $postboxes as $postbox ) {
								if ( $postbox['postboxId'] === $postbox_id ) {
									$item->update_meta_data( 'tvg_box', $postbox['name'] );
								}
							}
						}
					}
				}
			}
		}
	}

	public function tvg_change_order_item_meta_title( $key, $meta, $item ) {

		if ( 'tvg_box' === $meta->key ) {
			$key = 'Staður'; }

		return $key;
	}

	public function get_sender() {
		$prefix = 'tvg_sender_';
		return array(
			'Address'                => array(
				'AddressName' => get_option( $prefix . 'address' ),
				'City'        => get_option( $prefix . 'city' ),
				'Contact'     => get_option( $prefix . 'contact' ),
				'Country'     => get_option( $prefix . 'country_code' ),
				'Email'       => get_option( $prefix . 'email' ),
				'PhoneNumber' => get_option( $prefix . 'phone' ),
				'PostalCode'  => get_option( $prefix . 'postcode' ),
			),
			'NationalSecurityNumber' => get_option( $prefix . 'kt' ),
			'PartyName'              => get_option( $prefix . 'name' ),
		);
	}

	public function get_recipient( $order ) {
		$data     = $order->get_data();
		$customer = $data['shipping'];
		$billing  = $data['billing'];
		$kt_key   = get_option( 'tvg_kt' );
		$kt       = '0000000000';
		if ( ! empty( $kt_key ) ) {
			$kt = $order->get_meta( $kt_key );
		}

		$phone = '';
		if ( ! empty( $billing['phone'] ) ) {
			$phone = preg_replace( '/\D/', '', $billing['phone'] );
			$phone = substr( $phone, -7 );
		}

		return array(
			'Address'                => array(
				'AddressName' => $customer['address_1'],
				'City'        => $customer['city'],
				'Contact'     => $customer['first_name'] . ' ' . $customer['last_name'],
				'Country'     => $customer['country'],
				'Email'       => ! empty( $billing['email'] ) ? $billing['email'] : '',
				'PhoneNumber' => $phone,
				'PostalCode'  => $customer['postcode'],
			),
			'NationalSecurityNumber' => $kt,
			'PartyName'              => $customer['first_name'] . ' ' . $customer['last_name'],
		);
	}

	public function create_shipping_action() {
		$id = filter_input( INPUT_GET, 'order_id', FILTER_SANITIZE_NUMBER_INT );
		$this->create_shipping( $id );
		if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
			wp_safe_redirect( wp_unslash( $_SERVER['HTTP_REFERER'] ) );
		} else {
			wp_safe_redirect( '/' );
		}
	}

	public function create_pdf_action() {
		$order_id = filter_input( INPUT_GET, 'order_id', FILTER_SANITIZE_NUMBER_INT );

		$order       = wc_get_order( $order_id );
		$shipment_id = $order->get_meta( 'shipmentID' );

		if ( ! empty( $shipment_id ) ) {
			$service = Tvgexpress_Service_Factory::loadDefault();
			$pdf     = $service->get_labels( $shipment_id, array( 'labelType' => 'PDF' ) );

			if ( ! empty( $pdf->response->label ) ) {
				$this->render_file( $pdf->response->label );
			}
		}
	}

	public function render_file( $data ) {
		$dirs = wp_upload_dir();
		$tmp = '/eimskip/exports/';
		$filename = 'eimskip_' . uniqid() . '.pdf';
		$path = $dirs['basedir'] . $tmp . $filename;
		$dir = $dirs['basedir'] . $tmp;
		$public_path = $dirs['baseurl'] . $tmp . $filename;
		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
			chmod( $dir, 0755 );
		}
		/*Delete older files*/
		foreach ( glob( $dir . '*' ) as $file ) {
			if ( time() - filectime( $file ) > 600 ) {
				unlink( $file );
			}
		}
		file_put_contents( $path, base64_decode( $data ) );
		wp_safe_redirect( $public_path );
		die();
	}

	public function create_shipping( $order_id ) {
		$shipment_id = get_post_meta( $order_id, 'shipmentID', true );
		if ( ! empty( $shipment_id ) ) {
			$this->log( 'Shipment already created: ' . $shipment_id );
			return;
		}

		$order = wc_get_order( $order_id );

		$weight = 0;
		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();
			if ( ! empty( $product->get_weight() ) ) {
				$weight += (float) $product->get_weight() * $item['qty'];
			}
		}

		$options = new Tvgexpress_Options();
		$weight  = $options->convert_weight( $weight );
		// Iterating through order shipping items !
		foreach ( $order->get_items( 'shipping' ) as $item_id => $shipping_item_obj ) {

			// Get the data in an unprotected array !
			// $shipping_data = $shipping_item_obj->get_data(); !

			$method_id = $order->get_meta( 'tvg_method_id' );
			$option    = $options->get_by_id( $method_id );
			if ( $option ) {

				$receiver = $this->get_recipient( $order );

				if ( $options->is_postbox( $method_id ) ) {
					$delivery = array( 'Type' => $order->get_meta( 'tvg_postbox_id' ) );
				} else {
					$code = $option['code'];
					if ( isset( $option['postcodePrice'] ) ) {
						foreach ( $option['postcodePrice'] as $postcode_price ) {
							if ( in_array( $receiver['Address']['PostalCode'], $postcode_price['postcodes'] ) ) {
								$code = $postcode_price['code'];
							}
						}
					}
					$delivery = array( 'Type' => $code );
				}
               $service = Tvgexpress_Service_Factory::loadDefault();
				$params  = array(
					'request' => array(
						'shipment' => array(
							'Sender'   => $this->get_sender(),
							'Receiver' => $receiver,
							'Delivery' => $delivery,
							'Parcel'   => array(
								array(
									'Description' => 'Pöntun #' . $order_id,
									'Weight'      => (string) $weight,
								),
							),
						),
					),
				);

				$response = $service->create_shipment( $params );
				if ( ! empty( $response->response ) ) {
					update_post_meta( $order_id, 'shipmentID', sanitize_text_field( $response->response->shipmentNumber ) );
					delete_post_meta( $order_id, 'tvg_error' );

					$body = array(
						'shop'           => home_url(),
						'orderId'        => $order_id,
						'trackingNumber' => $response->response->shipmentNumber,
						'delivery'       => $params,
					);

					$service->track_package( $body );
					$this->send_to_printnode( $response->response->shipmentNumber );
				} else {
					delete_post_meta( $order_id, 'tvg_error' );
					$response = (array) $response;
					if ( ! empty( $response['$error'] ) ) {
						add_post_meta( $order_id, 'tvg_error', sanitize_text_field( $response['$error'] ) );
					} elseif ( ! empty( $response['status']->message ) ) {
						add_post_meta( $order_id, 'tvg_error', sanitize_text_field( $response['status']->message ) );
					} else {

						add_post_meta( $order_id, 'tvg_error', 'Unknown error. please try again ' . wp_json_encode( $response ) );
					}
				}
			}
		}
	}

	public function tvg_sort_shipping_rates( $rates ) {
		if ( ! $rates ) {
			return $rates;
		}

		$tvg_settings = get_option( 'woocommerce_tvg_express_settings' );
		if ( isset( $tvg_settings['tvg_set_on_top'] ) && 'yes' === $tvg_settings['tvg_set_on_top'] ) {
			foreach ( $rates as $key => $rate ) {
				if ( 'tvg_express' === $rate->method_id ) {
					$temp = array( $key => $rates[ $key ] );
					unset( $rates[ $key ] );
					$rates = $temp + $rates;
				}
			}
		}

		return $rates;
	}

	public function send_to_printnode( $shipment_number ) {
		$print_node_api_key = $this->decrypt( get_option( 'tvg_printnode_api_key' ) );
		if ( ! empty( $print_node_api_key ) ) {
            $print_node = new Tvgexpress_PrintNode( $print_node_api_key );
			$service    = Tvgexpress_Service_Factory::loadDefault();
			$pdf        = $service->get_labels( $shipment_number, array( 'labelType' => 'PDF' ) );
			$printer    = get_option( 'tvg_printer' )[0];
			return $print_node->set_printer( $printer )->print_pdf_base64( $pdf->response->label );
		}
	}
	public function add_actions_button( $actions, $order ) {
		// var_dump($actions); die(); !
		// Display the button for all orders that have a 'processing' status !
		$order_id    = $order->get_id();
		$shipment_id = get_post_meta( $order_id, 'shipmentID', true );

		if ( ! empty( $shipment_id ) ) {
			$create_url                = add_query_arg(
				array(
					'action'   => 'tvgexpress_create_pdf_action',
					'order_id' => $order_id,
				),
				admin_url( 'admin-post.php' )
			);
			$actions['tvg_pdf_create'] = array(
				'url'    => esc_url( $create_url ),
				'name'   => __( 'Print ticket', 'tvgxpress' ),
				'action' => 'tvg_pdf',
			);
		} else {
			$create_url            = add_query_arg(
				array(
					'action'   => 'tvgexpress_create_action',
					'order_id' => $order_id,
				),
				admin_url( 'admin-post.php' )
			);
			$actions['tvg_create'] = array(
				'url'    => esc_url( $create_url ),
				'name'   => __( 'Create EIMSKIP shipping', 'tvgxpress' ),
				'action' => 'tvg_create',
			);
		}

		return $actions;
	}

	public function action_after_shipping_rate( $method, $index ) {
		if ( is_cart() ) {
			return; // Exit on cart page !
		}
		$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
		if ( $chosen_methods[0] === $method->id && is_array( $method->meta_data ) && key_exists( 'desc', $method->meta_data ) ) {
			echo '<p><i>' . esc_html( $method->meta_data['desc'] ) . '</i></p>';
		}

	}

	public function add_actions_button_css() {
		echo '<style>.wc-action-button-tvg_pdf::after { font-family: tvg-icon !important; content: "\e954" !important; }</style>';
		echo '<style>.wc-action-button-tvg_create::after { font-family: woocommerce !important; content: "\e019" !important; }</style>';
	}

	protected function get_default_key(): string {
		return 'das-ist-kein-geheimer-schluessel';
	}

	protected function get_default_salt(): string {
		return 'das-ist-kein-geheimes-salz';
	}

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

	protected function log( $message ) {
		if ( empty( $this->log ) ) {
			$this->log = wc_get_logger();
		}

		$this->log->debug( $message, array( 'source' => 'tvg-xpress' ) );

	}

	public function validate_postbox_select() {
		// Verify the nonce !
		$nonce = isset( $_REQUEST['woocommerce-process-checkout-nonce'] ) ? sanitize_key( $_REQUEST['woocommerce-process-checkout-nonce'] ) : '';
		$nonce_wpnonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_key( $_REQUEST['_wpnonce'] ) : '';
		$nonce_value    = wc_get_var( $nonce, wc_get_var( $nonce_wpnonce, '' ) );

		if ( empty( $nonce_value ) || ! wp_verify_nonce( $nonce_value, 'woocommerce-process_checkout' ) ) {
			// nonce failed, do not save postbox information.
			$donothing = true;
		} else {
			if ( ! empty( $_POST['shipping_method'] ) && in_array( 'DROPP', $_POST['shipping_method'] ) ) {
				if ( isset( $_POST['tvg_postbox_id'] ) && empty( $_POST['tvg_postbox_id'] ) ) {
					wc_add_notice( 'Vinsamlegast veldu afhendingarstað', 'error' );
				}
			}
		}
	}
}
