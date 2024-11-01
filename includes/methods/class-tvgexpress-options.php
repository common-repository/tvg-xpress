<?php /**
	   * Options for Eimskip plugin
	   *
	   * @link       smartmedia.is
	   * @since      1.0.0
	   * @package    Tvgexpress
	   * @subpackage Tvgexpress/admin
	   */
class Tvgexpress_Options {

	/**
	 * The weight of the package.
	 *
	 * @var float
	 */
	public $weight;

	/**
	 * The length of the package.
	 *
	 * @var float
	 */
	public $length;

	/**
	 * The width of the package.
	 *
	 * @var float
	 */
	public $width;

	/**
	 * The height of the package.
	 *
	 * @var float
	 */
	public $height;

	/**
	 * The weight to be used for calculating dimensional weight.
	 *
	 * @var float
	 */
	public $dimension_weight;

	/**
	 * The maximum length allowed for a package.
	 *
	 * @var int
	 */
	public $max_length = 40;

	/**
	 * The maximum width allowed for a package.
	 *
	 * @var int
	 */
	public $max_width  = 30;

	/**
	 * The maximum height allowed for a package.
	 *
	 * @var int
	 */
	public $max_height = 30;

	/**
	 * The price per kilogram for dimensional weight.
	 *
	 * @var int
	 */
	private $dimension_kg_price = 42;

	/**
	 * The postcode of the destination.
	 *
	 * @var string
	 */
	public $postcode;

	/**
	 * The country code of the destination.
	 *
	 * @var string
	 */
	public $country_code;

	/**
	 * The API URL for retrieving shipping data.
	 *
	 * @var string
	 */
	protected $_api_url = 'http://tvg-methods.viral.is.s3-website.eu-west-2.amazonaws.com/';

	/**
	 * Get remote data from the API.
	 *
	 * @return object|bool The response data or false on failure.
	 */
	public function get() {
		$data     = array(
			'headers' => array(
				'Content-Type' => 'application/json',
				'Accept'       => 'application/json',
			),
		);
		$response = wp_remote_get( $this->_api_url, $data );
		return json_decode( wp_remote_retrieve_body( $response ) );
	}
	/**
	 * Get a shipping option by its ID.
	 *
	 * @since    1.0.0
	 * @param string $id The ID of the option to retrieve.
	 * @return array|false The option data or false if not found.
	 */
	public function get_by_id( $id ) {

		$options = $this->get_options();

		foreach ( $options as $option ) {
			if ( $id === $option['id'] ) {
				return $option;
			}
		}
		return false;
	}
	/**
	 * Get all shipping options.
	 *
	 * @return array The array of shipping options.
	 */
	public function get_options() {
		$tvg_shipping_options = get_transient( 'tvg_shipping_options' );

		if ( empty( $tvg_shipping_options ) ) {
			$tvg_shipping_options = json_decode( wp_json_encode( $this->get()->methods ), true );

			// Put the results in a transient. Expire after 12 hours.
			set_transient( 'tvg_shipping_options', $tvg_shipping_options, 12 * HOUR_IN_SECONDS );
		}

		return $tvg_shipping_options;
	}
	/**
	 * Get Price
	 *
	 * @since    1.0.0
	 * @param array $option Option to check.
	 * @return float The calculated shipping cost.
	 */
	public function get_price( $option ) {
		$weight = $this->get_weight();

		if ( isset( $option['weight'] ) ) {

			if ( isset( $option['useDimensionalWeight'] ) && $option['useDimensionalWeight'] ) {
				$dimension_weight = $this->get_dimension_weight();
				if ( $dimension_weight > $weight ) {
					$weight = $dimension_weight;
				}
			}

			if ( $option['maxWeight'] < $weight && ! empty( $option['noMaxWeight'] ) ) {
				$array = end( $option['weight'] );
				return floatval( $array['price'] );
			}

			foreach ( $option['weight'] as $w ) {
				if ( $w['min'] <= $weight && $w['max'] >= $weight ) {
					return floatval( $w['price'] );
				}
			}
		}

		return '';
	}
	/**
	 * Get Weight Class
	 *
	 * @since    1.0.0
	 * @param array $option Option to check.
	 */
	public function get_weight_class( $option ) {
		$weight = $this->get_weight();

		if ( isset( $option['weight'] ) ) {

			if ( isset( $option['useDimensionalWeight'] ) && $option['useDimensionalWeight'] ) {
				$dimension_weight = $this->get_dimension_weight();
				if ( $dimension_weight > $weight ) {
					$weight = $dimension_weight;
				}
			}

			if ( $option['maxWeight'] < $weight && ! empty( $option['noMaxWeight'] ) ) {
				return end( $option['weight'] );
			}

			foreach ( $option['weight'] as $w ) {
				if ( $w['min'] <= $weight && $w['max'] >= $weight ) {
					return $w;
				}
			}
		}
		return false;
	}
	/**
	 * Check if is Available
	 *
	 * @since    1.0.0
	 * @param string $option Option to check.
	 */
	public function is_available( $option ) {
		return $this->is_valid_postcode( $option ) && $this->is_valid_weight( $option ) && $this->is_valid_country_code( $option ) && $this->is_valid_dimension( $option );
	}
	/**
	 * Check if is Valid Dimension
	 *
	 * @since    1.0.0
	 * @param array $option Option to check.
	 */
	public function is_valid_dimension( $option ) {
		if ( 'DROPP' === $option['id'] ) {
			if ( $this->get_length() <= $this->max_length && $this->get_width() <= $this->max_width && $this->get_height() <= $this->max_height ) {
				return true;
			}
			return false;
		}
		return true;
	}
	/**
	 * Check if is Valid Postcode
	 *
	 * @since    1.0.0
	 * @param array $ship Option to check.
	 */
	public function is_valid_postcode( $ship ) {
		$postcode = $this->get_postcode();
		if ( empty( $postcode ) ) {
			return true;
		}
		if ( is_array( $ship['postcode'] ) ) {
			if ( in_array( $postcode, $ship['postcode'] ) ) {
				return true;
			} else {
				return false;
			}
		}

		if ( 'A' === $ship['postcode'] ) {
			return true;
		}
		if ( 'H' === $ship['postcode'] ) {
			return ( $postcode < 275 && ! in_array( $postcode, array( 116, 190 ) ) );
		}
		if ( 'L' === $ship['postcode'] ) {
			return ( $postcode > 275 || in_array( $postcode, array( 116, 190 ) ) );
		}

		if ( 'NH' === $ship['postcode'] ) {
			return in_array( $postcode, array( 230, 235, 240, 250, 260, 262, 276, 300, 400, 600, 603, 800, 810, 815, 820, 825 ) );
		}

		if ( 'PB' === $ship['postcode'] ) {
			$boxes = $this->get_postboxes();
			foreach ( $boxes as $box ) {
				if ( substr( $box['postcode'], 0, 1 ) === substr( $postcode, 0, 1 ) ) {
					return true;}
			}
			return false;
		}
		return false;
	}
	/**
	 * Get and return Postboxes
	 *
	 * @since    1.0.0
	 */
	public function get_postboxes() {
		$tvg_postboxes = get_transient( 'tvg_postboxes' );
		if ( false === $tvg_postboxes ) {
			$tvg_postboxes = json_decode( json_encode( $this->get()->postboxes ), true );
			// Put the results in a transient. Expire after 12 hours.
			set_transient( 'tvg_postboxes', $tvg_postboxes, 12 * HOUR_IN_SECONDS );
		}
		return $tvg_postboxes;
	}
	/**
	 * Check if is Valid Weight
	 *
	 * @since    1.0.0
	 * @param array $option Option to check.
	 */
	public function is_valid_weight( $option ) {
		$weight = $this->get_weight();

		if ( ! empty( $option['noMaxWeight'] ) ) {
			return true;
		}

		if ( key_exists( 'minWeight', $option ) ) {
			return ( $option['minWeight'] <= $weight && $option['maxWeight'] >= $weight );
		} else {
			return true;
		}
	}

	public function is_valid_country_code() {
		return 'IS' === $this->get_country_code();
	}
	/**
	 * Check if is Postbox
	 *
	 * @since    1.0.0
	 * @param string $id Check if is Postbox.
	 */
	public function is_postbox( $id ) {
		return 'DROPP' === $id;
	}

	public function set_weight( $weight ) {
		$this->weight = $weight;
		return $this;
	}
	public function get_weight() {
		return $this->weight;
	}
	public function set_length( $length ) {
		$this->length = $length;
		return $this;
	}
	public function get_length() {
		return $this->length;
	}
	public function set_width( $width ) {
		$this->width = $width;
		return $this;
	}
	public function get_width() {
		return $this->width;
	}
	public function set_height( $height ) {
		$this->height = $height;
		return $this;
	}
	public function get_height() {
		return $this->height;
	}
	public function set_postcode( $postcode ) {
		$this->postcode = $postcode;
		return $this;
	}
	public function get_postcode() {
		return $this->postcode;
	}
	public function set_country_code( $country_code ) {
		$this->country_code = $country_code;
		return $this;
	}
	public function get_country_code() {
		return $this->country_code;
	}
	public function get_dimension_kg_price() {
		return $this->dimension_kg_price;
	}
	public function is_valid_id( $id ) {
		return $this->get_by_id( $id ) !== false;
	}
	/**
	 * Make Select box
	 */
	public function to_selectbox() {
		$tvg_settings = get_option( 'woocommerce_tvg_express_settings' );

		$html  = "<select name='tvg_shipping_method' class='tvg_shipping_method' >";
		$html .= "<option selected disabled value='0'>Velja nýjan sendingarmáta</option>";
		foreach ( $this->get_options() as $option ) {
			if ( empty( $tvg_settings[ "{$option['id']}_enabled" ] ) || 'yes' === $tvg_settings[ "{$option['id']}_enabled" ] ) {

				if ( 'DROPP' === $option['id'] ) {
					$title = ( ! empty( $tvg_settings[ "{$option['id']}_title" ] ) ) ? $tvg_settings[ "{$option['id']}_title" ] : 'Afhendingarstaður';
				} else {
					$title = ( ! empty( $tvg_settings[ "{$option['id']}_title" ] ) ) ? $tvg_settings[ "{$option['id']}_title" ] : $option['title'];
				}

				$html .= sprintf( '<option value="%s">%s</option>', esc_html( $option['id'] ), esc_html( $title ) );
			}
		}
		$html .= '</select>';

		$html .= "<select name='tvg_shipping_box' class='tvg_shipping_box'>";
		foreach ( $this->get_postboxes() as $option ) {
			if ( true === $option['active'] ) {
				$html .= sprintf( '<option value="%s">%s</option>', esc_html( $option['postboxId'] ), esc_html( $option['name'] ) );
			}
		}
		$html .= '</select>';

		return $html;
	}
	/**
	 * Get Dimension to return
	 *
	 * @since    1.0.0
	 * @param string $dimension Dimension to return.
	 */
	public function get_dimension( $dimension ) {

		$dimension_unit = get_option( 'woocommerce_dimension_unit' );
		switch ( $dimension_unit ) {

			case 'mm':
				return $dimension / 10.000;

			case 'in':
				return $dimension / 0.39370;

			case 'yd':
				return $dimension / 0.010936;

			case 'cm':
				return $dimension;

			case 'm':
				return $dimension / 0.010000;

			default:
				return $dimension;
		}
	}
	/**
	 * Convert weight
	 *
	 * @since    1.0.0
	 * @param string $weight Weight to convert.
	 */
	public function convert_weight( $weight ) {

		$weight_unit = get_option( 'woocommerce_weight_unit' );

		switch ( $weight_unit ) {

			case 'kg':
				return $weight;

			case 'g':
				return $weight * 0.0010000;

			case 'lbs':
				return $weight * 0.45359237;

			case 'oz':
				return $weight * 0.02834952;

			default:
				return $weight;
		}
	}
	/**
	 * Set Dimension Weight
	 *
	 * @since    1.0.0
	 */
	public function set_dimension_weight() {
		$length = $this->get_length() / 100;
		$width  = $this->get_width() / 100;
		$height = $this->get_height() / 100;

		$this->dimension_weight = ( $length * $width * $height ) * 350;
		return $this;
	}
	/**
	 * Get Dimension weight
	 */
	public function get_dimension_weight() {
		return $this->dimension_weight;
	}
	public function allowedhtml() {
		return array(
			'select' => array(
				'id'    => array(),
				'name'  => array(),
				'class' => array(),
			),
			'option' => array(
				'selected' => array(),
				'disabled' => array(),
				'value'    => array(),
			),
		);
	}
}
