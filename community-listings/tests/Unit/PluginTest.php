<?php
/**
 * Tests for Plugin bootstrap.
 *
 * @package SilverAssist\CommunityListings\Tests\Unit
 */

namespace SilverAssist\CommunityListings\Tests\Unit;

use SilverAssist\CommunityListings\Admin\ProviderListingsMetaBox;
use SilverAssist\CommunityListings\Admin\SettingsPage;
use SilverAssist\CommunityListings\Core\Plugin;
use SilverAssist\CommunityListings\Service\CptRegistrar;
use SilverAssist\CommunityListings\Service\GraphQLResolver;
use SilverAssist\CommunityListings\Service\RestApiFilters;
use SilverAssist\PluginKernel\Testing\TestCase;

/**
 * @covers \SilverAssist\CommunityListings\Core\Plugin
 */
class PluginTest extends TestCase {

	public function test_get_components_lists_all_five_components(): void {
		$reflection = new \ReflectionMethod( Plugin::class, 'get_components' );
		$reflection->setAccessible( true );

		$components = $reflection->invoke( Plugin::instance() );

		$this->assertSame(
			[
				CptRegistrar::class,
				RestApiFilters::class,
				GraphQLResolver::class,
				ProviderListingsMetaBox::class,
				SettingsPage::class,
			],
			$components
		);
	}

	public function test_plugin_bootstraps_and_registers_cpt(): void {
		Plugin::instance()->init();

		\do_action( 'init' );

		$this->assertTrue( \post_type_exists( 'community' ) );
	}
}
