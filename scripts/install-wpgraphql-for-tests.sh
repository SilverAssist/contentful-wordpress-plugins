#!/usr/bin/env bash
#
# WPGraphQL Installation Script for Testing
#
# Downloads and installs WPGraphQL into the shared WordPress test environment
# (WP_TESTS_DIR/WP_CORE_DIR) so integration tests in any of the 3 sub-plugins
# can exercise real WPGraphQL registration/resolution instead of skipping.
# Adapted from silver-assist-security's scripts/install-wpgraphql-for-tests.sh
# (generic already — no plugin-specific naming to adjust).
#
# Usage:
#   bash scripts/install-wpgraphql-for-tests.sh
#

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

print_header() {
	echo -e "\n${BLUE}$1${NC}"
	echo "=========================================="
}

print_success() {
	echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
	echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
	echo -e "${RED}❌ $1${NC}"
}

# Get WordPress test installation directory
WP_TESTS_DIR="${WP_TESTS_DIR:-/tmp/wordpress-tests-lib}"
WP_CORE_DIR=$(dirname "${WP_TESTS_DIR}")/wordpress
WP_PLUGINS_DIR="$WP_CORE_DIR/wp-content/plugins"

print_header "WPGraphQL Test Installation"

echo "WordPress Core Dir: $WP_CORE_DIR"
echo "WordPress Plugins Dir: $WP_PLUGINS_DIR"
echo ""

if [ ! -d "$WP_CORE_DIR" ]; then
	print_error "WordPress test environment not found at: $WP_CORE_DIR"
	echo "Please run: bash scripts/install-wp-tests.sh wordpress_test root '' localhost latest"
	exit 1
fi

if [ ! -d "$WP_PLUGINS_DIR" ]; then
	print_warning "Creating plugins directory: $WP_PLUGINS_DIR"
	mkdir -p "$WP_PLUGINS_DIR"
fi

GRAPHQL_PLUGIN_DIR="$WP_PLUGINS_DIR/wp-graphql"

if [ -d "$GRAPHQL_PLUGIN_DIR" ]; then
	print_success "WPGraphQL already installed at: $GRAPHQL_PLUGIN_DIR"

	if [ -f "$GRAPHQL_PLUGIN_DIR/wp-graphql.php" ]; then
		GRAPHQL_VERSION=$(grep "Version:" "$GRAPHQL_PLUGIN_DIR/wp-graphql.php" | head -1 | sed 's/.*Version: //' | sed 's/ .*//')
		echo "Current version: $GRAPHQL_VERSION"
	fi

	echo ""

	if [[ "$FORCE_GRAPHQL_REINSTALL" == "true" ]] || [[ "$CI" == "true" ]] || [[ "$GITHUB_ACTIONS" == "true" ]] || [[ "$CONTINUOUS_INTEGRATION" == "true" ]]; then
		print_warning "Non-interactive mode: Automatically reinstalling WPGraphQL"
		REPLY="y"
	else
		read -p "Do you want to reinstall WPGraphQL? (y/N): " -n 1 -r
		echo
	fi

	if [[ ! $REPLY =~ ^[Yy]$ ]]; then
		print_success "Using existing WPGraphQL installation"
		exit 0
	fi

	print_warning "Removing existing WPGraphQL..."
	rm -rf "$GRAPHQL_PLUGIN_DIR"
fi

print_header "Downloading WPGraphQL"

TEMP_DIR=$(mktemp -d)
GRAPHQL_ZIP="$TEMP_DIR/wp-graphql.zip"

echo "Downloading from WordPress.org repository..."
if command -v curl >/dev/null 2>&1; then
	curl -L "https://downloads.wordpress.org/plugin/wp-graphql.latest-stable.zip" -o "$GRAPHQL_ZIP"
elif command -v wget >/dev/null 2>&1; then
	wget "https://downloads.wordpress.org/plugin/wp-graphql.latest-stable.zip" -O "$GRAPHQL_ZIP"
else
	print_error "Neither curl nor wget found. Please install one of them."
	exit 1
fi

if [ ! -f "$GRAPHQL_ZIP" ] || [ ! -s "$GRAPHQL_ZIP" ]; then
	print_error "Failed to download WPGraphQL"
	exit 1
fi

print_success "WPGraphQL downloaded successfully"

print_header "Installing WPGraphQL"

cd "$WP_PLUGINS_DIR"
if command -v unzip >/dev/null 2>&1; then
	unzip -qo "$GRAPHQL_ZIP"
else
	print_error "unzip command not found. Please install unzip."
	exit 1
fi

if [ ! -d "$GRAPHQL_PLUGIN_DIR" ]; then
	print_error "WPGraphQL extraction failed"
	exit 1
fi

if [ ! -f "$GRAPHQL_PLUGIN_DIR/wp-graphql.php" ]; then
	print_error "WPGraphQL main file not found"
	exit 1
fi

GRAPHQL_VERSION=$(grep "Version:" "$GRAPHQL_PLUGIN_DIR/wp-graphql.php" | head -1 | sed 's/.*Version: //' | sed 's/ .*//')

print_success "WPGraphQL v$GRAPHQL_VERSION installed successfully"

rm -rf "$TEMP_DIR"

print_header "Verifying Installation"

if [ -f "$GRAPHQL_PLUGIN_DIR/wp-graphql.php" ]; then
	print_success "Main plugin file: wp-graphql.php"
fi

GRAPHQL_FILES=(
	"access-functions.php"
	"constants.php"
	"activation.php"
	"deactivation.php"
	"src/AppContext.php"
	"src/Registry/TypeRegistry.php"
	"src/Request.php"
)

for check_file in "${GRAPHQL_FILES[@]}"; do
	if [ -f "$GRAPHQL_PLUGIN_DIR/$check_file" ]; then
		print_success "Found: $check_file"
	else
		print_warning "Missing: $check_file"
	fi
done

print_header "Integration Test Setup"

GRAPHQL_TEST_HELPER="$GRAPHQL_PLUGIN_DIR/graphql-test-helper.php"
cat >"$GRAPHQL_TEST_HELPER" <<'HELPEREOF'
<?php
/**
 * WPGraphQL Test Helper
 *
 * Helper functions to load WPGraphQL in WordPress test environment
 */

// Ensure WPGraphQL is loaded for tests
if ( ! defined( 'WPGRAPHQL_PLUGIN_DIR' ) ) {
	define( 'WPGRAPHQL_PLUGIN_DIR', __DIR__ . '/' );
}

if ( ! defined( 'WPGRAPHQL_PLUGIN_URL' ) ) {
	define( 'WPGRAPHQL_PLUGIN_URL', plugins_url( '/', __FILE__ ) );
}

// Load WPGraphQL main file if not already loaded
if ( ! class_exists( 'WPGraphQL' ) ) {
	require_once __DIR__ . '/wp-graphql.php';
}

// Initialize WPGraphQL for testing
if ( ! did_action( 'graphql_init' ) ) {
	do_action( 'graphql_init' );
}

/**
 * Helper function to execute a GraphQL query in the test environment
 *
 * @param string $query     The GraphQL query string.
 * @param array  $variables Optional query variables.
 * @return array The GraphQL response.
 */
function execute_test_graphql_query( $query, $variables = array() ) {
	if ( ! function_exists( 'graphql' ) ) {
		return array( 'errors' => array( array( 'message' => 'WPGraphQL not available' ) ) );
	}

	return graphql(
		array(
			'query'     => $query,
			'variables' => $variables,
		)
	);
}

/**
 * Helper function to execute an introspection query
 *
 * @return array The introspection response.
 */
function execute_test_introspection_query() {
	$query = '
		query IntrospectionQuery {
			__schema {
				queryType { name }
				mutationType { name }
				types {
					name
					kind
				}
			}
		}
	';

	return execute_test_graphql_query( $query );
}
HELPEREOF

print_success "Created WPGraphQL test helper: graphql-test-helper.php"

print_header "Installation Complete"

echo "WPGraphQL Installation Summary:"
echo "- Version: $GRAPHQL_VERSION"
echo "- Location: $GRAPHQL_PLUGIN_DIR"
echo "- Test Helper: $GRAPHQL_TEST_HELPER"
echo ""

print_success "WPGraphQL ready for integration testing! 🚀"
