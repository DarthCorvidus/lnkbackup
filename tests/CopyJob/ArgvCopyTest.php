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
		$array[] = "--from=2010-01-01";
		$array[] = "--to=2010-12-31";
		$array[] = "--daily";
		$array[] = "--weekly";
		$array[] = "--monthly";
		$array[] = "--yearly";
		$array[] = "--progress";
		$array[] = "--silent";
		$array[] = "--run";
		
		$argv = new Argv($array, $model);
		
		$this->assertEquals($array[1], $argv->getPositional(0));
		$this->assertEquals($array[2], $argv->getPositional(1));
		$this->assertEquals(10, $argv->getValue("max"));
		$this->assertEquals("2010-01-01", $argv->getValue("from"));
		$this->assertEquals("2010-12-31", $argv->getValue("to"));
		$this->assertEquals(TRUE, $argv->getBoolean("daily"));
		$this->assertEquals(TRUE, $argv->getBoolean("weekly"));
		$this->assertEquals(TRUE, $argv->getBoolean("monthly"));
		$this->assertEquals(TRUE, $argv->getBoolean("yearly"));
		$this->assertEquals(TRUE, $argv->getBoolean("silent"));
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
		$this->expectExceptionMessage("--max:");
		$model = new ArgvCopy();
		$argv = new Argv($array, $model);
	}

	function testFromBogus() {
		$array[] = "lnkcopy.php";
		$array[] = __DIR__."/../target/";
		$array[] = __DIR__."/../target.empty/";
		$array[] = "--from=Sometimes";
		
		$this->expectException(ArgvException::class);
		$this->expectExceptionMessage("--from:");
		$model = new ArgvCopy();
		$argv = new Argv($array, $model);
	}
	
	function testToBogus() {
		$array[] = "lnkcopy.php";
		$array[] = __DIR__."/../target/";
		$array[] = __DIR__."/../target.empty/";
		$array[] = "--to=Sometimes";
		
		$this->expectException(ArgvException::class);
		$this->expectExceptionMessage("--to:");
		$model = new ArgvCopy();
		$argv = new Argv($array, $model);
	}

}
