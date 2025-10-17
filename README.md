# WooCommerce DHL Logger

A WordPress plugin that logs requests and responses made by the WooCommerce DHL Express Services plugin for debugging and monitoring purposes.

## Description

This plugin automatically intercepts and logs all API requests and responses made by the WooCommerce DHL Express Services plugin. It provides detailed logging information including request URLs, headers, body data, response codes, and response bodies.

## Features

- **Automatic Logging**: Automatically logs all DHL API requests and responses
- **Sensitive Data Protection**: Automatically redacts API keys and sensitive information from logs
- **WooCommerce Integration**: Uses WooCommerce's built-in logging system (`wc_get_logger`)
- **HPOS Compatible**: Declares compatibility with WooCommerce's High-Performance Order Storage (HPOS)
- **WordPress Coding Standards**: Follows WordPress Coding Standards (WPCS)

## Requirements

- WordPress 5.0 or higher
- WooCommerce 5.0 or higher
- WooCommerce DHL Express Services plugin
- PHP 7.4 or higher

## Installation

1. Upload the `woocommerce-dhl-logger` folder to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Ensure WooCommerce and the DHL Express Services plugin are active

## Usage

Once activated, the plugin will automatically start logging DHL API requests and responses. You can view the logs in:

**WooCommerce > Status > Logs**

Look for logs with the source `woocommerce-dhl-logger`.

## Log Format

The plugin logs both requests and responses in JSON format with the following information:

### Request Logs
- Method (GET, POST, etc.)
- URL (with API keys redacted)
- Headers
- Request body
- Timeout settings
- Timestamp

### Response Logs
- Response code
- Response message
- Response headers
- Response body
- Timestamp
- Error information (if applicable)

## Security

The plugin automatically redacts sensitive information including:
- API keys in URLs
- Authorization headers
- API keys in request/response bodies

## Support

For support and bug reports, please create an issue in the plugin repository.

## License

This plugin is licensed under the GPL v2 or later.

## Changelog

### 1.0.0
- Initial release
- Automatic logging of DHL API requests and responses
- Sensitive data redaction
- HPOS compatibility
- WordPress Coding Standards compliance
