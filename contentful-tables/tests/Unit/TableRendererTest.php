<?php
/**
 * Tests for TableRenderer.
 *
 * @package SilverAssist\ContentfulTables\Tests\Unit
 */

namespace SilverAssist\ContentfulTables\Tests\Unit;

use SilverAssist\ContentfulTables\View\TableRenderer;
use SilverAssist\PluginKernel\Testing\TestCase;

/**
 * @covers \SilverAssist\ContentfulTables\View\TableRenderer
 */
class TableRendererTest extends TestCase {

	public function test_render_raw_data_format_produces_table(): void {
		$html = TableRenderer::render(
			[ 'rawData' => [ [ 'Name', 'Price' ], [ 'Widget', '$10' ] ] ],
			'table-1'
		);

		$this->assertStringContainsString( '<table class="contentful-table">', $html );
		$this->assertStringContainsString( '<th>Name</th>', $html );
		$this->assertStringContainsString( '<td>Widget</td>', $html );
	}

	public function test_render_hides_key_column_from_display(): void {
		$html = TableRenderer::render(
			[
				'rawData'   => [ [ 'key', 'Name' ], [ 'food', 'Acme Food' ] ],
				'keyColumn' => 'key',
			],
			'table-2'
		);

		$this->assertStringNotContainsString( '<th>key</th>', $html );
		$this->assertStringNotContainsString( '<td>food</td>', $html );
		$this->assertStringContainsString( '<td>Acme Food</td>', $html );
	}

	public function test_render_applies_key_filter(): void {
		$html = TableRenderer::render(
			[
				'rawData'   => [ [ 'key', 'Name' ], [ 'food', 'Acme Food' ], [ 'agency', 'Acme Agency' ] ],
				'keyColumn' => 'key',
				'keyValues' => [ 'food', 'agency' ],
			],
			'table-3',
			'food'
		);

		$this->assertStringContainsString( 'Acme Food', $html );
		$this->assertStringNotContainsString( 'Acme Agency', $html );
	}

	public function test_render_legacy_headers_rows_format(): void {
		$html = TableRenderer::render(
			[ 'headers' => [ 'A', 'B' ], 'rows' => [ [ '1', '2' ] ] ],
			'table-4'
		);

		$this->assertStringContainsString( '<th>A</th>', $html );
		$this->assertStringContainsString( '<td>1</td>', $html );
	}

	public function test_render_shows_placeholder_when_no_recognized_format(): void {
		$html = TableRenderer::render( [], 'table-5' );

		$this->assertStringContainsString( 'No table data available.', $html );
	}

	public function test_render_shows_title_when_no_key_filter(): void {
		$html = TableRenderer::render( [ 'title' => 'My Title', 'rawData' => [ [ 'a' ], [ '1' ] ] ], 'table-6' );

		$this->assertStringContainsString( '<h3>My Title</h3>', $html );
	}

	public function test_render_hides_title_when_key_filter_present(): void {
		$html = TableRenderer::render(
			[ 'title' => 'My Title', 'rawData' => [ [ 'a' ], [ '1' ] ] ],
			'table-7',
			'some-filter'
		);

		$this->assertStringNotContainsString( '<h3>My Title</h3>', $html );
	}
}
