<?php
/**
 * Uninstall file for WooCommerce DHL Logger
 *
 * @package WooCommerce_DHL_Logger
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Clean up any plugin-specific data if needed.
// Note: We don't delete WooCommerce logs as they may contain other important information.
// Users can manually clean up logs through WooCommerce > Status > Logs if needed.
