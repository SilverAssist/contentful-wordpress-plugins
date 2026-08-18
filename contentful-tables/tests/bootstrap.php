<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package SilverAssist\ContentfulTables\Tests
 */

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

$_phpunit_polyfills_path = dirname( __DIR__ ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';
if ( file_exists( $_phpunit_polyfills_path ) ) {
	require_once $_phpunit_polyfills_path;
}

if ( ! file_exists( "{$_tests_dir}/includes/functions.php" ) ) {
	echo "\n❌ Could not find WordPress test suite at: {$_tests_dir}/includes/functions.php\n\n";
	echo "📋 Run the following command to install the WordPress test suite:\n";
	echo "   bash ../scripts/install-wp-tests.sh <db-name> <db-user> <db-pass> [db-host] [wp-version]\n\n";
	echo "ℹ️  Or set WP_TESTS_DIR to an existing WordPress test installation.\n\n";
	exit( 1 );
}

require_once "{$_tests_dir}/includes/functions.php";

/**
 * Manually load the plugin being tested and its dependencies.
 *
 * @return void
 */
function _manually_load_plugin() {
	// Load WPGraphQL if the shared test environment has it installed.
	if ( defined( 'WP_PLUGIN_DIR' ) && file_exists( WP_PLUGIN_DIR . '/wp-graphql/wp-graphql.php' ) ) {
		require_once WP_PLUGIN_DIR . '/wp-graphql/wp-graphql.php';
	}

	require dirname( __DIR__ ) . '/contentful-tables.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require "{$_tests_dir}/includes/bootstrap.php";
