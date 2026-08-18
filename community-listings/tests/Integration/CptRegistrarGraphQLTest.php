<?php
/**
 * Integration tests for CptRegistrar's WPGraphQL meta type registration.
 *
 * WPGraphQL's TypeRegistry does not tolerate register_graphql_object_type()/
 * register_graphql_field() being invoked more than once per process for the
 * same type (a real DUPLICATE_FIELD-class error, the same class of bug this
 * plugin already guards against elsewhere for graphql-shortcode-support) — so
 * only ONE test in this class performs the actual registration + schema
 * build; every other test asserts hook wiring only, matching the pattern
 * already proven in silver-assist-security's own GraphQL integration tests.
 *
 * @package SilverAssist\CommunityListings\Tests\Integration
 */

namespace SilverAssist\CommunityListings\Tests\Integration;

use SilverAssist\CommunityListings\Service\CptRegistrar;
use SilverAssist\PluginKernel\Testing\TestCase;

/**
 * @covers \SilverAssist\CommunityListings\Service\CptRegistrar
 */
class CptRegistrarGraphQLTest extends TestCase {

	private CptRegistrar $registrar;

	protected function setUp(): void {
		parent::setUp();

		if ( ! \class_exists( 'WPGraphQL' ) ) {
			$this->markTestSkipped( 'WPGraphQL plugin not available in the test environment.' );
		}

		$this->registrar = CptRegistrar::instance();
		CptRegistrar::register_post_type();
	}

	public function test_init_registers_graphql_register_types_hook(): void {
		$this->registrar->init();

		$this->assertNotFalse( \has_action( 'graphql_register_types', [ $this->registrar, 'register_graphql_meta_fields' ] ) );
	}

	/**
	 * The only test in this class that actually performs live WPGraphQL type
	 * registration — see class docblock for why it must stay singular.
	 */
	public function test_register_graphql_meta_fields_builds_queryable_schema(): void {
		$this->registrar->register_graphql_meta_fields();

		// A plain data query (not introspection, which WPGraphQL blocks for public
		// requests by default) is enough to force a real schema build.
		$result = \graphql( [ 'query' => '{ communities { nodes { id } } }' ] );

		$this->assertArrayNotHasKey( 'errors', $result, 'Schema must build successfully after register_graphql_meta_fields() runs.' );

		$type = \WPGraphQL::get_type_registry()->get_type( 'CommunityMeta' );
		$this->assertNotNull( $type, 'CommunityMeta GraphQL object type should be registered.' );
	}
}
