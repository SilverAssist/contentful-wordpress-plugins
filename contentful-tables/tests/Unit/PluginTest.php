<?php
/**
 * Tests for Plugin bootstrap.
 *
 * @package SilverAssist\ContentfulTables\Tests\Unit
 */

namespace SilverAssist\ContentfulTables\Tests\Unit;

use SilverAssist\ContentfulTables\Admin\SettingsPage;
use SilverAssist\ContentfulTables\Core\Plugin;
use SilverAssist\ContentfulTables\Service\GraphQLResolver;
use SilverAssist\ContentfulTables\Service\ShortcodeRegistrar;
use SilverAssist\ContentfulTables\Service\TableDataLoader;
use SilverAssist\PluginKernel\Testing\TestCase;

/**
 * @covers \SilverAssist\ContentfulTables\Core\Plugin
 */
class PluginTest extends TestCase {

	public function test_get_components_lists_all_four_components(): void {
		$reflection = new \ReflectionMethod( Plugin::class, 'get_components' );
		$reflection->setAccessible( true );

		$components = $reflection->invoke( Plugin::instance() );

		$this->assertSame(
			[
				TableDataLoader::class,
				ShortcodeRegistrar::class,
				GraphQLResolver::class,
				SettingsPage::class,
			],
			$components
		);
	}
}
