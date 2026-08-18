<?php
/**
 * Tests for TocRenderer.
 *
 * @package SilverAssist\ContentfulTables\Tests\Unit
 */

namespace SilverAssist\ContentfulTables\Tests\Unit;

use SilverAssist\ContentfulTables\View\TocRenderer;
use SilverAssist\PluginKernel\Testing\TestCase;

/**
 * @covers \SilverAssist\ContentfulTables\View\TocRenderer
 */
class TocRendererTest extends TestCase {

	public function test_render_static_items(): void {
		$html = TocRenderer::render(
			[ 'items' => [ [ 'text' => 'Section One', 'anchor' => 'section-one' ] ] ],
			'toc-1'
		);

		$this->assertStringContainsString( '<ul class="toc-list">', $html );
		$this->assertStringContainsString( 'href="#section-one"', $html );
		$this->assertStringContainsString( 'Section One', $html );
	}

	public function test_render_static_item_defaults_anchor_from_text(): void {
		$html = TocRenderer::render( [ 'items' => [ [ 'text' => 'My Section' ] ] ], 'toc-2' );

		$this->assertStringContainsString( 'href="#my-section"', $html );
	}

	public function test_render_dynamic_toc_when_no_items(): void {
		$html = TocRenderer::render( [], 'toc-3' );

		$this->assertStringContainsString( '<nav class="toc-container"', $html );
		$this->assertStringContainsString( '<script>', $html );
	}

	public function test_render_adds_sticky_class_when_configured(): void {
		$html = TocRenderer::render( [ 'isSticky' => true, 'items' => [] ], 'toc-4' );

		$this->assertStringContainsString( 'toc-sticky', $html );
	}

	public function test_render_omits_sticky_class_by_default(): void {
		$html = TocRenderer::render( [ 'items' => [] ], 'toc-5' );

		$this->assertStringNotContainsString( 'toc-sticky', $html );
	}

	public function test_render_includes_title_when_present(): void {
		$html = TocRenderer::render( [ 'title' => 'Table of Contents', 'items' => [] ], 'toc-6' );

		$this->assertStringContainsString( 'toc-title', $html );
		$this->assertStringContainsString( 'Table of Contents', $html );
	}
}
