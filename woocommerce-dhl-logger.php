<?php
/**
 * Plugin Name: WooCommerce DHL Logger
 * Plugin URI: https://github.com/your-username/woocommerce-dhl-logger
 * Description: Logs requests and responses made by the WooCommerce DHL Express Services plugin for debugging and monitoring purposes.
 * Version: 1.0.0
 * Author: @nicw, WooCommerce Growth Team
 * Author URI: https://woocommerce.com
 * Text Domain: woocommerce-dhl-logger
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.5
 * Requires Plugins: woocommerce, woocommerce-dhlexpress-services
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package WooCommerce_DHL_Logger
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Declare HPOS compatibility.
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );


/**
 * Initialize the plugin.
 */
function wc_dhl_logger_init() {
	// Load the logger class.
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-dhl-logger.php';

	// Initialize the logger.
	new WC_DHL_Logger();
}

// Hook into plugins_loaded to ensure WooCommerce is loaded first.
add_action( 'plugins_loaded', 'wc_dhl_logger_init' );
