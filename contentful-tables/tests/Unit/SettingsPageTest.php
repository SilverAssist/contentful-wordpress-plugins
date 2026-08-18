<?php
/**
 * Tests for SettingsPage.
 *
 * @package SilverAssist\ContentfulTables\Tests\Unit
 */

namespace SilverAssist\ContentfulTables\Tests\Unit;

use SilverAssist\ContentfulTables\Admin\SettingsPage;
use SilverAssist\PluginKernel\Testing\TestCase;

/**
 * @covers \SilverAssist\ContentfulTables\Admin\SettingsPage
 */
class SettingsPageTest extends TestCase {

	private SettingsPage $settings_page;

	protected function setUp(): void {
		parent::setUp();
		$this->settings_page = SettingsPage::instance();
	}

	public function test_get_priority_is_thirty(): void {
		$this->assertSame( 30, $this->settings_page->get_priority() );
	}

	public function test_register_with_settings_hub_falls_back_when_hub_absent(): void {
		if ( \class_exists( \SilverAssist\SettingsHub\SettingsHub::class ) ) {
			$this->markTestSkipped( 'Settings Hub package is present in this test run; standalone fallback path is not reachable.' );
		}

		$this->settings_page->register_with_settings_hub();

		$this->assertNotFalse( \has_action( 'admin_menu', [ $this->settings_page, 'add_menu' ] ) );
	}

	public function test_render_page_saves_load_css_option_on_submit(): void {
		$admin_id = static::factory()->user->create( [ 'role' => 'administrator' ] );
		\wp_set_current_user( $admin_id );

		$_POST['submit']   = 'Save Changes';
		$_POST['load_css'] = '1';

		ob_start();
		$this->settings_page->render_page();
		ob_get_clean();

		$this->assertTrue( \get_option( 'contentful_tables_load_css' ) );

		unset( $_POST['submit'], $_POST['load_css'] );
	}

	public function test_render_page_unsets_load_css_when_checkbox_absent_on_submit(): void {
		$admin_id = static::factory()->user->create( [ 'role' => 'administrator' ] );
		\wp_set_current_user( $admin_id );

		$_POST['submit'] = 'Save Changes';
		unset( $_POST['load_css'] );

		ob_start();
		$this->settings_page->render_page();
		ob_get_clean();

		$this->assertFalse( \get_option( 'contentful_tables_load_css' ) );

		unset( $_POST['submit'] );
		\update_option( 'contentful_tables_load_css', true );
	}
}
