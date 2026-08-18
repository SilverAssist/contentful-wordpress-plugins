<?php
/**
 * Tests for Activator.
 *
 * A CREATE TABLE statement in a fixture must run in wpSetUpBeforeClass(), not
 * setUp() — WordPress's per-test transaction rollback does not undo DDL, so
 * running it per-test would leak the table across the whole suite anyway;
 * running it once per class is both correct and faster.
 *
 * @package SilverAssist\ContentfulTables\Tests\Unit
 */

namespace SilverAssist\ContentfulTables\Tests\Unit;

use SilverAssist\ContentfulTables\Core\Activator;
use SilverAssist\PluginKernel\Testing\TestCase;

/**
 * @covers \SilverAssist\ContentfulTables\Core\Activator
 */
class ActivatorTest extends TestCase {

	public static function set_up_before_class(): void {
		parent::set_up_before_class();
		Activator::activate();
	}

	public function test_activate_creates_database_table(): void {
		global $wpdb;

		$table_name = $wpdb->prefix . 'contentful_tables';
		$exists     = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );

		$this->assertSame( $table_name, $exists );
	}

	public function test_activate_creates_table_with_expected_columns(): void {
		global $wpdb;

		$table_name = $wpdb->prefix . 'contentful_tables';
		$columns    = $wpdb->get_col( "DESCRIBE {$table_name}" );

		foreach ( [ 'id', 'entry_id', 'table_name', 'table_data', 'created_at', 'updated_at' ] as $expected ) {
			$this->assertContains( $expected, $columns );
		}
	}

	public function test_activate_sets_version_option(): void {
		$this->assertSame( CTFL_TABLES_VERSION, \get_option( 'contentful_tables_version' ) );
	}

	public function test_deactivate_removes_flush_needed_option(): void {
		\update_option( 'contentful_tables_flush_needed', 'yes' );

		Activator::deactivate();

		$this->assertFalse( \get_option( 'contentful_tables_flush_needed' ) );
	}
}
