<?php
/**
 * Integration tests for GraphQLShortcodeResolver.
 *
 * @package SilverAssist\GraphQLShortcodeSupport\Tests\Integration
 */

namespace SilverAssist\GraphQLShortcodeSupport\Tests\Integration;

use SilverAssist\GraphQLShortcodeSupport\Service\GraphQLShortcodeResolver;
use SilverAssist\PluginKernel\Testing\TestCase;

/**
 * @covers \SilverAssist\GraphQLShortcodeSupport\Service\GraphQLShortcodeResolver
 */
class GraphQLShortcodeResolverTest extends TestCase {

	private GraphQLShortcodeResolver $resolver;

	protected function setUp(): void {
		parent::setUp();

		if ( ! class_exists( 'WPGraphQL' ) ) {
			$this->markTestSkipped( 'WPGraphQL plugin not available in the test environment.' );
		}

		\delete_option( 'gss_settings' );
		$this->resolver = GraphQLShortcodeResolver::instance();
	}

	protected function tearDown(): void {
		\delete_option( 'gss_settings' );
		parent::tearDown();
	}

	public function test_should_load_matches_wpgraphql_availability(): void {
		$this->assertSame( class_exists( 'WPGraphQL' ), $this->resolver->should_load() );
	}

	public function test_init_registers_graphql_register_types_action(): void {
		$this->resolver->init();

		$this->assertNotFalse( \has_action( 'graphql_register_types', [ $this->resolver, 'register_rendered_content_field' ] ) );
	}

	public function test_init_registers_resolve_filter_when_enabled(): void {
		\update_option( 'gss_settings', [ 'enabled' => true, 'post_types' => [ 'post' ], 'fields' => [ 'content' ] ] );

		$resolver = GraphQLShortcodeResolver::instance();
		$resolver->init();

		$this->assertNotFalse( \has_filter( 'graphql_resolve_field', [ $resolver, 'resolve_shortcodes_in_field' ] ) );
	}

	public function test_resolve_shortcodes_in_field_ignores_non_string_result(): void {
		\update_option( 'gss_settings', [ 'enabled' => true, 'post_types' => [ 'post' ], 'fields' => [ 'content' ] ] );
		$resolver = GraphQLShortcodeResolver::instance();
		$resolver->init();

		$result = $resolver->resolve_shortcodes_in_field( 123, null, null, null, null, 'Post', 'content', null, null );

		$this->assertSame( 123, $result );
	}

	public function test_resolve_shortcodes_in_field_ignores_field_not_in_configured_list(): void {
		\update_option( 'gss_settings', [ 'enabled' => true, 'post_types' => [ 'post' ], 'fields' => [ 'content' ] ] );
		$resolver = GraphQLShortcodeResolver::instance();
		$resolver->init();

		$raw = 'Hello [test_shortcode]';
		$result = $resolver->resolve_shortcodes_in_field( $raw, null, null, null, null, 'Post', 'excerpt', null, null );

		$this->assertSame( $raw, $result, 'excerpt is not in the configured fields list, so it must pass through unchanged.' );
	}

	public function test_resolve_shortcodes_in_field_processes_registered_shortcode_via_generic_fallback(): void {
		\add_shortcode( 'gss_test_shortcode', fn() => 'RENDERED' );
		\update_option( 'gss_settings', [ 'enabled' => true, 'post_types' => [ 'post' ], 'fields' => [ 'content' ] ] );
		$resolver = GraphQLShortcodeResolver::instance();
		$resolver->init();

		$result = $resolver->resolve_shortcodes_in_field( 'Before [gss_test_shortcode] After', null, null, null, null, 'Post', 'content', null, null );

		$this->assertSame( 'Before RENDERED After', $result );

		\remove_shortcode( 'gss_test_shortcode' );
	}

	public function test_resolve_shortcodes_in_field_skips_plain_text_without_brackets(): void {
		\update_option( 'gss_settings', [ 'enabled' => true, 'post_types' => [ 'post' ], 'fields' => [ 'content' ] ] );
		$resolver = GraphQLShortcodeResolver::instance();
		$resolver->init();

		$plain = 'Just plain text, no shortcodes here.';
		$result = $resolver->resolve_shortcodes_in_field( $plain, null, null, null, null, 'Post', 'content', null, null );

		$this->assertSame( $plain, $result );
	}

	public function test_register_rendered_content_field_builds_queryable_schema(): void {
		\update_option( 'gss_settings', [ 'enabled' => true, 'post_types' => [ 'post', 'page' ], 'fields' => [ 'content' ] ] );
		$resolver = GraphQLShortcodeResolver::instance();

		$resolver->register_rendered_content_field();

		// A plain data query (not introspection, which WPGraphQL blocks for public
		// requests by default) is enough to force a real schema build and confirm
		// the field registration didn't leave the schema broken.
		$result = \graphql( [ 'query' => '{ posts { nodes { id } } }' ] );

		$this->assertArrayNotHasKey( 'errors', $result, 'Schema must build successfully after register_rendered_content_field() runs.' );
	}
}
