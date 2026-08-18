<?php
/**
 * Tests for FormRenderer.
 *
 * @package SilverAssist\ContentfulTables\Tests\Unit
 */

namespace SilverAssist\ContentfulTables\Tests\Unit;

use SilverAssist\ContentfulTables\View\FormRenderer;
use SilverAssist\PluginKernel\Testing\TestCase;

/**
 * @covers \SilverAssist\ContentfulTables\View\FormRenderer
 */
class FormRendererTest extends TestCase {

	public function test_render_outputs_form_with_required_fields(): void {
		$html = FormRenderer::render( [ 'title' => 'Contact Us', 'submit' => 'Send' ] );

		$this->assertStringContainsString( '<form class="contentful-form"', $html );
		$this->assertStringContainsString( 'name="name"', $html );
		$this->assertStringContainsString( 'name="email"', $html );
		$this->assertStringContainsString( 'name="message"', $html );
		$this->assertStringContainsString( 'Contact Us', $html );
		$this->assertStringContainsString( '>Send<', $html );
	}

	public function test_render_defaults_title_and_submit_when_absent(): void {
		$html = FormRenderer::render( [] );

		$this->assertStringContainsString( 'Contact Us', $html );
		$this->assertStringContainsString( '>Send<', $html );
	}

	public function test_render_applies_custom_class(): void {
		$html = FormRenderer::render( [ 'class' => 'my-form' ] );

		$this->assertStringContainsString( 'contentful-form-container my-form', $html );
	}
}
