<?php
/**
 * Tests for Helpers.
 *
 * @package SilverAssist\ContentfulTables\Tests\Unit
 */

namespace SilverAssist\ContentfulTables\Tests\Unit;

use SilverAssist\ContentfulTables\Utils\Helpers;
use SilverAssist\PluginKernel\Testing\TestCase;

/**
 * @covers \SilverAssist\ContentfulTables\Utils\Helpers
 */
class HelpersTest extends TestCase {

	public function test_format_header_label_replaces_underscores(): void {
		$this->assertSame( 'Provider Name', Helpers::format_header_label( 'provider_name' ) );
	}

	public function test_format_header_label_strips_trailing_string_suffix(): void {
		$this->assertSame( 'Room', Helpers::format_header_label( 'room_string' ) );
	}

	public function test_format_header_label_replaces_hyphens(): void {
		$this->assertSame( 'Base Pricing', Helpers::format_header_label( 'base-pricing' ) );
	}

	public function test_resolve_title_placeholders_returns_unchanged_without_placeholder(): void {
		$this->assertSame( 'Plain title', Helpers::resolve_title_placeholders( 'Plain title' ) );
	}

	public function test_resolve_title_placeholders_replaces_city_state(): void {
		$post_id = static::factory()->post->create( [ 'post_name' => 'birmingham-al' ] );
		$GLOBALS['post'] = \get_post( $post_id );

		$result = Helpers::resolve_title_placeholders( 'Listings in [city-state]' );

		$this->assertSame( 'Listings in Birmingham, AL', $result );
	}

	public function test_resolve_title_placeholders_lowercase_modifier(): void {
		$post_id = static::factory()->post->create( [ 'post_name' => 'birmingham-al' ] );
		$GLOBALS['post'] = \get_post( $post_id );

		$result = Helpers::resolve_title_placeholders( 'Listings in [ city-state ] lowercase' );

		$this->assertSame( 'Listings in birmingham, al', $result );
	}

	public function test_resolve_title_placeholders_without_state_suffix(): void {
		$post_id = static::factory()->post->create( [ 'post_name' => 'nationwide' ] );
		$GLOBALS['post'] = \get_post( $post_id );

		$result = Helpers::resolve_title_placeholders( '[city-state]' );

		$this->assertSame( 'Nationwide', $result );
	}

	public function test_resolve_key_from_heading_exact_match(): void {
		$result = Helpers::resolve_key_from_heading( 'food', [ 'food', 'agency' ] );

		$this->assertSame( 'food', $result );
	}

	public function test_resolve_key_from_heading_word_match(): void {
		$result = Helpers::resolve_key_from_heading( 'best food places', [ 'food' ] );

		$this->assertSame( 'food', $result );
	}

	public function test_resolve_key_from_heading_starts_with_match(): void {
		$result = Helpers::resolve_key_from_heading( 'foodtrucks', [ 'food' ] );

		$this->assertSame( 'food', $result );
	}

	public function test_resolve_key_from_heading_returns_null_when_no_match(): void {
		$result = Helpers::resolve_key_from_heading( 'unrelated', [ 'food', 'agency' ] );

		$this->assertNull( $result );
	}

	public function test_resolve_key_from_heading_returns_heading_when_no_key_values(): void {
		$result = Helpers::resolve_key_from_heading( 'anything', [] );

		$this->assertSame( 'anything', $result );
	}

	public function test_filter_rows_by_key_single_value(): void {
		$rows = [ [ 'food', 'A' ], [ 'agency', 'B' ] ];

		$filtered = Helpers::filter_rows_by_key( $rows, 0, 'food', [ 'food', 'agency' ] );

		$this->assertSame( [ [ 'food', 'A' ] ], $filtered );
	}

	public function test_filter_rows_by_key_multiple_values(): void {
		$rows = [ [ 'food', 'A' ], [ 'agency', 'B' ], [ 'other', 'C' ] ];

		$filtered = Helpers::filter_rows_by_key( $rows, 0, 'food,agency', [ 'food', 'agency', 'other' ] );

		$this->assertCount( 2, $filtered );
	}

	public function test_filter_rows_by_key_returns_all_rows_when_filter_empty(): void {
		$rows = [ [ 'food', 'A' ], [ 'agency', 'B' ] ];

		$filtered = Helpers::filter_rows_by_key( $rows, 0, '', [ 'food', 'agency' ] );

		$this->assertSame( $rows, $filtered );
	}

	public function test_filter_rows_by_key_returns_all_rows_when_key_col_negative(): void {
		$rows = [ [ 'food', 'A' ] ];

		$filtered = Helpers::filter_rows_by_key( $rows, -1, 'food', [ 'food' ] );

		$this->assertSame( $rows, $filtered );
	}
}
