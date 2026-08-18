<?php
/**
 * Tests for ShortcodeRegistrar.
 *
 * @package SilverAssist\ContentfulTables\Tests\Unit
 */

namespace SilverAssist\ContentfulTables\Tests\Unit;

use SilverAssist\ContentfulTables\Service\ShortcodeRegistrar;
use SilverAssist\ContentfulTables\Service\TableDataLoader;
use SilverAssist\PluginKernel\Testing\TestCase;

/**
 * @covers \SilverAssist\ContentfulTables\Service\ShortcodeRegistrar
 */
class ShortcodeRegistrarTest extends TestCase {

	private ShortcodeRegistrar $registrar;

	protected function setUp(): void {
		parent::setUp();

		$reflection = new \ReflectionClass( TableDataLoader::class );
		$property   = $reflection->getProperty( 'tables_data' );
		$property->setAccessible( true );
		$property->setValue( TableDataLoader::instance(), [] );

		$this->registrar = ShortcodeRegistrar::instance();
	}

	public function test_get_priority_is_twenty(): void {
		$this->assertSame( 20, $this->registrar->get_priority() );
	}

	public function test_render_table_shortcode_requires_id(): void {
		$html = $this->registrar->render_table_shortcode( [] );

		$this->assertStringContainsString( 'No table ID specified', $html );
	}

	public function test_render_table_shortcode_reports_missing_table(): void {
		$html = $this->registrar->render_table_shortcode( [ 'id' => 'does-not-exist' ] );

		$this->assertStringContainsString( 'not found', $html );
	}

	public function test_render_table_shortcode_uses_toc_renderer_for_toc_type(): void {
		$reflection = new \ReflectionClass( TableDataLoader::class );
		$property   = $reflection->getProperty( 'tables_data' );
		$property->setAccessible( true );
		$property->setValue( TableDataLoader::instance(), [ 'toc-1' => [ 'type' => 'tableOfContents', 'items' => [ [ 'text' => 'Section' ] ] ] ] );

		$html = $this->registrar->render_table_shortcode( [ 'id' => 'toc-1' ] );

		$this->assertStringContainsString( 'contentful-table-of-contents', $html );
	}

	public function test_render_table_shortcode_applies_custom_class(): void {
		$reflection = new \ReflectionClass( TableDataLoader::class );
		$property   = $reflection->getProperty( 'tables_data' );
		$property->setAccessible( true );
		$property->setValue( TableDataLoader::instance(), [ 't1' => [ 'rawData' => [ [ 'a' ], [ '1' ] ] ] ] );

		$html = $this->registrar->render_table_shortcode( [ 'id' => 't1', 'class' => 'my-class' ] );

		$this->assertStringContainsString( 'contentful-data-table my-class', $html );
	}

	public function test_render_toc_shortcode_requires_id(): void {
		$html = $this->registrar->render_toc_shortcode( [] );

		$this->assertStringContainsString( 'No TOC ID specified', $html );
	}

	public function test_render_chart_shortcode_requires_id(): void {
		$html = $this->registrar->render_chart_shortcode( [] );

		$this->assertStringContainsString( 'No ID specified', $html );
	}

	public function test_render_cards_shortcode_requires_id(): void {
		$html = $this->registrar->render_cards_shortcode( [] );

		$this->assertStringContainsString( 'No ID specified', $html );
	}

	public function test_render_form_shortcode_outputs_form(): void {
		$html = $this->registrar->render_form_shortcode( [ 'title' => 'Contact', 'submit' => 'Go' ] );

		$this->assertStringContainsString( '<form', $html );
		$this->assertStringContainsString( 'Contact', $html );
		$this->assertStringContainsString( 'Go', $html );
	}

	public function test_enqueue_styles_registers_file_when_asset_exists(): void {
		\update_option( 'contentful_tables_load_css', true );

		$this->registrar->enqueue_styles();

		$this->assertTrue( \wp_style_is( 'contentful-tables', 'registered' ) || \wp_style_is( 'contentful-tables', 'enqueued' ) );
	}

	public function test_enqueue_styles_skips_when_option_disabled(): void {
		// Storing the literal PHP boolean `false` via update_option() is a known
		// WordPress footgun: wp_cache_get() returns `false` to signal a cache
		// miss, so a genuinely-stored `false` value is indistinguishable from
		// "not cached" and get_option() falls through to its $default. Storing
		// '0' instead exercises the exact same `! get_option(...)` falsy check
		// in enqueue_styles() without hitting that ambiguity.
		\update_option( 'contentful_tables_load_css', '0' );
		\wp_dequeue_style( 'contentful-tables' );
		\wp_deregister_style( 'contentful-tables' );

		$this->registrar->enqueue_styles();

		$this->assertFalse( \wp_style_is( 'contentful-tables', 'enqueued' ) );

		\update_option( 'contentful_tables_load_css', true );
	}
}
