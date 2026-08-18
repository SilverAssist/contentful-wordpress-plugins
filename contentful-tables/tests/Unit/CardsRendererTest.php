<?php
/**
 * Tests for CardsRenderer.
 *
 * @package SilverAssist\ContentfulTables\Tests\Unit
 */

namespace SilverAssist\ContentfulTables\Tests\Unit;

use SilverAssist\ContentfulTables\Service\TableDataLoader;
use SilverAssist\ContentfulTables\View\CardsRenderer;
use SilverAssist\PluginKernel\Testing\TestCase;

/**
 * @covers \SilverAssist\ContentfulTables\View\CardsRenderer
 */
class CardsRendererTest extends TestCase {

	private TableDataLoader $loader;

	protected function setUp(): void {
		parent::setUp();
		$this->loader = TableDataLoader::instance();
	}

	public function test_render_card_grid_from_raw_data(): void {
		$html = CardsRenderer::render(
			[ 'rawData' => [ [ 'name', 'phone' ], [ 'Acme Home Care', '555-1234' ] ] ],
			'card-1',
			[],
			$this->loader
		);

		$this->assertStringContainsString( 'contentful-card', $html );
		$this->assertStringContainsString( 'Acme Home Care', $html );
		$this->assertStringContainsString( '555-1234', $html );
	}

	public function test_render_hides_key_column_by_default(): void {
		$html = CardsRenderer::render(
			[ 'rawData' => [ [ 'key', 'name' ], [ 'food', 'Acme Food' ] ] ],
			'card-2',
			[],
			$this->loader
		);

		$this->assertStringNotContainsString( 'card-label">Key', $html );
	}

	public function test_render_filters_rows_by_explicit_key(): void {
		$html = CardsRenderer::render(
			[ 'rawData' => [ [ 'key', 'name' ], [ 'food', 'Acme Food' ], [ 'agency', 'Acme Agency' ] ] ],
			'card-3',
			[ 'filters' => 'food' ],
			$this->loader
		);

		$this->assertStringContainsString( 'Acme Food', $html );
		$this->assertStringNotContainsString( 'Acme Agency', $html );
	}

	public function test_render_shows_placeholder_when_no_card_data(): void {
		$html = CardsRenderer::render( null, 'card-4', [], $this->loader );

		$this->assertStringContainsString( 'cards-placeholder', $html );
	}

	public function test_render_shows_no_listings_message_when_filter_matches_nothing(): void {
		$html = CardsRenderer::render(
			[ 'rawData' => [ [ 'key', 'name' ], [ 'food', 'Acme Food' ] ] ],
			'card-5',
			[ 'filters' => 'nonexistent' ],
			$this->loader
		);

		$this->assertStringContainsString( 'No listings found.', $html );
	}

	public function test_render_uses_selected_columns_when_configured(): void {
		$html = CardsRenderer::render(
			[
				'rawData' => [ [ 'name', 'phone', 'address' ], [ 'Acme', '555-1234', '123 Main St' ] ],
				'filters' => [ 'selectedColumns' => [ [ 'name' => 'name' ] ] ],
			],
			'card-6',
			[],
			$this->loader
		);

		$this->assertStringContainsString( 'Acme', $html );
		$this->assertStringNotContainsString( '555-1234', $html );
	}

	public function test_render_resolves_title_placeholder(): void {
		$post_id         = static::factory()->post->create( [ 'post_name' => 'birmingham-al' ] );
		$GLOBALS['post'] = \get_post( $post_id );

		$html = CardsRenderer::render(
			[ 'title' => 'Listings in [city-state]', 'rawData' => [ [ 'a' ], [ '1' ] ] ],
			'card-7',
			[],
			$this->loader
		);

		$this->assertStringContainsString( 'Listings in Birmingham, AL', $html );
	}
}
