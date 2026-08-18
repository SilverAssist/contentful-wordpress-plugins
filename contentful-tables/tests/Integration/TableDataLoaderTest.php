<?php
/**
 * Tests for TableDataLoader — the plugin's most complex logic: a 4-level
 * fallback chain (files -> post meta -> database) for tables, plus a
 * separate 4-source resolution (inline / rawData / cached CSV / remote
 * spreadsheet) for get_card_rows().
 *
 * @package SilverAssist\ContentfulTables\Tests\Integration
 */

namespace SilverAssist\ContentfulTables\Tests\Integration;

use SilverAssist\ContentfulTables\Core\Activator;
use SilverAssist\ContentfulTables\Service\TableDataLoader;
use SilverAssist\PluginKernel\Testing\TestCase;

/**
 * @covers \SilverAssist\ContentfulTables\Service\TableDataLoader
 */
class TableDataLoaderTest extends TestCase {

	private TableDataLoader $loader;

	/** @var string[] Files created during a test, cleaned up in tearDown(). */
	private array $created_files = [];

	protected function setUp(): void {
		parent::setUp();

		$this->loader = TableDataLoader::instance();
		$this->reset_loader_state();

		\delete_option( 'contentful_tables_source' );
		\delete_option( 'contentful_tables_post_id' );
	}

	protected function tearDown(): void {
		foreach ( $this->created_files as $file ) {
			if ( \file_exists( $file ) ) {
				\unlink( $file );
			}
		}
		$this->created_files = [];

		foreach ( [ WP_CONTENT_DIR . '/contentful-tables', WP_CONTENT_DIR . '/contentful-charts', WP_CONTENT_DIR . '/contentful-cards' ] as $dir ) {
			if ( \is_dir( $dir ) && [] === \glob( $dir . '/*' ) ) {
				\rmdir( $dir );
			}
		}

		\remove_all_filters( 'pre_http_request' );

		parent::tearDown();
	}

	/**
	 * TableDataLoader is a kernel singleton whose loaded-data arrays are never
	 * cleared between calls (by design — load_tables_data()'s fallback chain
	 * relies on "still empty" to decide whether to try the next source). Tests
	 * must reset that private state directly to get a clean slate each time.
	 */
	private function reset_loader_state(): void {
		$reflection = new \ReflectionClass( TableDataLoader::class );
		foreach ( [ 'tables_data', 'charts_data', 'cards_data' ] as $prop ) {
			$property = $reflection->getProperty( $prop );
			$property->setAccessible( true );
			$property->setValue( $this->loader, [] );
		}
	}

	private function write_file( string $path, string $contents ): void {
		\wp_mkdir_p( \dirname( $path ) );
		\file_put_contents( $path, $contents );
		$this->created_files[] = $path;
	}

	public function test_get_priority_is_ten(): void {
		$this->assertSame( 10, $this->loader->get_priority() );
	}

	public function test_load_all_data_loads_json_table_from_files(): void {
		$this->write_file(
			WP_CONTENT_DIR . '/contentful-tables/my-table.json',
			\wp_json_encode( [ 'type' => 'Plain', 'title' => 'My Table' ] )
		);

		$this->loader->load_all_data();

		$this->assertSame( [ 'type' => 'Plain', 'title' => 'My Table' ], $this->loader->get_table( 'my-table' ) );
		$this->assertSame( 'files', \get_option( 'contentful_tables_source' ) );
	}

	public function test_load_all_data_loads_csv_table_with_key_column(): void {
		$this->write_file(
			WP_CONTENT_DIR . '/contentful-tables/csv-table.csv',
			"key,name\nfood,Acme Food\nagency,Acme Agency\n"
		);

		$this->loader->load_all_data();

		$table = $this->loader->get_table( 'csv-table' );

		$this->assertNotNull( $table );
		$this->assertSame( 'key', $table['keyColumn'] );
		$this->assertSame( 0, $table['keyColumnIndex'] );
		$this->assertSame( [ 'food', 'agency' ], $table['keyValues'] );
	}

	public function test_load_all_data_falls_back_to_post_meta_when_no_files(): void {
		\wp_insert_post(
			[
				'import_id'   => 241,
				'post_title'  => 'Legacy table source post',
				'post_status' => 'publish',
				'post_type'   => 'post',
			]
		);
		\update_post_meta( 241, 'contentful_table_from_meta', \wp_json_encode( [ 'type' => 'Plain', 'title' => 'From Meta' ] ) );

		$this->loader->load_all_data();

		$this->assertSame( [ 'type' => 'Plain', 'title' => 'From Meta' ], $this->loader->get_table( 'from_meta' ) );
		$this->assertSame( '241', (string) \get_option( 'contentful_tables_post_id' ) );
	}

	/**
	 * Regression test: load_tables_from_database() previously selected a
	 * non-existent `table_id` column (schema only has `entry_id`), which
	 * would have thrown a real SQL error the first time this fallback path
	 * was ever reached in production.
	 */
	public function test_load_all_data_falls_back_to_database_when_no_files_or_post_meta(): void {
		Activator::activate();

		global $wpdb;
		$table_name = $wpdb->prefix . 'contentful_tables';
		$wpdb->insert(
			$table_name,
			[
				'entry_id'   => 'db-table',
				'table_name' => 'DB Table',
				'table_data' => \wp_json_encode( [ 'type' => 'Plain', 'title' => 'From Database' ] ),
			]
		);

		$this->loader->load_all_data();

		$this->assertSame( [ 'type' => 'Plain', 'title' => 'From Database' ], $this->loader->get_table( 'db-table' ) );

		$wpdb->query( "TRUNCATE TABLE {$table_name}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	public function test_load_all_data_loads_charts_from_files(): void {
		$this->write_file(
			WP_CONTENT_DIR . '/contentful-charts/my-chart.json',
			\wp_json_encode( [ 'title' => 'My Chart' ] )
		);

		$this->loader->load_all_data();

		$this->assertSame( [ 'title' => 'My Chart' ], $this->loader->get_chart( 'my-chart' ) );
	}

	public function test_load_all_data_loads_cards_from_files(): void {
		$this->write_file(
			WP_CONTENT_DIR . '/contentful-cards/my-card.json',
			\wp_json_encode( [ 'title' => 'My Card' ] )
		);

		$this->loader->load_all_data();

		$this->assertSame( [ 'title' => 'My Card' ], $this->loader->get_card( 'my-card' ) );
	}

	public function test_get_card_rows_source_inline_table_data(): void {
		$rows = $this->loader->get_card_rows( [ 'source' => [ 'dataTable' => [ 'tableData' => [ [ 'a' ], [ '1' ] ] ] ] ] );

		$this->assertSame( [ [ 'a' ], [ '1' ] ], $rows );
	}

	public function test_get_card_rows_source_raw_data(): void {
		$rows = $this->loader->get_card_rows( [ 'rawData' => [ [ 'a' ], [ '1' ] ] ] );

		$this->assertSame( [ [ 'a' ], [ '1' ] ], $rows );
	}

	public function test_get_card_rows_source_cached_csv_file(): void {
		$card_id = 'csv-card-' . \uniqid();
		$this->write_file( WP_CONTENT_DIR . "/contentful-cards/{$card_id}.csv", "a,b\n1,2\n" );

		$rows = $this->loader->get_card_rows( [ 'id' => $card_id ] );

		$this->assertSame( [ [ 'a', 'b' ], [ '1', '2' ] ], $rows );

		\delete_transient( 'ctfl_card_csv_' . \substr( \md5( $card_id ), 0, 16 ) );
	}

	public function test_get_card_rows_source_remote_spreadsheet_downloads_and_caches(): void {
		\add_filter(
			'pre_http_request',
			static function () {
				return [
					'response' => [ 'code' => 200 ],
					'body'     => "a,b\nx,y\n",
				];
			}
		);

		$card_id  = 'remote-card-' . \uniqid();
		$csv_path = WP_CONTENT_DIR . "/contentful-cards/{$card_id}.csv";
		\wp_mkdir_p( \dirname( $csv_path ) );

		$rows = $this->loader->get_card_rows(
			[
				'id'     => $card_id,
				'source' => [ 'type' => 'spreadsheet', 'url' => 'https://example.test/data.csv' ],
			]
		);

		$this->assertSame( [ [ 'a', 'b' ], [ 'x', 'y' ] ], $rows );
		$this->assertFileExists( $csv_path );

		$this->created_files[] = $csv_path;
	}

	public function test_get_card_rows_returns_empty_array_when_no_source_matches(): void {
		$rows = $this->loader->get_card_rows( [] );

		$this->assertSame( [], $rows );
	}
}
