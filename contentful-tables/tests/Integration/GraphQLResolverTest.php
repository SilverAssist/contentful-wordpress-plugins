<?php
/**
 * Integration tests for GraphQLResolver.
 *
 * @package SilverAssist\ContentfulTables\Tests\Integration
 */

namespace SilverAssist\ContentfulTables\Tests\Integration;

use SilverAssist\ContentfulTables\Service\GraphQLResolver;
use SilverAssist\PluginKernel\Testing\TestCase;

/**
 * @covers \SilverAssist\ContentfulTables\Service\GraphQLResolver
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

	public function test_resolve_shortcodes_applies_to_post_page_and_community(): void {
		\add_shortcode( 'ct_test_shortcode', fn() => 'RENDERED' );
		$raw = 'Text [ct_test_shortcode] here';

		foreach ( [ 'Post', 'Page', 'Community' ] as $type ) {
			$result = $this->resolver->resolve_shortcodes( $raw, null, [], null, null, $type, 'content', null, null );
			$this->assertSame( 'Text RENDERED here', $result, "Type {$type} should have shortcodes processed." );
		}

		\remove_shortcode( 'ct_test_shortcode' );
	}

	public function test_resolve_shortcodes_ignores_other_types(): void {
		$raw = 'Text [ct_test_shortcode] here';

		$result = $this->resolver->resolve_shortcodes( $raw, null, [], null, null, 'CustomType', 'content', null, null );

		$this->assertSame( $raw, $result );
	}

	public function test_resolve_shortcodes_ignores_fields_other_than_content_and_excerpt(): void {
		$raw = 'Text [ct_test_shortcode] here';

		$result = $this->resolver->resolve_shortcodes( $raw, null, [], null, null, 'Post', 'title', null, null );

		$this->assertSame( $raw, $result );
	}

	public function test_resolve_shortcodes_skips_content_without_brackets(): void {
		$plain = 'No shortcodes here.';

		$result = $this->resolver->resolve_shortcodes( $plain, null, [], null, null, 'Post', 'content', null, null );

		$this->assertSame( $plain, $result );
	}

	public function test_resolve_shortcodes_ignores_non_string_result(): void {
		$result = $this->resolver->resolve_shortcodes( 42, null, [], null, null, 'Post', 'content', null, null );

		$this->assertSame( 42, $result );
	}
}
