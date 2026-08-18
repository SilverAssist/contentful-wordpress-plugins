<?php
/**
 * Tests for SettingsPage.
 *
 * @package SilverAssist\CommunityListings\Tests\Unit
 */

namespace SilverAssist\CommunityListings\Tests\Unit;

use SilverAssist\CommunityListings\Admin\SettingsPage;
use SilverAssist\PluginKernel\Testing\TestCase;

/**
 * @covers \SilverAssist\CommunityListings\Admin\SettingsPage
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

	public function test_should_load_is_true(): void {
		$this->assertTrue( $this->settings_page->should_load() );
	}

	public function test_register_with_settings_hub_falls_back_to_standalone_when_hub_absent(): void {
		if ( class_exists( \SilverAssist\SettingsHub\SettingsHub::class ) ) {
			$this->markTestSkipped( 'Settings Hub package is present in this test run; standalone fallback path is not reachable.' );
		}

		$this->settings_page->register_with_settings_hub();

		$this->assertNotFalse( \has_action( 'admin_menu', [ $this->settings_page, 'register_standalone_settings' ] ) );
	}

	public function test_render_settings_page_outputs_nothing_without_capability(): void {
		\wp_set_current_user( 0 );

		ob_start();
		$this->settings_page->render_settings_page();
		$output = ob_get_clean();

		$this->assertSame( '', $output );
	}

	public function test_render_settings_page_outputs_html_for_admin(): void {
		$admin_id = static::factory()->user->create( [ 'role' => 'administrator' ] );
		\wp_set_current_user( $admin_id );

		ob_start();
		$this->settings_page->render_settings_page();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'Plugin Status', $output );
		$this->assertStringContainsString( 'community', $output );
	}
}
