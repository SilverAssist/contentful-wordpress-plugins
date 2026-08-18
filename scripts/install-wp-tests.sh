#!/usr/bin/env bash
#
# Thin wrapper around the vendorized WordPress test-suite installer shipped by
# silverassist/wp-coding-standards. All 3 sub-plugins depend on it (see each
# composer.json's require-dev) and vendor an identical copy, so this repo-root
# wrapper just invokes community-listings' copy — the WP_TESTS_DIR/WP_CORE_DIR
# it installs is shared across all 3 sub-plugins.
#
# Usage:
#   bash scripts/install-wp-tests.sh <db-name> <db-user> <db-pass> [db-host] [wp-version] [skip-database-creation]
#

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
VENDORED_SCRIPT="$SCRIPT_DIR/../community-listings/vendor/silverassist/wp-coding-standards/scripts/install-wp-tests.sh"

if [ ! -f "$VENDORED_SCRIPT" ]; then
	echo "❌ Vendored install-wp-tests.sh not found at: $VENDORED_SCRIPT" >&2
	echo "   Run 'make install-dev' first." >&2
	exit 1
fi

exec bash "$VENDORED_SCRIPT" "$@"
