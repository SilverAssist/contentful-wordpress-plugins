<?php
/**
 * Admin settings page with Settings Hub integration.
 *
 * @package SilverAssist\CommunityListings
 * @author  Silver Assist
 * @license PolyForm-Noncommercial-1.0.0
 * @since   2.1.0
 */

declare( strict_types=1 );

namespace SilverAssist\CommunityListings\Admin;

use SilverAssist\CommunityListings\Core\Interfaces\LoadableInterface;
use SilverAssist\SettingsHub\SettingsHub;

/**
 * Registers the admin settings page under Silver Assist Settings Hub.
 *
 * Falls back to a standalone Settings page if the hub is not available.
 *
 * Priority 30 — loads after services.
 *
 * @since 2.1.0
 */
final class SettingsPage implements LoadableInterface {

	/**
	 * Plugin slug for settings.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	private const PLUGIN_SLUG = 'community-listings';

	/**
	 * Return the loading priority.
	 *
	 * @since 2.1.0
	 *
	 * @return int Loading priority.
	 */
	public function priority(): int {
		return 30;
	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 2.1.0
	 *
	 * @return void
	 */
	public function register(): void {
		\add_action( 'init', array( $this, 'register_with_settings_hub' ) );
	}

	/**
	 * Register this plugin with the Settings Hub.
	 *
	 * Falls back to a standalone settings page if the hub is not available.
	 *
	 * @since 2.1.0
	 *
	 * @return void
	 */
	public function register_with_settings_hub(): void {
		if ( ! class_exists( SettingsHub::class ) ) {
			\add_action( 'admin_menu', array( $this, 'register_standalone_settings' ) );
			return;
		}

		$hub = SettingsHub::get_instance();

		$hub->register_plugin(
			self::PLUGIN_SLUG,
			\__( 'Community Listings', 'community-listings' ),
			array( $this, 'render_settings_page' ),
			array(
				'description' => \__( 'Hierarchical Community CPT for state and city memory care listings with WPGraphQL support.', 'community-listings' ),
				'version'     => CMTY_LISTINGS_VERSION,
			)
		);
	}

	/**
	 * Fallback: Register standalone settings page if hub is not available.
	 *
	 * @since 2.1.0
	 *
	 * @return void
	 */
	public function register_standalone_settings(): void {
		\add_options_page(
			\__( 'Community Listings', 'community-listings' ),
			\__( 'Community Listings', 'community-listings' ),
			'manage_options',
			self::PLUGIN_SLUG,
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Render the settings page content.
	 *
	 * @since 2.1.0
	 *
	 * @return void
	 */
	public function render_settings_page(): void {
		if ( ! \current_user_can( 'manage_options' ) ) {
			return;
		}

		$community_count = \wp_count_posts( 'community' );
		$total           = 0;
		if ( $community_count ) {
			$total = (int) $community_count->publish;
		}

		?>
		<div class="wrap silverassist-settings-page">
			<div class="silverassist-settings-intro">
				<p class="description">
					<?php \esc_html_e( 'The Community Listings plugin registers a hierarchical "Community" custom post type for state and city memory care listings.', 'community-listings' ); ?>
				</p>
			</div>

			<h3><?php \esc_html_e( 'Plugin Status', 'community-listings' ); ?></h3>
			<table class="form-table">
				<tr>
					<th scope="row"><?php \esc_html_e( 'Version', 'community-listings' ); ?></th>
					<td><code><?php echo \esc_html( CMTY_LISTINGS_VERSION ); ?></code></td>
				</tr>
				<tr>
					<th scope="row"><?php \esc_html_e( 'Published Communities', 'community-listings' ); ?></th>
					<td><?php echo \esc_html( (string) $total ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php \esc_html_e( 'WPGraphQL', 'community-listings' ); ?></th>
					<td>
						<?php if ( class_exists( 'WPGraphQL' ) ) : ?>
							<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
							<?php \esc_html_e( 'Active', 'community-listings' ); ?>
						<?php else : ?>
							<span class="dashicons dashicons-dismiss" style="color: #dc3232;"></span>
							<?php \esc_html_e( 'Not active — WPGraphQL is required for GraphQL support.', 'community-listings' ); ?>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php \esc_html_e( 'Post Type', 'community-listings' ); ?></th>
					<td><code>community</code></td>
				</tr>
				<tr>
					<th scope="row"><?php \esc_html_e( 'GraphQL Type', 'community-listings' ); ?></th>
					<td><code>Community</code></td>
				</tr>
			</table>

			<h3><?php \esc_html_e( 'Features', 'community-listings' ); ?></h3>
			<ul style="list-style: disc; padding-left: 20px;">
				<li><?php \esc_html_e( 'Hierarchical custom post type (State → City)', 'community-listings' ); ?></li>
				<li><?php \esc_html_e( 'WPGraphQL integration with renderedContent field', 'community-listings' ); ?></li>
				<li><?php \esc_html_e( 'REST API parent/children filters', 'community-listings' ); ?></li>
				<li><?php \esc_html_e( 'Shortcode processing in GraphQL responses', 'community-listings' ); ?></li>
			</ul>
		</div>
		<?php
	}
}
