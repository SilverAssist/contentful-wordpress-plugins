<?php
/**
 * Integration tests for GraphQLResolver.
 *
 * @package SilverAssist\CommunityListings\Tests\Integration
 */

namespace SilverAssist\CommunityListings\Tests\Integration;

use SilverAssist\CommunityListings\Service\GraphQLResolver;
use SilverAssist\PluginKernel\Testing\TestCase;

/**
 * @covers \SilverAssist\CommunityListings\Service\GraphQLResolver
 */
class GraphQLResolverTest extends TestCase {

	private GraphQLResolver $resolver;

	protected function setUp(): void {
		parent::setUp();

		if ( ! \class_exists( 'WPGraphQL' ) ) {
			$this->markTestSkipped( 'WPGraphQL plugin not available in the test environment.' );
		}

		$this->resolver = GraphQLResolver::instance();
	}

	public function test_init_registers_resolve_field_filter(): void {
		$this->resolver->init();

		$this->assertNotFalse( \has_filter( 'graphql_resolve_field', [ $this->resolver, 'resolve_shortcodes' ] ) );
	}

	public function test_init_registers_graphql_register_types_action(): void {
		$this->resolver->init();

		$this->assertNotFalse( \has_action( 'graphql_register_types', [ $this->resolver, 'register_rendered_content' ] ) );
	}

	public function test_resolve_shortcodes_only_applies_to_community_content_and_excerpt(): void {
		\add_shortcode( 'cl_test_shortcode', fn() => 'RENDERED' );

		$raw = 'Text [cl_test_shortcode] here';

		$community_content = $this->resolver->resolve_shortcodes( $raw, null, [], null, null, 'Community', 'content', null, null );
		$this->assertSame( 'Text RENDERED here', $community_content );

		$other_type = $this->resolver->resolve_shortcodes( $raw, null, [], null, null, 'Post', 'content', null, null );
		$this->assertSame( $raw, $other_type, 'Non-Community types must not have shortcodes processed.' );

		$other_field = $this->resolver->resolve_shortcodes( $raw, null, [], null, null, 'Community', 'title', null, null );
		$this->assertSame( $raw, $other_field, 'Fields other than content/excerpt must not have shortcodes processed.' );

		\remove_shortcode( 'cl_test_shortcode' );
	}

	public function test_resolve_shortcodes_skips_content_without_brackets(): void {
		$plain = 'No shortcodes in this text.';

		$result = $this->resolver->resolve_shortcodes( $plain, null, [], null, null, 'Community', 'content', null, null );

		$this->assertSame( $plain, $result );
	}

	public function test_register_rendered_content_skips_when_graphql_shortcode_support_present(): void {
		if ( ! \class_exists( \SilverAssist\GraphQLShortcodeSupport\Core\Plugin::class ) ) {
			$this->markTestSkipped( 'graphql-shortcode-support is not loaded in this test run; duplicate-field guard is not exercised here.' );
		}

		// The method returns early via class_exists() before calling register_graphql_field() —
		// this just documents/exercises that early-return path without a fatal. Calling
		// register_graphql_field() unconditionally here (the "absent" branch) is deliberately
		// NOT exercised as a second test: WPGraphQL's TypeRegistry treats a repeated direct
		// call as a real duplicate field registration (DUPLICATE_FIELD), so it can only safely
		// run once per process — the hook-registration assertion above already covers that
		// the callback is wired correctly for the natural single schema-build call.
		$this->resolver->register_rendered_content();

		$this->assertTrue( true );
	}
}
