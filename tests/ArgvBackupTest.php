<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
class ArgvBackupTest extends TestCase {
	function testConfig() {
		$argv[] = "lnkbackup.php";
		$argv[] = __DIR__."/conf.d/";
		$model = new ArgvBackup();
		$param = new Argv($argv, $model);
		$this->assertEquals(__DIR__."/conf.d/", $param->getPositional(0));
	}
	
	function testConfigInvalid() {
		$argv[] = "lnkbackup.php";
		$argv[] = __DIR__."/conf/";
		$model = new ArgvBackup();
		$this->expectException(ArgvException::class);
		$param = new Argv($argv, $model);
	}
	
	function testForceDateDefault() {
		$argv[] = "lnkbackup.php";
		$argv[] = __DIR__."/conf.d/";
		$model = new ArgvBackup();
		$param = new Argv($argv, $model);
		$this->assertEquals(date("Y-m-d"), $param->getValue("force-date"));
	}
	
	function testForceDate() {
		$argv[] = "lnkbackup.php";
		$argv[] = __DIR__."/conf.d/";
		$argv[] = "--force-date=2010-01-01";
		$model = new ArgvBackup();
		$param = new Argv($argv, $model);
		$this->assertEquals("2010-01-01", $param->getValue("force-date"));
	}

}
