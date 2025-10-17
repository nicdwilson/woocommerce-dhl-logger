<?php
/**
 * WooCommerce DHL Logger Class
 *
 * @package WooCommerce_DHL_Logger
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_DHL_Logger class.
 *
 * Handles logging of DHL Express Services API requests and responses.
 */
class WC_DHL_Logger {

	/**
	 * Logger instance.
	 *
	 * @var WC_Logger
	 */
	private $logger;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->logger = wc_get_logger();
		$this->init_hooks();
	}

	/**
	 * Initialize hooks to intercept DHL API calls.
	 */
	private function init_hooks() {
		// Hook into wp_remote_post to intercept DHL API requests.
		add_filter( 'pre_http_request', array( $this, 'intercept_dhl_request' ), 10, 3 );
		
		// Hook into wp_remote_retrieve_body to log responses.
		add_action( 'http_api_debug', array( $this, 'log_dhl_response' ), 10, 5 );
	}

	/**
	 * Intercept DHL API requests and log them.
	 *
	 * @param false|array|WP_Error $preempt Whether to preempt an HTTP request's return value.
	 * @param array                $parsed_args Parsed HTTP request arguments.
	 * @param string               $url The request URL.
	 * @return false|array|WP_Error
	 */
	public function intercept_dhl_request( $preempt, $parsed_args, $url ) {
		// Check if this is a DHL API request.
		if ( $this->is_dhl_api_request( $url ) ) {
			$this->log_request( $url, $parsed_args );
		}

		return $preempt;
	}

	/**
	 * Log DHL API responses.
	 *
	 * @param array|WP_Error $response HTTP response or WP_Error.
	 * @param string         $type The type of HTTP request.
	 * @param string         $class The HTTP transport used.
	 * @param array          $parsed_args HTTP request arguments.
	 * @param string         $url The request URL.
	 */
	public function log_dhl_response( $response, $type, $class, $parsed_args, $url ) {
		// Only log for DHL API requests.
		if ( ! $this->is_dhl_api_request( $url ) ) {
			return;
		}

		$this->log_response( $url, $response, $parsed_args );
	}

	/**
	 * Check if the URL is a DHL API request.
	 *
	 * @param string $url The request URL.
	 * @return bool True if it's a DHL API request, false otherwise.
	 */
	private function is_dhl_api_request( $url ) {
		return strpos( $url, 'api.starshipit.com' ) !== false && strpos( $url, 'DHL' ) !== false;
	}

	/**
	 * Log the request details.
	 *
	 * @param string $url The request URL.
	 * @param array  $args Request arguments.
	 */
	private function log_request( $url, $args ) {
		$log_data = array(
			'method'     => isset( $args['method'] ) ? $args['method'] : 'GET',
			'url'        => $url,
			'headers'    => isset( $args['headers'] ) ? $args['headers'] : array(),
			'body'       => isset( $args['body'] ) ? $this->format_json_body( $args['body'] ) : '',
			'timeout'    => isset( $args['timeout'] ) ? $args['timeout'] : 30,
			'timestamp'  => current_time( 'mysql' ),
		);

		// Sanitize sensitive data.
		$log_data = $this->sanitize_log_data( $log_data );

		$this->logger->info(
			'DHL API Request:',
			array( 
				'data' => $log_data,
				'source' => 'woocommerce-dhl-logger' 
			)
		);
	}

	/**
	 * Log the response details.
	 *
	 * @param string         $url The request URL.
	 * @param array|WP_Error $response The HTTP response.
	 * @param array          $args Original request arguments.
	 */
	private function log_response( $url, $response, $args ) {
		$log_data = array(
			'url'       => $url,
			'timestamp' => current_time( 'mysql' ),
		);

		if ( is_wp_error( $response ) ) {
			$log_data['error'] = array(
				'code'    => $response->get_error_code(),
				'message' => $response->get_error_message(),
			);
		} else {
			$log_data['response'] = array(
				'code'    => wp_remote_retrieve_response_code( $response ),
				'message' => wp_remote_retrieve_response_message( $response ),
				'headers' => wp_remote_retrieve_headers( $response )->getAll(),
				'body'    => $this->format_json_body( wp_remote_retrieve_body( $response ) ),
			);
		}

		// Sanitize sensitive data.
		$log_data = $this->sanitize_log_data( $log_data );

		$log_level = is_wp_error( $response ) ? 'error' : 'info';
		
		$this->logger->log(
			$log_level,
			'DHL API Response:',
			array( 
				'data' => $log_data,
				'source' => 'woocommerce-dhl-logger' 
			)
		);
	}

	/**
	 * Format JSON body to remove unnecessary whitespace and newlines.
	 *
	 * @param string $body The request body.
	 * @return string|array Formatted body.
	 */
	private function format_json_body( $body ) {
		// If it's already JSON, decode and return as array for better logging.
		$decoded = json_decode( $body, true );
		if ( $decoded !== null ) {
			return $decoded;
		}
		
		// If it's not valid JSON, return as-is.
		return $body;
	}

	/**
	 * Sanitize log data to remove sensitive information.
	 *
	 * @param array $data The log data to sanitize.
	 * @return array Sanitized log data.
	 */
	private function sanitize_log_data( $data ) {
		// Remove API keys from URL.
		if ( isset( $data['url'] ) ) {
			$data['url'] = preg_replace( '/apiKey=[^&]+/', 'apiKey=***REDACTED***', $data['url'] );
		}

		// Remove API keys from headers.
		if ( isset( $data['headers'] ) && is_array( $data['headers'] ) ) {
			foreach ( $data['headers'] as $key => $value ) {
				if ( stripos( $key, 'authorization' ) !== false || stripos( $key, 'api-key' ) !== false ) {
					$data['headers'][ $key ] = '***REDACTED***';
				}
			}
		}

		// Remove API keys from request body.
		if ( isset( $data['body'] ) ) {
			if ( is_array( $data['body'] ) && isset( $data['body']['apiKey'] ) ) {
				$data['body']['apiKey'] = '***REDACTED***';
			} elseif ( is_string( $data['body'] ) ) {
				$body_data = json_decode( $data['body'], true );
				if ( $body_data && isset( $body_data['apiKey'] ) ) {
					$body_data['apiKey'] = '***REDACTED***';
					$data['body'] = $body_data;
				}
			}
		}

		// Remove API keys from response body.
		if ( isset( $data['response']['body'] ) ) {
			if ( is_array( $data['response']['body'] ) && isset( $data['response']['body']['apiKey'] ) ) {
				$data['response']['body']['apiKey'] = '***REDACTED***';
			} elseif ( is_string( $data['response']['body'] ) ) {
				$response_data = json_decode( $data['response']['body'], true );
				if ( $response_data && isset( $response_data['apiKey'] ) ) {
					$response_data['apiKey'] = '***REDACTED***';
					$data['response']['body'] = $response_data;
				}
			}
		}

		return $data;
	}
}
