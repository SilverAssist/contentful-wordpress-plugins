<?php
/**
 * Tests for CptRegistrar (registration behavior, no WPGraphQL required).
 *
 * @package SilverAssist\CommunityListings\Tests\Unit
 */

namespace SilverAssist\CommunityListings\Tests\Unit;

use SilverAssist\CommunityListings\Service\CptRegistrar;
use SilverAssist\PluginKernel\Testing\TestCase;

/**
 * @covers \SilverAssist\CommunityListings\Service\CptRegistrar
 */
class CptRegistrarTest extends TestCase {

	private const EXPECTED_META_KEYS = [
		'contentful_id',
		'listing_type',
		'state_short',
		'state_long',
		'original_slug',
		'original_url',
		'content_bucket',
		'sitemap_group',
		'link_text',
		'hero_text_contrast',
		'noindex',
		'nofollow',
		'provider_listings',
	];

	public function test_get_priority_is_ten(): void {
		$this->assertSame( 10, CptRegistrar::instance()->get_priority() );
	}

	public function test_should_load_is_true(): void {
		$this->assertTrue( CptRegistrar::instance()->should_load() );
	}

	public function test_register_post_type_sets_expected_args(): void {
		CptRegistrar::register_post_type();

		$this->assertTrue( \post_type_exists( 'community' ) );

		$object = \get_post_type_object( 'community' );
		$this->assertTrue( $object->hierarchical );
		$this->assertTrue( $object->show_in_graphql );
		$this->assertSame( 'community', $object->graphql_single_name );
		$this->assertSame( 'communities', $object->graphql_plural_name );
		$this->assertSame( 'community', $object->rest_base );
	}

	public function test_register_meta_registers_all_thirteen_fields(): void {
		CptRegistrar::register_post_type();
		CptRegistrar::instance()->register_meta();

		$registered = \get_registered_meta_keys( 'post', 'community' );

		foreach ( self::EXPECTED_META_KEYS as $key ) {
			$this->assertArrayHasKey( $key, $registered, "Meta key '{$key}' should be registered for the community post type." );
		}
	}

	public function test_register_meta_boolean_fields_have_boolean_type(): void {
		CptRegistrar::register_post_type();
		CptRegistrar::instance()->register_meta();

		$registered = \get_registered_meta_keys( 'post', 'community' );

		foreach ( [ 'hero_text_contrast', 'noindex', 'nofollow' ] as $key ) {
			$this->assertSame( 'boolean', $registered[ $key ]['type'] );
		}
	}

	public function test_register_meta_string_fields_have_string_type(): void {
		CptRegistrar::register_post_type();
		CptRegistrar::instance()->register_meta();

		$registered = \get_registered_meta_keys( 'post', 'community' );

		$this->assertSame( 'string', $registered['state_short']['type'] );
		$this->assertSame( 'string', $registered['contentful_id']['type'] );
	}
}
