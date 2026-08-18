<?php
/**
 * Tests for Activator.
 *
 * @package SilverAssist\CommunityListings\Tests\Unit
 */

namespace SilverAssist\CommunityListings\Tests\Unit;

use SilverAssist\CommunityListings\Core\Activator;
use SilverAssist\PluginKernel\Testing\TestCase;

/**
 * @covers \SilverAssist\CommunityListings\Core\Activator
 */
class ActivatorTest extends TestCase {

	public function test_activate_registers_community_post_type(): void {
		Activator::activate();

		$this->assertTrue( \post_type_exists( 'community' ) );

		$post_type_object = \get_post_type_object( 'community' );
		$this->assertNotNull( $post_type_object );
		$this->assertTrue( $post_type_object->hierarchical );
		$this->assertSame( 'community', $post_type_object->graphql_single_name );
	}
}
