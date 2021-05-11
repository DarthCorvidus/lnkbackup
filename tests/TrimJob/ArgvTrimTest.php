<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
class ArgvTrimTest extends TestCase {
	function setUp() {
		mkdir(__DIR__."/trim");
	}
	
	function tearDown() {
		exec("rm ".escapeshellarg(__DIR__."/trim")." -rf");
	}
	function testConstruct() {
		$model = new ArgvTrim();
		$this->assertInstanceof(ArgvTrim::class, $model);
	}
	
	function testExtractFull() {
		$model = new ArgvTrim();
		$array = array();
		$array[] = "lnkcopy.php";
		$array[] = __DIR__."/trim/";
		$array[] = "--max=10";
		$array[] = "--from=2010-01-01";
		$array[] = "--to=2010-12-31";
		$array[] = "--days=365";
		$array[] = "--weeks=52";
		$array[] = "--months=12";
		$array[] = "--years=1";
		$array[] = "--to=2010-12-31";
		$array[] = "--from=2010-01-01";
		$array[] = "--run";
		
		$argv = new Argv($array, $model);
		$this->assertEquals($array[1], $argv->getPositional(0));
		$this->assertEquals(10, $argv->getValue("max"));
		$this->assertEquals("2010-01-01", $argv->getValue("from"));
		$this->assertEquals("2010-12-31", $argv->getValue("to"));
		$this->assertEquals(365, $argv->getValue("days"));
		$this->assertEquals(52, $argv->getValue("weeks"));
		$this->assertEquals(12, $argv->getValue("months"));
		$this->assertEquals(1, $argv->getValue("years"));
		$this->assertEquals(TRUE, $argv->getBoolean("run"));
		#$this->assertEquals(TRUE, $argv->getBoolean("silent"));
	}
}
