<?php
/**
 * Tests for SettingsPage.
 *
 * @package SilverAssist\GraphQLShortcodeSupport\Tests\Unit
 */

namespace SilverAssist\GraphQLShortcodeSupport\Tests\Unit;

use SilverAssist\GraphQLShortcodeSupport\Admin\SettingsPage;
use SilverAssist\PluginKernel\Testing\TestCase;

/**
 * @covers \SilverAssist\GraphQLShortcodeSupport\Admin\SettingsPage
 */
class SettingsPageTest extends TestCase {

	private SettingsPage $settings_page;

	protected function setUp(): void {
		parent::setUp();
		$this->settings_page = SettingsPage::instance();
	}

	public function test_sanitize_settings_enabled_checkbox_present(): void {
		$result = $this->settings_page->sanitize_settings( [ 'enabled' => '1' ] );

		$this->assertTrue( $result['enabled'] );
	}

	public function test_sanitize_settings_enabled_checkbox_absent(): void {
		$result = $this->settings_page->sanitize_settings( [] );

		$this->assertFalse( $result['enabled'] );
	}

	public function test_sanitize_settings_post_types_sanitized(): void {
		$result = $this->settings_page->sanitize_settings(
			[ 'post_types' => [ 'post', 'Community!!' ] ]
		);

		$this->assertSame( [ 'post', 'community' ], $result['post_types'] );
	}

	public function test_sanitize_settings_fields_comma_separated_string(): void {
		$result = $this->settings_page->sanitize_settings(
			[ 'fields' => 'content, excerpt' ]
		);

		$this->assertSame( [ 'content', 'excerpt' ], array_values( $result['fields'] ) );
	}

	public function test_sanitize_settings_fields_array_input(): void {
		$result = $this->settings_page->sanitize_settings(
			[ 'fields' => [ 'content', 'excerpt' ] ]
		);

		$this->assertSame( [ 'content', 'excerpt' ], array_values( $result['fields'] ) );
	}

	public function test_sanitize_settings_fields_defaults_to_content_when_empty(): void {
		$result = $this->settings_page->sanitize_settings( [ 'fields' => '' ] );

		$this->assertSame( [ 'content' ], $result['fields'] );
	}

	public function test_should_load_true_in_admin_context(): void {
		set_current_screen( 'edit-post' );

		$this->assertTrue( \is_admin() );
		$this->assertTrue( $this->settings_page->should_load() );
	}

	public function test_get_priority_is_thirty(): void {
		$this->assertSame( 30, $this->settings_page->get_priority() );
	}
}
