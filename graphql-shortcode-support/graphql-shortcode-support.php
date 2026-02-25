<?php
/**
 * Plugin Name: GraphQL Shortcode Support
 * Plugin URI: https://github.com/SilverAssist/graphql-shortcode-support
 * Description: Applies do_shortcode() to WPGraphQL content fields, rendering shortcodes as HTML in GraphQL responses.
 * Version: 1.2.0
 * Author: Silver Assist
 * Author URI: https://silverassist.com
 * License: PolyForm-Noncommercial-1.0.0
 * License URI: https://polyformproject.org/licenses/noncommercial/1.0.0/
 * Text Domain: graphql-shortcode-support
 * Domain Path: /languages
 * Requires at least: 6.5
 * Tested up to: 6.8
 * Requires PHP: 8.2
 * Network: false
 * Update URI: https://github.com/SilverAssist/graphql-shortcode-support
 *
 * @package SilverAssist\GraphQLShortcodeSupport
 * @author  Silver Assist
 * @license PolyForm-Noncommercial-1.0.0
 * @since   1.0.0
 */

// Prevent direct access.
\defined( 'ABSPATH' ) || exit;

// Define plugin constants.
\define( 'GRAPHQL_SHORTCODE_SUPPORT_VERSION', '1.2.0' );
\define( 'GRAPHQL_SHORTCODE_SUPPORT_FILE', __FILE__ );
\define( 'GRAPHQL_SHORTCODE_SUPPORT_PATH', \plugin_dir_path( __FILE__ ) );
\define( 'GRAPHQL_SHORTCODE_SUPPORT_BASENAME', \plugin_basename( __FILE__ ) );

/**
 * Composer autoloader with security validation.
 */
$graphql_shortcode_support_autoload_path      = GRAPHQL_SHORTCODE_SUPPORT_PATH . 'vendor/autoload.php';
$graphql_shortcode_support_real_autoload_path = \realpath( $graphql_shortcode_support_autoload_path );
$graphql_shortcode_support_plugin_real_path   = \realpath( GRAPHQL_SHORTCODE_SUPPORT_PATH );

// Validate: both paths resolve, autoloader is inside plugin directory.
if (
	$graphql_shortcode_support_real_autoload_path &&
	$graphql_shortcode_support_plugin_real_path &&
	0 === \strpos( $graphql_shortcode_support_real_autoload_path, $graphql_shortcode_support_plugin_real_path )
) {
	require_once $graphql_shortcode_support_real_autoload_path;
} else {
	\add_action(
		'admin_notices',
		function () {
			printf(
				'<div class="notice notice-error"><p>%s</p></div>',
				\esc_html__( 'GraphQL Shortcode Support: Missing or invalid Composer dependencies. Run "composer install".', 'graphql-shortcode-support' )
			);
		}
	);
	return;
}

// Initialize plugin.
\add_action(
	'plugins_loaded',
	function () {
		\SilverAssist\GraphQLShortcodeSupport\Core\Plugin::instance()->init();
	}
);

// Register activation hook.
\register_activation_hook(
	__FILE__,
	function () {
		\SilverAssist\GraphQLShortcodeSupport\Core\Activator::activate();
	}
);

// Register deactivation hook.
\register_deactivation_hook(
	__FILE__,
	function () {
		\SilverAssist\GraphQLShortcodeSupport\Core\Activator::deactivate();
	}
);
