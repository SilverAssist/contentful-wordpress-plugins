<?php
/**
 * Tests for Activator.
 *
 * @package SilverAssist\GraphQLShortcodeSupport\Tests\Unit
 */

namespace SilverAssist\GraphQLShortcodeSupport\Tests\Unit;

use SilverAssist\GraphQLShortcodeSupport\Core\Activator;
use SilverAssist\PluginKernel\Testing\TestCase;

/**
 * @covers \SilverAssist\GraphQLShortcodeSupport\Core\Activator
 */
class ActivatorTest extends TestCase {

	protected function tearDown(): void {
		\delete_option( 'gss_settings' );
		parent::tearDown();
	}

	public function test_activate_sets_default_options_when_none_exist(): void {
		\delete_option( 'gss_settings' );

		Activator::activate();

		$settings = \get_option( 'gss_settings' );

		$this->assertIsArray( $settings );
		$this->assertTrue( $settings['enabled'] );
		$this->assertSame( [ 'post', 'page', 'community' ], $settings['post_types'] );
		$this->assertSame( [ 'content' ], $settings['fields'] );
	}

	public function test_activate_does_not_overwrite_existing_options(): void {
		\update_option( 'gss_settings', [ 'enabled' => false, 'post_types' => [ 'post' ], 'fields' => [ 'excerpt' ] ] );

		Activator::activate();

		$settings = \get_option( 'gss_settings' );

		$this->assertFalse( $settings['enabled'] );
		$this->assertSame( [ 'post' ], $settings['post_types'] );
	}
}
