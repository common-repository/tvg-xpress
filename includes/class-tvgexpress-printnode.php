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
class Tvgexpress_PrintNode {

	/**
	 * The API key required to authenticate with the PrintNode API.
	 *
	 * @var string $api_key The API key used for authentication with the PrintNode API.
	 */
	protected $api_key;

	/**
	 * Stores the information about the printer to be used for printing.
	 *
	 * @var string $printer The printer information.
	 */
	protected $printer;

	/**
	 * Api url to connect.
	 *
	 * @var string $_api_url The base URL for the PrintNode API.
	 */
	protected $_api_url = 'https://api.printnode.com/';

	/**
	 * Constructor method for the MyPrintNodeIntegration class.
	 *
	 * @param string $api_key The API key used for authentication with the PrintNode API.
	 * @description Initializes the MyPrintNodeIntegration class and sets up the necessary headers for API requests.
	 */
	public function __construct( $api_key ) {
		$this->_headers = array(
			'Content-Type'  => 'application/json',
			'Accept'        => 'application/json',
			'Authorization' => 'basic ' . base64_encode( $api_key ),
		);
		$this->api_key  = $api_key;
	}

	public function get_printers() {
		return $this->get( 'printers' );
	}
	public function print_pdf_base64( $base64 ) {
		$params = array(
			'printerId'   => $this->get_printer(),
			'contentType' => 'pdf_base64',
			'content'     => $base64,
		);
		return $this->post( 'printjobs', $params );
	}

	/**
	 * Get printer.
	 *
	 * @return mixed
	 */
	public function get_printer() {
		return $this->printer;
	}

	/**
	 * Set printer.
	 *
	 * @param  mixed $printer Set the printer.
	 * @return PrintNode
	 */
	public function set_printer( $printer ) {
		$this->printer = $printer;
		return $this;
	}



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

	protected function log( $message ) {
		if ( empty( $this->log ) ) {
			$this->log = wc_get_logger();
		}

		$this->log->debug( $message, array( 'source' => 'tvg-xpress' ) );

	}
}
