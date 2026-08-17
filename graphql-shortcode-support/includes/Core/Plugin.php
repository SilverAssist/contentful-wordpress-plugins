<?php
/**
 * Main Plugin Class.
 *
 * Bootstraps all components using LoadableInterface priority system.
 *
 * @package SilverAssist\GraphQLShortcodeSupport\Core
 * @since   1.0.0
 */

namespace SilverAssist\GraphQLShortcodeSupport\Core;

use SilverAssist\GraphQLShortcodeSupport\Admin\SettingsPage;
use SilverAssist\GraphQLShortcodeSupport\Service\GraphQLShortcodeResolver;
use SilverAssist\PluginKernel\AbstractPlugin;

// Prevent direct access.
\defined( 'ABSPATH' ) || exit;

/**
 * Plugin singleton class.
 *
 * Singleton access (instance()) and the priority-ordered component loading
 * loop are inherited from AbstractPlugin (silverassist/wp-plugin-kernel) —
 * this class only declares which components to load. This plugin's own
 * component classes already used the same get_priority()/should_load()/
 * init() method names as the kernel interface before this migration, so no
 * changes were needed there.
 *
 * @since 1.0.0
 */
class Plugin extends AbstractPlugin {

	/**
	 * Get components to load.
	 *
	 * @since 1.1.0
	 *
	 * @return array<class-string>
	 */
	protected function get_components(): array {
		return [
			GraphQLShortcodeResolver::class,
			SettingsPage::class,
		];
	}
}
