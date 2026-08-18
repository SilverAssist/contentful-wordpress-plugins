<?php
/**
 * Plugin bootstrap.
 *
 * @package SilverAssist\ContentfulTables
 * @author  Silver Assist
 * @license PolyForm-Noncommercial-1.0.0
 * @since   4.0.0
 */

declare( strict_types=1 );

namespace SilverAssist\ContentfulTables\Core;

use SilverAssist\ContentfulTables\Admin\SettingsPage;
use SilverAssist\ContentfulTables\Service\GraphQLResolver;
use SilverAssist\ContentfulTables\Service\ShortcodeRegistrar;
use SilverAssist\ContentfulTables\Service\TableDataLoader;
use SilverAssist\PluginKernel\AbstractPlugin;

/**
 * Singleton that bootstraps the plugin.
 *
 * Singleton access (instance()) and the priority-ordered component loading
 * loop are inherited from AbstractPlugin (silverassist/wp-plugin-kernel) —
 * this class only declares which components to load. TableDataLoader is
 * itself a kernel-managed singleton component now; ShortcodeRegistrar and
 * SettingsPage fetch it via TableDataLoader::instance() in their own
 * constructors instead of receiving it injected here.
 *
 * @since 4.0.0
 */
final class Plugin extends AbstractPlugin {

	/**
	 * List the component classes this plugin loads.
	 *
	 * @since 4.3.0
	 *
	 * @return array<class-string>
	 */
	protected function get_components(): array {
		return array(
			TableDataLoader::class,
			ShortcodeRegistrar::class,
			GraphQLResolver::class,
			SettingsPage::class,
		);
	}
}
