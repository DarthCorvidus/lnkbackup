<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
class ArgvCopyTest extends TestCase {
	function testConstruct() {
		$model = new ArgvCopy();
		$this->assertInstanceof(ArgvCopy::class, $model);
	}
	
	function testExtractFull() {
		$model = new ArgvCopy();
		$array = array();
		$array[] = "lnkcopy.php";
		$array[] = __DIR__."/../target/";
		$array[] = __DIR__."/../target.empty/";
		$array[] = "--max=10";
		$array[] = "--daily";
		$array[] = "--weekly";
		$array[] = "--monthly";
		$array[] = "--yearly";
		$array[] = "--progress";
		$array[] = "--run";
		
		$argv = new Argv($array, $model);
		
		$this->assertEquals($array[1], $argv->getPositional(0));
		$this->assertEquals($array[2], $argv->getPositional(1));
		$this->assertEquals(10, $argv->getValue("max"));
		$this->assertEquals(TRUE, $argv->getBoolean("daily"));
		$this->assertEquals(TRUE, $argv->getBoolean("weekly"));
		$this->assertEquals(TRUE, $argv->getBoolean("monthly"));
		$this->assertEquals(TRUE, $argv->getBoolean("yearly"));
		$this->assertEquals(TRUE, $argv->getBoolean("run"));
	}
	
	function testInvalidSource() {
		$array[] = "lnkcopy.php";
		$array[] = __DIR__."/../target.full/";
		$array[] = __DIR__."/../target.empty/";
		
		$this->expectException(ArgvException::class);
		$model = new ArgvCopy();
		$argv = new Argv($array, $model);
		
	}
	
	function testInvalidTarget() {
		$array[] = "lnkcopy.php";
		$array[] = __DIR__."/../target/";
		$array[] = __DIR__."/../target.somewhere/";
		
		$this->expectException(ArgvException::class);
		$model = new ArgvCopy();
		$argv = new Argv($array, $model);
	}
	
	function testMaxString() {
		$array[] = "lnkcopy.php";
		$array[] = __DIR__."/../target/";
		$array[] = __DIR__."/../target.empty/";
		$array[] = "--max=fifteen";
		
		$this->expectException(ArgvException::class);
		$model = new ArgvCopy();
		$argv = new Argv($array, $model);
		
	}

	function testMaxFloat() {
		$array[] = "lnkcopy.php";
		$array[] = __DIR__."/../target/";
		$array[] = __DIR__."/../target.empty/";
		$array[] = "--max=15.3";
		
		$this->expectException(ArgvException::class);
		$model = new ArgvCopy();
		$argv = new Argv($array, $model);
	}

}
