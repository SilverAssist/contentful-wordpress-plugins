<?php
/**
 * Tests for ChartRenderer.
 *
 * @package SilverAssist\ContentfulTables\Tests\Unit
 */

namespace SilverAssist\ContentfulTables\Tests\Unit;

use SilverAssist\ContentfulTables\View\ChartRenderer;
use SilverAssist\PluginKernel\Testing\TestCase;

/**
 * @covers \SilverAssist\ContentfulTables\View\ChartRenderer
 */
class ChartRendererTest extends TestCase {

	public function test_render_table_source_produces_table(): void {
		$html = ChartRenderer::render(
			[
				'title'  => 'My Chart',
				'source' => [ 'type' => 'table', 'dataTable' => [ 'tableData' => [ [ 'Month', 'Sales' ], [ 'Jan', '100' ] ] ] ],
			],
			'chart-1',
			[]
		);

		$this->assertStringContainsString( 'chart-title', $html );
		$this->assertStringContainsString( 'My Chart', $html );
		$this->assertStringContainsString( '<th>Month</th>', $html );
		$this->assertStringContainsString( '<td>Jan</td>', $html );
	}

	public function test_render_applies_numeric_label_prefix(): void {
		$html = ChartRenderer::render(
			[
				'source'      => [ 'type' => 'table', 'dataTable' => [ 'tableData' => [ [ 'Month', 'Sales' ], [ 'Jan', '1000' ] ] ] ],
				'labelPrefix' => '$',
			],
			'chart-2',
			[]
		);

		$this->assertStringContainsString( '$1,000', $html );
	}

	public function test_render_spreadsheet_source_shows_download_link(): void {
		$html = ChartRenderer::render(
			[ 'source' => [ 'type' => 'spreadsheet', 'url' => 'https://example.test/data.csv' ] ],
			'chart-3',
			[]
		);

		$this->assertStringContainsString( 'https://example.test/data.csv', $html );
	}

	public function test_render_shows_placeholder_when_no_chart_data(): void {
		$html = ChartRenderer::render( null, 'chart-4', [ 'type' => 'bar' ] );

		$this->assertStringContainsString( 'chart-placeholder', $html );
		$this->assertStringContainsString( 'bar', $html );
	}

	public function test_render_custom_title_overrides_data_title(): void {
		$html = ChartRenderer::render( [ 'title' => 'Data Title' ], 'chart-5', [ 'title' => 'Custom Title' ] );

		$this->assertStringContainsString( 'Custom Title', $html );
		$this->assertStringNotContainsString( 'Data Title', $html );
	}
}
