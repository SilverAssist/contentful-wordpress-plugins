<?php
/**
 * Tests for CsvParser (pure PHP, no WordPress dependency).
 *
 * @package SilverAssist\ContentfulTables\Tests\Unit
 */

namespace SilverAssist\ContentfulTables\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SilverAssist\ContentfulTables\Utils\CsvParser;

/**
 * @covers \SilverAssist\ContentfulTables\Utils\CsvParser
 */
class CsvParserTest extends TestCase {

	public function test_parse_simple_csv(): void {
		$rows = CsvParser::parse( "a,b,c\n1,2,3" );

		$this->assertSame( [ [ 'a', 'b', 'c' ], [ '1', '2', '3' ] ], $rows );
	}

	public function test_parse_quoted_fields_with_commas(): void {
		$rows = CsvParser::parse( 'name,note' . "\n" . '"Acme, Inc.",hello' );

		$this->assertSame( [ [ 'name', 'note' ], [ 'Acme, Inc.', 'hello' ] ], $rows );
	}

	public function test_parse_escaped_quotes_inside_quoted_field(): void {
		$rows = CsvParser::parse( 'quote' . "\n" . '"She said ""hi""."' );

		$this->assertSame( [ [ 'quote' ], [ 'She said "hi".' ] ], $rows );
	}

	public function test_parse_multiline_value_inside_quotes(): void {
		$csv = "note\n\"Line one\nLine two\"";

		$rows = CsvParser::parse( $csv );

		$this->assertSame( [ [ 'note' ], [ "Line one\nLine two" ] ], $rows );
	}

	public function test_parse_crlf_line_endings(): void {
		$rows = CsvParser::parse( "a,b\r\n1,2\r\n" );

		$this->assertSame( [ [ 'a', 'b' ], [ '1', '2' ] ], $rows );
	}

	public function test_parse_strips_utf8_bom(): void {
		$rows = CsvParser::parse( "\xEF\xBB\xBFa,b\n1,2" );

		$this->assertSame( 'a', $rows[0][0] );
	}

	public function test_parse_filters_fully_empty_rows(): void {
		$rows = CsvParser::parse( "a,b\n,\n1,2" );

		$this->assertSame( [ [ 'a', 'b' ], [ '1', '2' ] ], $rows );
	}

	public function test_parse_captures_last_row_without_trailing_newline(): void {
		$rows = CsvParser::parse( "a,b\n1,2" );

		$this->assertCount( 2, $rows );
		$this->assertSame( [ '1', '2' ], $rows[1] );
	}

	public function test_parse_trims_cell_whitespace(): void {
		$rows = CsvParser::parse( "a, b ,c\n" );

		$this->assertSame( [ 'a', 'b', 'c' ], $rows[0] );
	}
}
