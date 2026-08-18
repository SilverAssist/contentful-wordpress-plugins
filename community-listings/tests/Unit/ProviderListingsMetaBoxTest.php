<?php
/**
 * Tests for ProviderListingsMetaBox.
 *
 * @package SilverAssist\CommunityListings\Tests\Unit
 */

namespace SilverAssist\CommunityListings\Tests\Unit;

use SilverAssist\CommunityListings\Admin\ProviderListingsMetaBox;
use SilverAssist\PluginKernel\Testing\TestCase;

/**
 * @covers \SilverAssist\CommunityListings\Admin\ProviderListingsMetaBox
 */
class ProviderListingsMetaBoxTest extends TestCase {

	private const NONCE_ACTION = 'provider_listings_meta_box_action';
	private const NONCE_FIELD  = 'provider_listings_meta_box_nonce';
	private const FIELD_NAME   = 'provider_listings_json';

	private ProviderListingsMetaBox $meta_box;

	protected function setUp(): void {
		parent::setUp();

		$this->meta_box = ProviderListingsMetaBox::instance();

		$admin_id = static::factory()->user->create( [ 'role' => 'administrator' ] );
		\wp_set_current_user( $admin_id );
	}

	protected function tearDown(): void {
		$_POST = [];
		$_GET  = [];
		global $wp_meta_boxes;
		$wp_meta_boxes = [];
		parent::tearDown();
	}

	private function create_community_post( string $listing_type ): int {
		$post_id = static::factory()->post->create( [ 'post_type' => 'community' ] );
		\update_post_meta( $post_id, 'listing_type', $listing_type );
		return $post_id;
	}

	public function test_get_priority_is_twenty_five(): void {
		$this->assertSame( 25, $this->meta_box->get_priority() );
	}

	public function test_should_load_is_true(): void {
		$this->assertTrue( $this->meta_box->should_load() );
	}

	public function test_register_meta_box_registers_for_city_listing(): void {
		$post_id = $this->create_community_post( 'city' );

		$this->meta_box->register_meta_box( \get_post( $post_id ) );

		global $wp_meta_boxes;
		$this->assertArrayHasKey( 'provider-listings-meta-box', $wp_meta_boxes['community']['normal']['high'] ?? [] );
	}

	public function test_register_meta_box_skips_non_city_listing(): void {
		$post_id = $this->create_community_post( 'state' );

		$this->meta_box->register_meta_box( \get_post( $post_id ) );

		global $wp_meta_boxes;
		$this->assertArrayNotHasKey( 'provider-listings-meta-box', $wp_meta_boxes['community']['normal']['high'] ?? [] );
	}

	public function test_save_provider_listings_rejects_invalid_json(): void {
		$post_id = $this->create_community_post( 'city' );

		$_POST[ self::NONCE_FIELD ] = \wp_create_nonce( self::NONCE_ACTION );
		$_POST[ self::FIELD_NAME ]  = '{not valid json';

		$this->meta_box->save_provider_listings( $post_id, \get_post( $post_id ), true );

		$this->assertSame( '', \get_post_meta( $post_id, 'provider_listings', true ) );
		$this->assertNotFalse( \has_filter( 'redirect_post_location', [ $this->meta_box, 'append_invalid_json_query_arg' ] ) );
	}

	public function test_save_provider_listings_rejects_invalid_nonce(): void {
		$post_id = $this->create_community_post( 'city' );

		$_POST[ self::NONCE_FIELD ] = 'not-a-real-nonce';
		$_POST[ self::FIELD_NAME ]  = '[{"name":"Acme Home Care"}]';

		$this->meta_box->save_provider_listings( $post_id, \get_post( $post_id ), true );

		$this->assertSame( '', \get_post_meta( $post_id, 'provider_listings', true ) );
	}

	public function test_save_provider_listings_skips_non_city_posts(): void {
		$post_id = $this->create_community_post( 'state' );

		$_POST[ self::NONCE_FIELD ] = \wp_create_nonce( self::NONCE_ACTION );
		$_POST[ self::FIELD_NAME ]  = '[{"name":"Acme Home Care"}]';

		$this->meta_box->save_provider_listings( $post_id, \get_post( $post_id ), true );

		$this->assertSame( '', \get_post_meta( $post_id, 'provider_listings', true ) );
	}

	/**
	 * Regression test for the PR #2 fix: the direct $wpdb->update() write bypasses
	 * update_post_meta()'s own cache invalidation, so save_provider_listings() must
	 * call wp_cache_delete() itself — otherwise a subsequent get_post_meta() call
	 * within the same request keeps serving the pre-save cached value.
	 */
	public function test_save_provider_listings_invalidates_meta_cache(): void {
		$post_id = $this->create_community_post( 'city' );

		\update_post_meta( $post_id, 'provider_listings', '[{"name":"Old Provider"}]' );

		// Prime the object cache for this post's meta.
		$primed = \get_post_meta( $post_id, 'provider_listings', true );
		$this->assertSame( '[{"name":"Old Provider"}]', $primed );

		$new_json = '[{"name":"New Provider"}]';
		$_POST[ self::NONCE_FIELD ] = \wp_create_nonce( self::NONCE_ACTION );
		$_POST[ self::FIELD_NAME ]  = $new_json;

		$this->meta_box->save_provider_listings( $post_id, \get_post( $post_id ), true );

		$this->assertSame(
			$new_json,
			\get_post_meta( $post_id, 'provider_listings', true ),
			'get_post_meta() must reflect the direct DB write immediately — a stale cache read here means wp_cache_delete() regressed.'
		);
	}

	public function test_save_provider_listings_inserts_when_meta_row_missing(): void {
		$post_id = $this->create_community_post( 'city' );
		// No prior provider_listings meta row exists for this post.

		$new_json = '[{"name":"Brand New Provider"}]';
		$_POST[ self::NONCE_FIELD ] = \wp_create_nonce( self::NONCE_ACTION );
		$_POST[ self::FIELD_NAME ]  = $new_json;

		$this->meta_box->save_provider_listings( $post_id, \get_post( $post_id ), true );

		$this->assertSame( $new_json, \get_post_meta( $post_id, 'provider_listings', true ) );
	}

	public function test_append_invalid_json_query_arg_adds_flag_and_removes_itself(): void {
		\add_filter( 'redirect_post_location', [ $this->meta_box, 'append_invalid_json_query_arg' ] );

		$result = $this->meta_box->append_invalid_json_query_arg( 'https://example.test/wp-admin/post.php' );

		$this->assertStringContainsString( 'provider_listings_json_invalid=1', $result );
		$this->assertFalse( \has_filter( 'redirect_post_location', [ $this->meta_box, 'append_invalid_json_query_arg' ] ) );
	}
}
