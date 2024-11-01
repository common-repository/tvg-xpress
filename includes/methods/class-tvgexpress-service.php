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
class Tvgexpress_Service {

	protected $_headers;
	protected $_api_url = 'https://appgw.eimskip.net/gateway/TVGXpressOperations/1.0/';
	protected $_response;
	protected $_client;
	protected $_service = 'TVGXpress';
	/**
	 * Construct function
	 *
	 * @since    1.0.0
	 * @param string $username Username.
	 * @param string $api_key ApiKey to connect to service.
	 * @param bool   $test Test service or not.
	 */
	public function __construct( $username, $api_key, $test = false ) {
		$this->_headers = array(
			'Content-Type'  => 'application/json',
			'Accept'        => 'application/json',
			'Authorization' => sprintf( 'Basic %s', base64_encode( $username . ':' . $api_key ) ),
		);

		if ( $test ) {
			$this->_api_url = 'https://appgw-test.eimskip.net/gateway/TVGXpressOperations/1.0/';
		}

		$this->_client = new WP_Http();
	}



	/**
	 *
	 * This service is used to get labels for a given shipment. A label contains details about the shipment it
	 * refers to. A ZPL string or a PDF file can be requested.
	 *
	 * @param string $shipment_id - The shipment number for which to get labels.
	 * @param array  $params [labelType] - Enum: "ZPL_STRING", "PDF".
	 * @return array [status] - Segment that indicates whether the operation was successful or not.
	 */
	public function get_labels( $shipment_id, $params = array() ) {
		return $this->get( sprintf( 'labels/%s', $shipment_id ), $params );
	}

	/**
	 * Returns detailed information about the requested shipment.
	 *
	 * @param string $shipment_id Shipment_id to fetch.
	 * @return array [status] Segment that indicates whether the operation was successful or not.
	 */
	public function get_shipment( $shipment_id ) {
		return $this->get( sprintf( 'shipments/info/%s', $shipment_id ) );
	}


	/**
	 * Returns detailed information about all shipments created within the specified time interval.
	 * Maximum date span is 30 days.
	 *
	 * @param date $date_from Pattern: yyyy-MM-dd HH:mm:ss.
	 * @param date $date_to Pattern: yyyy-MM-dd HH:mm:ss.
	 *
	 * @return array [status] Segment that indicates whether the operation was successful or not.<br><br>
	 */
	public function get_shipments( $date_from, $date_to ) {
		return $this->get(
			'shipments/info',
			array(
				'dateFrom' => $date_from,
				'dateTo'   => $date_to,
			)
		);
	}

	/**
	 * Returns key information about the requested shipment.
	 *
	 * @param string $shipment_id Reference number of the original shipment.
	 * @return array [status] Segment that indicates whether the operation was successful or not.
	 */
	public function get_shipment_status( $shipment_id ) {
		return $this->get( sprintf( 'shipments/status/%s', $shipment_id ) );
	}
	/**
	 * Get Shipment status html
	 *
	 * @since    1.0.0
	 * @param string $shipment_id Shipment to fetch.
	 */
	public function get_shipment_status_html( $shipment_id ) {
		$response = $this->get( sprintf( 'shipments/status/%s', $shipment_id ) );

		$html = '';

		if ( ! empty( $response ) && !empty($response->status) && '200' === $response->status->code ) {
			$status_index = 1;
			$status_index = ( strlen( $response->response->shipmentStatus->ReceivedDate ) > 0 ) ? 2 : $status_index;
			$status_index = ( strlen( $response->response->shipmentStatus->DeliveryRequestDate ) > 0 ) ? 3 : $status_index;
			$status_index = ( strlen( $response->response->shipmentStatus->DeliveryConfirmedDate ) > 0 ) ? 4 : $status_index;

			$html = '<ul class="tvg-shipping-status">
                <li class="' . esc_html( $this->get_status_class( 1, $status_index ) ) . '">Pakki skráður</li>
                <li class="' . esc_html( $this->get_status_class( 2, $status_index ) ) . '">Pakkinn kominn til flutningsaðila</li>
                <li class="' . esc_html( $this->get_status_class( 3, $status_index ) ) . '">Pakkinn er á leiðinni til viðskiptavinar</li>
                <li class="' . esc_html( $this->get_status_class( 4, $status_index ) ) . '">Pakki hefur komist til skila</li>
                </ul>';
		}
		return $html;
	}
	/**
	 * Get Status Class
	 *
	 * @since    1.0.0
	 * @param string $index Index to compare.
	 * @param string $status_index Status Index to verify.
	 */
	private function get_status_class( $index, $status_index ): string {
		if ( $index === $status_index ) {
			return 'active';
		} elseif ( $index > $status_index ) {
			return 'disabled';
		} else {
			return 'done';
		}
	}
	/**
	 * This service is used to register a new shipment in the TVGXpress system. If registration is successful,
	 * the service returns a shipment number generated for the request.
	 *
	 * @param array $params [request].
	 * @return array [status] Segment that indicates whether the operation was.
	 */
	public function create_shipment( $params ) {
		return $this->post( 'shipments', $params );
	}
	/**
	 * Get data
	 *
	 * @since    1.0.0
	 * @param string $url Url to get data.
	 * @param array  $params Params.
	 */
	protected function get( $url, $params = null ) {
		$data = array(
			'headers' => $this->_headers,
		);
		if ( ! empty( $params ) ) {
			$url = $url . '?' . http_build_query( $params );
		}

		try {
			$response     = wp_remote_get( $this->_api_url . $url, $data );
			$code         = wp_remote_retrieve_response_code( $response );
			$ret_response = json_decode( wp_remote_retrieve_body( $response ) );
			$this->log(
				wc_print_r(
					array(
						'code'     => $code,
						'url'      => $this->_api_url . $url,
						'data'     => $params,
						'response' => $ret_response,
					),
					true
				)
			);
			return $ret_response;
		} catch ( exception $e ) {
			$this->log(
				wc_print_r(
					array(
						'code'     => $e->getCode(),
						'url'      => $this->_api_url . $url,
						'data'     => $params,
						'response' => $e->getMessage(),
					),
					true
				)
			);
		}
	}
	/**
	 * Post data
	 *
	 * @since    1.0.0
	 * @param string $url Url to post data.
	 * @param array  $params Params to post.
	 */
	protected function post( $url, $params ) {

		$data = array(
			'headers'     => $this->_headers,
			'body'        => wp_json_encode( $params ),
			'data_format' => 'body',
		);

		try {
			$response     = wp_remote_post( $this->_api_url . $url, $data );
			$code         = wp_remote_retrieve_response_code( $response );
			$ret_response = json_decode( wp_remote_retrieve_body( $response ) );
			$this->log(
				wc_print_r(
					array(
						'code'    => $code,
						'url'     => $this->_api_url . $url,
						'data'    => $params,
						'rsponse' => $ret_response,
					),
					true
				)
			);
			return $ret_response;

		} catch ( exception $e ) {
			$this->log(
				wc_print_r(
					array(
						'code'    => $e->getCode(),
						'url'     => $this->_api_url . $url,
						'data'    => $params,
						'rsponse' => $e->getMessage(),
					),
					true
				)
			);
		}
	}
	/**
	 * Track package
	 *
	 * @since    1.0.0
	 * @param array $body body to track.
	 */
	public function track_package( $body = array() ) {

		$url = 'https://tvg-shopify-jacd3.ondigitalocean.app/api/collection/track';

		$args = array(
			'timeout' => 0,
			'method'  => 'POST',
			'headers' => array(
				'Content-Type' => 'application/json',
				'Connection'   => 'keep-alive',
			),
		);

		if ( ! empty( $body ) ) {
			$body         = wp_json_encode( $body );
			$args['body'] = $body;
		}
		try {
			$response     = wp_remote_request( $url, $args );
			$code         = wp_remote_retrieve_response_code( $response );
			$ret_response = json_decode( wp_remote_retrieve_body( $response ) );
			$this->log(
				wc_print_r(
					array(
						'code'     => $code,
						'url'      => $url,
						'data'     => json_decode( $body ),
						'response' => $ret_response,
					),
					true
				)
			);
			return $ret_response;
		} catch ( exception $e ) {
			$this->log(
				wc_print_r(
					array(
						'code'     => $e->getCode(),
						'url'      => $url,
						'data'     => json_decode( $body ),
						'response' => $e->getMessage(),
					),
					true
				)
			);
		}
	}
	/**
	 * Retrieve last response
	 *
	 * @return GuzzleHttp\Psr7\Response
	 */
	public function get_response() {
		return $this->_response;
	}
	/**
	 * Log to log errors
	 *
	 * @since    1.0.0
	 * @param string $message Log message.
	 */
	protected function log( $message ) {
		if ( empty( $this->log ) ) {
			$this->log = wc_get_logger();
		}

		$this->log->debug( $message, array( 'source' => 'tvg-xpress' ) );

	}
	/**
	 * Allowed html for escaped.
	 *
	 * @since    1.0.0
	 */
	public function allowedhtml() {
		return array(
			'ul' => array(
				'class' => array(),
			),
			'li' => array(
				'class' => array(),
			),
		);
	}
}

