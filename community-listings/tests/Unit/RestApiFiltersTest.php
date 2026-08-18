<?php
/**
 * Tests for RestApiFilters.
 *
 * @package SilverAssist\CommunityListings\Tests\Unit
 */

namespace SilverAssist\CommunityListings\Tests\Unit;

use SilverAssist\CommunityListings\Service\RestApiFilters;
use SilverAssist\PluginKernel\Testing\TestCase;
use WP_REST_Request;

/**
 * @covers \SilverAssist\CommunityListings\Service\RestApiFilters
 */
class RestApiFiltersTest extends TestCase {

	private RestApiFilters $filters;

	protected function setUp(): void {
		parent::setUp();
		$this->filters = RestApiFilters::instance();
	}

	public function test_filter_query_adds_listing_type_meta_query(): void {
		$request = new WP_REST_Request();
		$request->set_param( 'listing_type', 'city' );

		$args = $this->filters->filter_query( [], $request );

		$this->assertSame(
			[ 'key' => 'listing_type', 'value' => 'city' ],
			$args['meta_query'][0]
		);
	}

	public function test_filter_query_uppercases_state_short(): void {
		$request = new WP_REST_Request();
		$request->set_param( 'state_short', 'tx' );

		$args = $this->filters->filter_query( [], $request );

		$this->assertSame(
			[ 'key' => 'state_short', 'value' => 'TX' ],
			$args['meta_query'][0]
		);
	}

	public function test_filter_query_keeps_state_long_as_is(): void {
		$request = new WP_REST_Request();
		$request->set_param( 'state_long', 'texas' );

		$args = $this->filters->filter_query( [], $request );

		$this->assertSame(
			[ 'key' => 'state_long', 'value' => 'texas' ],
			$args['meta_query'][0]
		);
	}

	public function test_filter_query_combines_multiple_params(): void {
		$request = new WP_REST_Request();
		$request->set_param( 'listing_type', 'city' );
		$request->set_param( 'state_short', 'tx' );

		$args = $this->filters->filter_query( [], $request );

		$this->assertCount( 2, $args['meta_query'] );
	}

	public function test_filter_query_no_params_leaves_args_unchanged(): void {
		$request = new WP_REST_Request();

		$args = $this->filters->filter_query( [ 'existing' => 'value' ], $request );

		$this->assertArrayNotHasKey( 'meta_query', $args );
		$this->assertSame( 'value', $args['existing'] );
	}

	public function test_register_params_adds_all_three_params(): void {
		$params = $this->filters->register_params( [] );

		$this->assertArrayHasKey( 'listing_type', $params );
		$this->assertArrayHasKey( 'state_short', $params );
		$this->assertArrayHasKey( 'state_long', $params );
		$this->assertSame( [ 'state', 'city' ], $params['listing_type']['enum'] );
	}

	public function test_get_priority_is_twenty(): void {
		$this->assertSame( 20, $this->filters->get_priority() );
	}
}
