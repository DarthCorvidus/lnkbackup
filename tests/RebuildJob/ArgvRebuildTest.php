<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
class ArgvRebuildTest extends TestCase {
	
	function setUp() {
		mkdir(__DIR__."/rebuild/");
	}
	
	function tearDown() {
		exec("rm ".escapeshellarg(__DIR__."/rebuild")." -rf");
	}
	
	function testConstruct() {
		$model = new ArgvRebuild();
		$this->assertInstanceof(ArgvRebuild::class, $model);
	}
	
	function testExtractFull() {
		$model = new ArgvRebuild();
		$array = array();
		$array[] = "lnkrebuild.php";
		$array[] = __DIR__."/rebuild/";
		$array[] = "--weekly";
		$array[] = "--monthly";
		$array[] = "--yearly";
		$array[] = "--max=5";
		$array[] = "--run";
		
		$argv = new Argv($array, $model);
		
		$this->assertEquals($array[1], $argv->getPositional(0));
		$this->assertEquals(TRUE, $argv->getBoolean("weekly"));
		$this->assertEquals(TRUE, $argv->getBoolean("monthly"));
		$this->assertEquals(TRUE, $argv->getBoolean("yearly"));
		$this->assertEquals(5, $argv->getValue("max"));
		$this->assertEquals(TRUE, $argv->getBoolean("run"));
	}
	
	function testInvalidMax() {
		$model = new ArgvRebuild();
		$array = array();
		$array[] = "lnkrebuild.php";
		$array[] = __DIR__."/rebuild/";
		$array[] = "--max=bogus";
		
		$this->expectException(ArgvException::class);
		$this->expectExceptionMessage("--max: not a valid integer");
		$argv = new Argv($array, $model);
	}

}
