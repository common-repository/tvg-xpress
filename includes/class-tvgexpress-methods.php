<?php /**
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
class Tvgexpress_Methods extends WC_Shipping_Method {

	/**
	 * Shipping option.
	 *
	 * @var float
	 */
	protected $shipping_option;

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
		 $this->id                = 'tvg_express'; // Id for your shipping method. Should be uunique.
		$this->method_title       = __( 'EIMSKIP', 'tvgxpress' );  // Title shown in admin.
		$this->method_description = __( 'EIMSKIP is an express import/export service that delivers shipments in 24–72 hours.<br/>All handling of these shipments are high priority, and therefore, the delivery time is much shorter than normal air freight.<br />EIMSKIP is based on "Door-to-Door" delivery.', 'tvgxpress' ); // Description shown in admin.

		$this->enabled = $this->get_option( 'enabled', 'no' );

		if ( empty( get_option( 'tvg_sender_kt' ) ) ) {
			$this->enabled = 'no';
		}
		$this->title = 'EIMSKIP'; // This can be added as an setting but for this example its forced.
		$this->init();

	}

	/**
	 * Init your settings
	 *
	 * @access public
	 * @return void
	 */
	public function init() {
		// Load the settings API.
		$this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings.
		$this->init_settings(); // This is part of the settings API. Loads settings you previously init.

		// Save settings in admin if you have any defined.
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		// add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_postis_rates' ) ); !

		// add_filter( 'woocommerce_cart_shipping_method_full_label', $this, 'getlabel' ); !
	}

	/**
	 * Initate form fields
	 *
	 * Set all form fields for user
	 *
	 * @since    1.0.0
	 */
	public function init_form_fields() {
		$missing_info = empty( get_option( 'tvg_sender_kt' ) ) ? "Vantar kennitölu á sendanda. <a href='/wp-admin/admin.php?page=tvg-express'>Fylltu út hér</a>" : '';

		$this->form_fields = array(
			'enabled'         => array(
				'title'       => __( 'Enable/Disable', 'tvgxpress' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable', 'tvgxpress' ),
				'default'     => 'no',
				'description' => $missing_info,
				'disabled'    => ! empty( $missing_info ),
			),
			'tvg_use_woo_tax' => array(
				'title'       => __( 'Use Woocommerce tax settings', 'tvgxpress' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable', 'tvgxpress' ),
				'default'     => 'no',
				'description' => __( 'If enabled EIMSKIP uses the tax settings you set in woocommerce.', 'tvgxpress' ),
			),
			'tvg_set_on_top'  => array(
				'title'       => __( 'Set EIMSKIP shipping on top', 'tvgxpress' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable', 'tvgxpress' ),
				'default'     => 'no',
				'description' => __( 'If enabled the EIMSKIP shipping will display on top in cart and checkout.', 'tvgxpress' ),
			),
		);

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/methods/class-tvgexpress-options.php';
		$options_model = new Tvgexpress_Options();

		$options = $options_model->get_options();

		$shipping_classes = get_terms(
			array(
				'taxonomy'   => 'product_shipping_class',
				'hide_empty' => false,
			)
		);

		foreach ( $options as $option ) {

			$title = ( 'DROPP' === $option['id'] ) ? 'Afhendingarstaður' : $option['title'];

			$this->form_fields[ "{$option['id']}_enabled" ] = array(
				'title'   => $title,
				'type'    => 'checkbox',
				'label'   => __( 'Enable', 'tvgxpress' ),
				'class'   => 'tvg_enable_box',
				'default' => 'yes',
			);
			$this->form_fields[ "{$option['id']}_title" ]   = array(
				'title'       => __( 'Method Title', 'tvgxpress' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'tvgxpress' ),
				'default'     => '', // $option['title'], !
				'placeholder' => $title,
				'desc_tip'    => false,
			);
			$this->form_fields[ "{$option['id']}_desc" ]    = array(
				'title'       => __( 'Method Description', 'tvgxpress' ),
				'css'         => 'width:400px',
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'tvgxpress' ),
				'default'     => '', // $option['title'], !
				'placeholder' => $option['desc'],
				'desc_tip'    => false,
			);

			$this->form_fields[ "{$option['id']}_free_shipping" ] = array(
				'title'       => __( 'Free shipping', 'tvgxpress' ),
				'type'        => 'text',
				'description' => __( 'Free shipping for orders over x amount', 'tvgxpress' ),
				'placeholder' => '',
				'default'     => '',
				'desc_tip'    => false,
			);

			$this->form_fields[ "{$option['id']}_price_type" ] = array(
				'title'       => __( 'Price type', 'tvgxpress' ),
				'type'        => 'select',
				'options'     => array(
					0 => __( 'Use EIMSKIP price list', 'tvgxpress' ),
					1 => __( 'Use custom price', 'tvgxpress' ),
				),
				'description' => __( 'custom price is the price here below and EIMSKIP price list is the official price list from EIMSKIP in Iceland', 'tvgxpress' ),
				'default'     => $option['title'],
				'desc_tip'    => false,
			);

			foreach ( $option['weight'] as $key => $weight ) {
				$title = $weight['min'] . ' - ' . $weight['max'] . ' KG';
				if ( ! empty( $option['noMaxWeight'] ) && $weight['max'] === $option['maxWeight'] ) {
					$title = $weight['min'] . ' KG og yfir';
				}
				$this->form_fields[ 'weight_' . $weight['id'] ] = array(
					'title'       => $title,
					'type'        => 'text',
					'description' => __( 'Custom price for', 'tvgxpress' ) . ' ' . $title,
					'placeholder' => $weight['price'],
					'default'     => '',
					'desc_tip'    => false,
				);
			}

			if ( key_exists( 'useDimensionalWeight', $option ) && $option['useDimensionalWeight'] ) {
				$this->form_fields[ $option['id'] . '_skip_dimension_price' ] = array(
					'title'   => 'Sleppa rúmmálsreiknireglu',
					'type'    => 'checkbox',
					'label'   => 'EIMSKIP bætir gjaldi ofan á sendingarverð út frá reiknireglu tengda við rúmmál. Veldu þetta ef þú vilt sleppa því.',
					'class'   => '',
					'default' => '',
				);
			}

			if ( ! empty( $shipping_classes ) && ! is_wp_error( $shipping_classes ) ) {
				foreach ( $shipping_classes as $shipping_class ) {
					$this->form_fields[ $option['id'] . '_shipping_class_' . $shipping_class->slug ] = array(
						'title'       => $shipping_class->name,
						'type'        => 'number',
						'description' => 'Verð á sendingu á vörum í sendingarflokknum <strong>' . $shipping_class->name . '</strong> (shipping class)',
						'placeholder' => '',
						'default'     => '',
						'desc_tip'    => false,
					);

					$this->form_fields[ $option['id'] . '_sc_skip_fs_' . $shipping_class->slug ] = array(
						'title'       => 'Sleppa "Free shipping" fyrir ' . $shipping_class->name,
						'type'        => 'checkbox',
						'label'       => 'Ef þetta er valið mun verð á ' . $shipping_class->name . ' vera óháð frírri sendingu',
						'description' => '',
						'placeholder' => '',
						'default'     => '',
						'desc_tip'    => false,
					);

					$this->form_fields[ $option['id'] . '_sc_qty_' . $shipping_class->slug ] = array(
						'title'       => 'Margfalda með fjölda',
						'type'        => 'checkbox',
						'label'       => 'Ef þetta er valið mun verð fyrir <strong>' . $shipping_class->name . '</strong> margfaldast eftir fjölda vara í þeim sendingarflokk (shipping class)',
						'description' => '',
						'placeholder' => '',
						'default'     => '',
						'desc_tip'    => false,
					);

					$this->form_fields[ $option['id'] . '_sc_calc_type_' . $shipping_class->slug ] = array(
						'title'       => 'Reikniregla fyrir ' . $shipping_class->name,
						'type'        => 'select',
						'description' => '',
						'placeholder' => '',
						'default'     => '',
						'desc_tip'    => false,
						'options'     => array(
							0 => 'Bæta verði á "' . $shipping_class->name . '" ofan á verð sendingar ',
							1 => 'Dýrasta sending er valin til að hafa sem sendingarverð',
						),
					);
				}
			}
		}
	}

	/**
	 * Calculate_shipping function.
	 *
	 * @access public
	 * @param mixed $package Array with information.
	 * @return void
	 */
	public function calculate_shipping( $package = array() ) {
		if ( 'no' === $this->enabled ) {
			return;}
		global $woocommerce;
		$weight                   = 0;
		$product_free_shipping    = true;
		$product_shipping_classes = array();

		$packer = new \Cloudstek\PhpLaff\Packer();
		$boxes  = array();

		foreach ( $package['contents'] as $c => $product ) {

			$skip_shipping = get_post_meta( $product['product_id'], '_tvg_skip_shipping', true );

			if ( 'yes' === $skip_shipping ) {
				return;
			}

			$product_shipping_class = $product['data']->get_shipping_class();
			if ( ! empty( $product_shipping_class ) ) {
				if ( isset( $product_shipping_classes[ $product_shipping_class ] ) ) {
					$product_shipping_classes[ $product_shipping_class ] += $product['quantity'];
				} else {
					$product_shipping_classes[ $product_shipping_class ] = $product['quantity'];
				}
			}

			$skip_free_shipping = get_post_meta( $product['product_id'], '_tvg_skip_free_shipping', true );
			if ( 'yes' === $skip_free_shipping ) {
				$product_free_shipping = false;
			}
			$weight += floatval( $product['data']->get_weight() ) * floatval( $product['quantity'] );

			if ( $product['data']->has_dimensions() && ( ! empty( $product['data']->get_length() ) && ! empty( $product['data']->get_width() ) && ! empty( $product['data']->get_height() ) ) ) {
				for ( $i = 0; $i < $product['quantity']; $i++ ) {
					$dimension = array(
						$product['data']->get_length(),
						$product['data']->get_width(),
						$product['data']->get_height(),
					);

					rsort( $dimension );

					$boxes[] = array(
						'length' => $dimension[0],
						'width'  => $dimension[1],
						'height' => $dimension[2],
					);
				}
			} else {
				$boxes[] = array(
					'length' => 0,
					'width'  => 0,
					'height' => 0,
				);
			}
		}

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/methods/class-tvgexpress-options.php';

		$packer->pack( $boxes );
		$container_size = $packer->get_container_dimensions();

		$options_model = new Tvgexpress_Options();
		$options_model
		->set_length( $options_model->get_dimension( floatval( $container_size['length'] ) ) )
		->set_width( $options_model->get_dimension( floatval( $container_size['width'] ) ) )
		->set_height( $options_model->get_dimension( floatval( $container_size['height'] ) ) )
		->set_weight( $options_model->convert_weight( $weight ) )
		->set_postcode( $woocommerce->customer->get_shipping_postcode() )
		->set_country_code( $woocommerce->customer->get_shipping_country() )
		->set_dimension_weight();

		$opt = $options_model->get_options();
		foreach ( $opt as $key => $option ) {
			if ( $options_model->is_available( $option ) && 'yes' === $this->get_option( "{$option['id']}_enabled" ) ) {

				$price = '';
				if ( ! empty( $this->get_option( "{$option['id']}_price_type" ) ) ) {
					$weight_class = $options_model->get_weight_class( $option );

					$price = $this->get_option( 'weight_' . $weight_class['id'] );
				}

				$price = ( '' === $price ) ? $options_model->get_price( $option ) : $price;

				$weight = $options_model->get_weight();

				$skip_dimension_price = $this->get_option( $option['id'] . '_skip_dimension_price' );

				if ( 'yes' !== $skip_dimension_price ) {
					if ( isset( $option['useDimensionalWeight'] ) && $option['useDimensionalWeight'] ) {
						$dimension_weight = $options_model->get_dimension_weight();

						if ( $dimension_weight > $weight ) {
							$weight = $dimension_weight;
						}
					}

					if ( isset( $option['additionalPrice'] ) ) {
						foreach ( $option['additionalPrice'] as $additional_price ) {
							if ( $additional_price['afterWeight'] < $weight ) {
								$weight_diff = $weight - $additional_price['afterWeight'];

								$price += ( $weight_diff * $additional_price['price'] );
							}
						}
					}
				}

				if ( isset( $option['postcodePrice'] ) ) {
					foreach ( $option['postcodePrice'] as $postcode_price ) {
						if ( in_array( $options_model->get_postcode(), $postcode_price['postcodes'] ) ) {
							$price += $postcode_price['price'];
						}
					}
				}

				$is_free_shipping = false;
				$free_shipping    = $this->get_option( "{$option['id']}_free_shipping" );
				if ( ! empty( $free_shipping ) && $free_shipping < $package['contents_cost'] && $product_free_shipping ) {
					$is_free_shipping = true;
				}

				$added_cost = 0;
				$tmp_price  = $price;
				foreach ( $product_shipping_classes as $product_shipping_class => $product_qty ) {
					$sc_skip_free_shipping = $this->get_option( $option['id'] . '_sc_skip_fs_' . $product_shipping_class );
					if ( ( $is_free_shipping && ! empty( $sc_skip_free_shipping ) && 'yes' === $sc_skip_free_shipping ) || ! $is_free_shipping ) {

						$is_free_shipping = false;

						$shipping_class_price = $this->get_option( $option['id'] . '_shipping_class_' . $product_shipping_class );
						if ( ! empty( $shipping_class_price ) && is_numeric( $shipping_class_price ) ) {
							$multiple_qty = $this->get_option( $option['id'] . '_sc_qty_' . $product_shipping_class );
							if ( ! empty( $multiple_qty ) && 'yes' === $multiple_qty ) {
								$shipping_class_price = floatval( $shipping_class_price ) * floatval( $product_qty );
							}

							$calc_type = $this->get_option( $option['id'] . '_sc_calc_type_' . $product_shipping_class );
							if ( '0' === $calc_type ) {
								$added_cost += $shipping_class_price;
							} elseif ( '1' === $calc_type ) {
								if ( $shipping_class_price > $tmp_price ) {
									$tmp_price = $shipping_class_price;
								}
							}
						}
					}
				}

				if ( $is_free_shipping && $tmp_price === $price && 0 === $added_cost ) {
					$price = 0;
				} elseif ( $price !== $tmp_price ) {
					$price = $tmp_price;
				}

				$price += $added_cost;

				if ( 'DROPP' === $option['id'] ) {
					$title = ( ! empty( $this->get_option( "{$option['id']}_title" ) ) ) ? $this->get_option( "{$option['id']}_title" ) : 'Afhendingarstaður';
				} else {
					$title = ( ! empty( $this->get_option( "{$option['id']}_title" ) ) ) ? $this->get_option( "{$option['id']}_title" ) : $option['title'];
				}

				$has_tax = ( 'yes' === $this->get_option( 'tvg_use_woo_tax' ) ) ? true : false;

				$rate = array(
					'id'        => $option['id'],
					'label'     => $title,
					'cost'      => $price,
					// 'calc_tax' => 'per_order', !
					'taxes'     => $has_tax,
					'meta_data' => array( 'desc' => ( ! empty( $this->get_option( "{$option['id']}_desc" ) ) ) ? $this->get_option( "{$option['id']}_desc" ) : $option['desc'] ),
				);
				$this->add_rate( $rate );
			}
		}

	}
}
