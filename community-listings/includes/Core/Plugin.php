<?php
/**
 * Plugin bootstrap.
 *
 * @package SilverAssist\CommunityListings
 * @author  Silver Assist
 * @license PolyForm-Noncommercial-1.0.0
 * @since   2.0.0
 */

declare( strict_types=1 );

namespace SilverAssist\CommunityListings\Core;

use SilverAssist\CommunityListings\Admin\ProviderListingsMetaBox;
use SilverAssist\CommunityListings\Admin\SettingsPage;
use SilverAssist\CommunityListings\Service\CptRegistrar;
use SilverAssist\CommunityListings\Service\GraphQLResolver;
use SilverAssist\CommunityListings\Service\RestApiFilters;
use SilverAssist\PluginKernel\AbstractPlugin;

/**
 * Singleton that bootstraps the plugin.
 *
 * Singleton access (instance()) and the priority-ordered component loading
 * loop are inherited from AbstractPlugin (silverassist/wp-plugin-kernel) —
 * this class only declares which components to load.
 *
 * @since 2.0.0
 */
final class Plugin extends AbstractPlugin {

	/**
	 * List the component classes this plugin loads.
	 *
	 * @since 2.3.0
	 *
	 * @return array<class-string>
	 */
	protected function get_components(): array {
		return array(
			CptRegistrar::class,
			RestApiFilters::class,
			GraphQLResolver::class,
			ProviderListingsMetaBox::class,
			SettingsPage::class,
		);
	}
}
