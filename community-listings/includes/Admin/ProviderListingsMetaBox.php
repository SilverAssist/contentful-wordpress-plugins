<?php
/**
 * Provider Listings meta box for city-level Community posts.
 *
 * @package SilverAssist\CommunityListings
 * @author  Silver Assist
 * @license PolyForm-Noncommercial-1.0.0
 * @since   2.2.5
 */

declare( strict_types=1 );

namespace SilverAssist\CommunityListings\Admin;

use SilverAssist\CommunityListings\Core\Interfaces\LoadableInterface;

/**
 * Registers and handles provider_listings meta box editing.
 *
 * @since 2.2.5
 */
final class ProviderListingsMetaBox implements LoadableInterface {

	/**
	 * Nonce action.
	 *
	 * @since 2.2.5
	 *
	 * @var string
	 */
	private const NONCE_ACTION = 'provider_listings_meta_box_action';

	/**
	 * Nonce field name.
	 *
	 * @since 2.2.5
	 *
	 * @var string
	 */
	private const NONCE_FIELD = 'provider_listings_meta_box_nonce';

	/**
	 * Textarea field name.
	 *
	 * @since 2.2.5
	 *
	 * @var string
	 */
	private const FIELD_NAME = 'provider_listings_json';

	/**
	 * Query arg name for invalid JSON notice.
	 *
	 * @since 2.2.5
	 *
	 * @var string
	 */
	private const INVALID_JSON_QUERY_ARG = 'provider_listings_json_invalid';

	/**
	 * Return the loading priority.
	 *
	 * @since 2.2.5
	 *
	 * @return int Loading priority.
	 */
	public function priority(): int {
		return 25;
	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function register(): void {
		\add_action( 'add_meta_boxes_community', array( $this, 'register_meta_box' ) );
		\add_action( 'save_post_community', array( $this, 'save_provider_listings' ), 10, 3 );
		\add_action( 'admin_notices', array( $this, 'render_invalid_json_notice' ) );
	}

	/**
	 * Register the Provider Listings meta box for city-level communities.
	 *
	 * @since 2.2.5
	 *
	 * @param \WP_Post $post Post object.
	 * @return void
	 */
	public function register_meta_box( \WP_Post $post ): void {
		$listing_type = \get_post_meta( $post->ID, 'listing_type', true );

		if ( 'city' !== $listing_type ) {
			return;
		}

		\add_meta_box(
			'provider-listings-meta-box',
			\__( 'Provider Listings', 'community-listings' ),
			array( $this, 'render_meta_box' ),
			'community',
			'normal',
			'high'
		);
	}

	/**
	 * Render the Provider Listings meta box.
	 *
	 * @since 2.2.5
	 *
	 * @param \WP_Post $post Post object.
	 * @return void
	 */
	public function render_meta_box( \WP_Post $post ): void {
		$raw_json = \get_post_meta( $post->ID, 'provider_listings', true );
		$raw_json = \is_string( $raw_json ) ? $raw_json : '';

		$formatted_json = $raw_json;
		$decoded_json   = null;

		if ( '' !== $raw_json ) {
			$decoded_json = \json_decode( $raw_json, true );

			if ( \JSON_ERROR_NONE === \json_last_error() ) {
				$pretty_json = \json_encode( $decoded_json, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE );

				if ( false !== $pretty_json ) {
					$formatted_json = $pretty_json;
				}
			}
		}

		$provider_count = \is_array( $decoded_json ) && \array_is_list( $decoded_json ) ? \count( $decoded_json ) : 0;
		$byte_count     = \strlen( $formatted_json );
		$nonce          = \wp_create_nonce( self::NONCE_ACTION );
		?>
		<input type="hidden" name="<?php echo \esc_attr( self::NONCE_FIELD ); ?>" value="<?php echo \esc_attr( $nonce ); ?>" />
		<textarea
			id="provider-listings-json"
			name="<?php echo \esc_attr( self::FIELD_NAME ); ?>"
			rows="20"
			style="width: 100%; font-family: monospace;"
		><?php echo \esc_textarea( $formatted_json ); ?></textarea>
		<p class="description">
			<strong id="provider-listings-byte-count"><?php echo \esc_html( \number_format_i18n( $byte_count ) ); ?> bytes</strong>
			·
			<strong id="provider-listings-provider-count"><?php echo \esc_html( \number_format_i18n( $provider_count ) ); ?> <?php echo \esc_html( 1 === $provider_count ? 'provider' : 'providers' ); ?></strong>
		</p>
		<p class="description" id="provider-listings-json-error" style="display: none; color: #d63638;">
			<?php \esc_html_e( 'Invalid JSON. Please correct before saving.', 'community-listings' ); ?>
		</p>
		<script>
			(function () {
				const textarea = document.getElementById('provider-listings-json');
				const errorMessage = document.getElementById('provider-listings-json-error');
				const byteCount = document.getElementById('provider-listings-byte-count');
				const providerCount = document.getElementById('provider-listings-provider-count');
				if (!textarea || !errorMessage || !byteCount || !providerCount) {
					return;
				}
				const formatNumber = (num) => num.toLocaleString();
				const updateStats = () => {
					const text = textarea.value;
					byteCount.textContent = `${formatNumber((new TextEncoder().encode(text)).length)} bytes`;
					try {
						const parsed = JSON.parse(text);
						const count = Array.isArray(parsed) ? parsed.length : 0;
						providerCount.textContent = `${formatNumber(count)} ${count === 1 ? 'provider' : 'providers'}`;
						textarea.style.borderColor = '';
						errorMessage.style.display = 'none';
					} catch (error) {
						providerCount.textContent = '0 providers';
						textarea.style.borderColor = '#d63638';
						errorMessage.style.display = 'block';
					}
				};
				textarea.addEventListener('blur', updateStats);
				textarea.addEventListener('change', updateStats);
				textarea.addEventListener('input', updateStats);
				updateStats();
			})();
		</script>
		<?php
	}

	/**
	 * Save provider_listings JSON using direct DB update.
	 *
	 * @since 2.2.5
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 * @param bool     $update  Whether this is an existing post being updated.
	 * @return void
	 */
	public function save_provider_listings( int $post_id, \WP_Post $post, bool $update ): void {
		unset( $post, $update );

		if ( ! \current_user_can( 'edit_posts' ) ) {
			return;
		}

		if ( \wp_is_post_autosave( $post_id ) || \wp_is_post_revision( $post_id ) ) {
			return;
		}

		$nonce = $_POST[ self::NONCE_FIELD ] ?? '';
		$nonce = \is_string( $nonce ) ? \wp_unslash( $nonce ) : '';
		if ( ! \wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			return;
		}

		$listing_type = \get_post_meta( $post_id, 'listing_type', true );
		if ( 'city' !== $listing_type ) {
			return;
		}

		$posted_json = $_POST[ self::FIELD_NAME ] ?? null;
		if ( ! \is_string( $posted_json ) ) {
			return;
		}

		$json_string = \wp_unslash( $posted_json );
		$decoded     = \json_decode( $json_string, true );

		if ( \JSON_ERROR_NONE !== \json_last_error() ) {
			\add_filter( 'redirect_post_location', array( $this, 'append_invalid_json_query_arg' ) );
			return;
		}

		unset( $decoded );

		global $wpdb; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		$updated = $wpdb->update(
			$wpdb->postmeta,
			array( 'meta_value' => $json_string ),
			array(
				'post_id'  => $post_id,
				'meta_key' => 'provider_listings',
			),
			array( '%s' ),
			array( '%d', '%s' )
		);

		if ( false === $updated ) {
			return;
		}

		if ( 0 === $updated && ! \metadata_exists( 'post', $post_id, 'provider_listings' ) ) {
			$wpdb->insert(
				$wpdb->postmeta,
				array(
					'post_id'    => $post_id,
					'meta_key'   => 'provider_listings',
					'meta_value' => $json_string,
				),
				array( '%d', '%s', '%s' )
			);
		}
	}

	/**
	 * Add invalid JSON flag to redirect URL.
	 *
	 * @since 2.2.5
	 *
	 * @param string $location Redirect URL.
	 * @return string
	 */
	public function append_invalid_json_query_arg( string $location ): string {
		\remove_filter( 'redirect_post_location', array( $this, __FUNCTION__ ) );
		return \add_query_arg( self::INVALID_JSON_QUERY_ARG, '1', $location );
	}

	/**
	 * Render invalid JSON admin notice.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render_invalid_json_notice(): void {
		$screen = \function_exists( 'get_current_screen' ) ? \get_current_screen() : null;
		if ( ! $screen || 'community' !== $screen->post_type ) {
			return;
		}

		$invalid_flag = $_GET[ self::INVALID_JSON_QUERY_ARG ] ?? '';
		$invalid_flag = \is_string( $invalid_flag ) ? \sanitize_text_field( \wp_unslash( $invalid_flag ) ) : '';
		if ( '1' !== $invalid_flag ) {
			return;
		}
		?>
		<div class="notice notice-error is-dismissible">
			<p><?php \esc_html_e( 'Provider Listings not saved: invalid JSON.', 'community-listings' ); ?></p>
		</div>
		<?php
	}
}
