<?php
/**
 * CPT registrar service.
 *
 * @package SilverAssist\CommunityListings
 * @author  Silver Assist
 * @license PolyForm-Noncommercial-1.0.0
 * @since   2.0.0
 */

declare( strict_types=1 );

namespace SilverAssist\CommunityListings\Service;

use SilverAssist\CommunityListings\Core\Interfaces\LoadableInterface;

/**
 * Registers the Community custom post type and meta fields.
 *
 * Priority 10 — loads first.
 *
 * @since 2.0.0
 */
final class CptRegistrar implements LoadableInterface {

	/**
	 * Meta fields with their types.
	 *
	 * @since 2.0.0
	 *
	 * @var array<string, string>
	 */
	private const META_FIELDS = array(
		'contentful_id'      => 'string',
		'listing_type'       => 'string',
		'state_short'        => 'string',
		'state_long'         => 'string',
		'original_slug'      => 'string',
		'original_url'       => 'string',
		'content_bucket'     => 'string',
		'sitemap_group'      => 'string',
		'link_text'          => 'string',
		'hero_text_contrast' => 'boolean',
		'noindex'            => 'boolean',
		'nofollow'           => 'boolean',
	);

	/**
	 * Return the loading priority.
	 *
	 * @since 2.0.0
	 *
	 * @return int Loading priority.
	 */
	public function priority(): int {
		return 10;
	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function register(): void {
		\add_action( 'init', array( self::class, 'register_post_type' ) );
		\add_action( 'init', array( $this, 'register_meta' ) );
		\add_action( 'graphql_register_types', array( $this, 'register_graphql_meta_fields' ) );
	}

	/**
	 * Register the Community custom post type.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public static function register_post_type(): void {
		$labels = array(
			'name'               => 'Communities',
			'singular_name'      => 'Community',
			'menu_name'          => 'Communities',
			'name_admin_bar'     => 'Community',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New Community',
			'new_item'           => 'New Community',
			'edit_item'          => 'Edit Community',
			'view_item'          => 'View Community',
			'all_items'          => 'All Communities',
			'search_items'       => 'Search Communities',
			'parent_item_colon'  => 'Parent State:',
			'not_found'          => 'No communities found.',
			'not_found_in_trash' => 'No communities found in Trash.',
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_rest'       => true,
			'rest_base'          => 'community',
			'query_var'          => true,
			'rewrite'            => array(
				'slug'       => 'communities',
				'with_front' => false,
			),
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
			'has_archive'        => true,
			'hierarchical'       => true,
			'menu_position'      => 5,
			'menu_icon'          => 'dashicons-location-alt',
			'show_in_graphql'     => true,
			'graphql_single_name' => 'community',
			'graphql_plural_name' => 'communities',
			'supports'           => array(
				'title',
				'editor',
				'excerpt',
				'thumbnail',
				'custom-fields',
				'page-attributes',
				'revisions',
			),
		);

		\register_post_type( 'community', $args );
	}

	/**
	 * Register custom meta fields for the REST API.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function register_meta(): void {
		foreach ( self::META_FIELDS as $key => $type ) {
			\register_post_meta(
				'community',
				$key,
				array(
					'show_in_rest'    => true,
					'show_in_graphql' => true,
					'single'          => true,
					'type'            => $type,
					'auth_callback'   => static function (): bool {
						return \current_user_can( 'edit_posts' );
					},
				)
			);
		}
	}

	/**
	 * Register the CommunityMeta GraphQL object type and communityMeta field.
	 *
	 * Exposes all custom meta fields on the Community type via a dedicated
	 * `communityMeta` field in WPGraphQL.
	 *
	 * @since 2.2.2
	 *
	 * @return void
	 */
	public function register_graphql_meta_fields(): void {
		if ( ! \function_exists( 'register_graphql_object_type' ) ) {
			return;
		}

		// Map PHP meta types to GraphQL scalar types.
		$type_map = array(
			'string'  => 'String',
			'boolean' => 'Boolean',
			'integer' => 'Int',
			'number'  => 'Float',
		);

		// Build the fields array for the CommunityMeta object type.
		$graphql_fields = array();
		foreach ( self::META_FIELDS as $key => $type ) {
			// Convert snake_case meta key to camelCase GraphQL field name.
			$camel_key = \lcfirst( \str_replace( '_', '', \ucwords( $key, '_' ) ) );

			$graphql_fields[ $camel_key ] = array(
				'type'        => $type_map[ $type ] ?? 'String',
				'description' => \sprintf( 'The %s meta field.', \str_replace( '_', ' ', $key ) ),
				'resolve'     => static function ( $source ) use ( $key, $type ) {
					$post_id = 0;

					// WPGraphQL Model\Post — preferred.
					if ( \is_object( $source ) && isset( $source->databaseId ) ) {
						$post_id = (int) $source->databaseId;
					} elseif ( $source instanceof \WP_Post ) {
						$post_id = (int) $source->ID;
					} elseif ( \is_object( $source ) && isset( $source->ID ) ) {
						$post_id = (int) $source->ID;
					}

					if ( 0 === $post_id ) {
						return 'boolean' === $type ? false : '';
					}

					$value = \get_post_meta( $post_id, $key, true );

					if ( 'boolean' === $type ) {
						return (bool) $value;
					}

					return \is_string( $value ) ? $value : (string) $value;
				},
			);
		}

		// Register the CommunityMeta object type.
		\register_graphql_object_type(
			'CommunityMeta',
			array(
				'description' => 'Custom meta fields for the Community post type.',
				'fields'      => $graphql_fields,
			)
		);

		// Register the communityMeta field on the Community type.
		\register_graphql_field(
			'Community',
			'communityMeta',
			array(
				'type'        => 'CommunityMeta',
				'description' => 'Community custom meta fields.',
				'resolve'     => static function ( $post ) {
					return $post;
				},
			)
		);
	}
}
