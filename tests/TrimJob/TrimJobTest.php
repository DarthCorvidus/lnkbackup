<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
class TrimJobTest extends TestCase {
	function setUp() {
		mkdir(__DIR__."/trim");
		$date = JulianDate::fromString("2010-01-01");
		for($i=0;$i<365;$i++) {
			mkdir(__DIR__."/trim/".$date->getFormat("Y-m-d"));
			$date = $date->addUnit(1, JulianDate::DAY);
		}
		$argv[] = "xxx";
		$argv[] = __DIR__."/trim";
		$argv[] = "--run";
		$argv[] = "--weekly";
		$argv[] = "--monthly";
		$argv[] = "--yearly";
		$argv[] = "--silent";
		
		$rebuild = new RebuildJob($argv);
		$rebuild->run();
	}
	
	function tearDown() {
		exec("rm ".escapeshellarg(__DIR__."/trim")." -rf");
	}
	
	function testConstruct() {
		$argv[] = "xxx";
		$argv[] = __DIR__."/trim";
		$trim = new TrimJob($argv);
		$this->assertInstanceOf(TrimJob::class, $trim);
	}
	
	function testDetermineDeleteAll() {
		$argv[] = "xxx";
		$argv[] = __DIR__."/trim";
		$argv[] = "--days=0";
		$argv[] = "--weeks=0";
		$argv[] = "--months=0";
		$argv[] = "--years=0";
		$argv[] = "--to=2010-12-31";
		$argv[] = "--run";
		$trim = new TrimJob($argv);
		$expect = array();
		foreach(glob(__DIR__."/trim/*") as $value) {
			$expect[] = $value;
		}
		$this->assertEquals($expect, $trim->determineDelete());
	}

	function testDetermineDeleteAllMax() {
		$argv[] = "xxx";
		$argv[] = __DIR__."/trim";
		$argv[] = "--days=0";
		$argv[] = "--weeks=0";
		$argv[] = "--months=0";
		$argv[] = "--years=0";
		$argv[] = "--to=2010-12-31";
		$argv[] = "--max=10";
		$argv[] = "--run";
		$trim = new TrimJob($argv);
		$expect = array();
		foreach(glob(__DIR__."/trim/*") as $value) {
			$expect[] = $value;
		}
		$expect = array_slice($expect, 0, 10);
		$this->assertEquals($expect, $trim->determineDelete());
	}
	
	function testDetermineDeleteDays() {
		$argv[] = "xxx";
		$argv[] = __DIR__."/trim";
		$argv[] = "--days=0";
		$argv[] = "--to=2010-12-31";
		$trim = new TrimJob($argv);
		$expect = array();
		foreach(glob(__DIR__."/trim/*") as $value) {
			if(!preg_match("/^2010-[0-9]{2}-[0-9]{2}$/", basename($value))) {
				continue;
			}
			$expect[] =$value;
		}
		$this->assertEquals($expect, $trim->determineDelete());
	}
	
	function testDetermineKeepFortnight() {
		$argv[] = "xxx";
		$argv[] = __DIR__."/trim";
		$argv[] = "--days=14";
		$argv[] = "--to=2010-12-31";
		$trim = new TrimJob($argv);
		$expect = array();
		foreach(glob(__DIR__."/trim/*") as $value) {
			if(!preg_match("/^2010-[0-9]{2}-[0-9]{2}$/", basename($value))) {
				continue;
			}
			$expect[] =$value;
		}
		$expect = array_slice($expect, 0, 365-14);
		$this->assertEquals($expect, $trim->determineDelete());
	}

	function testDetermineDeleteWeeks() {
		$argv[] = "xxx";
		$argv[] = __DIR__."/trim";
		$argv[] = "--weeks=0";
		$argv[] = "--to=2010-12-31";
		$trim = new TrimJob($argv);
		$expect = array();
		foreach(glob(__DIR__."/trim/*") as $value) {
			if(!preg_match("/^2010-[0-9]{2}-[0-9]{2}\.weekly$/", basename($value))) {
				continue;
			}
			$expect[] =$value;
		}
		$this->assertEquals($expect, $trim->determineDelete());
	}
	
	function testDetermineKeepWeeks() {
		$argv[] = "xxx";
		$argv[] = __DIR__."/trim";
		$argv[] = "--weeks=4";
		$argv[] = "--to=2010-12-31";
		$trim = new TrimJob($argv);
		$expect = array();
		$keep = array("2010-12-05.weekly", "2010-12-12.weekly", "2010-12-19.weekly", "2010-12-26.weekly");
		foreach(glob(__DIR__."/trim/*") as $value) {
			if(!preg_match("/^2010-[0-9]{2}-[0-9]{2}\.weekly$/", basename($value))) {
				continue;
			}
			if(in_array(basename($value), $keep)) {
				continue;
			}
			$expect[] = $value;
		}
		$this->assertEquals($expect, $trim->determineDelete());
	}

	function testDetermineDeleteMonths() {
		$argv[] = "xxx";
		$argv[] = __DIR__."/trim";
		$argv[] = "--months=0";
		$argv[] = "--to=2010-12-31";
		$trim = new TrimJob($argv);
		$expect = array();
		foreach(glob(__DIR__."/trim/*") as $value) {
			if(!preg_match("/^2010-[0-9]{2}-[0-9]{2}\.monthly$/", basename($value))) {
				continue;
			}
			$expect[] =$value;
		}
		$this->assertEquals($expect, $trim->determineDelete());
	}
	
	function testDetermineKeepMonths() {
		$argv[] = "xxx";
		$argv[] = __DIR__."/trim";
		$argv[] = "--months=6";
		$argv[] = "--to=2010-12-31";
		$trim = new TrimJob($argv);
		$expect = array();
		$keep = array("2010-07-01.monthly", "2010-08-01.monthly", "2010-09-01.monthly", "2010-10-01.monthly", "2010-11-01.monthly", "2010-12-01.monthly");
		foreach(glob(__DIR__."/trim/*") as $value) {
			if(!preg_match("/^2010-[0-9]{2}-[0-9]{2}\.monthly$/", basename($value))) {
				continue;
			}
			if(in_array(basename($value), $keep)) {
				continue;
			}
			$expect[] = $value;
		}
		$this->assertEquals($expect, $trim->determineDelete());
	}

	function testDetermineDeleteYears() {
		$argv[] = "xxx";
		$argv[] = __DIR__."/trim";
		$argv[] = "--years=0";
		$argv[] = "--to=2010-12-31";
		$trim = new TrimJob($argv);
		$expect = array();
		foreach(glob(__DIR__."/trim/*") as $value) {
			if(!preg_match("/^2010-[0-9]{2}-[0-9]{2}\.yearly$/", basename($value))) {
				continue;
			}
			$expect[] =$value;
		}
		$this->assertEquals($expect, $trim->determineDelete());
	}
	
	function testDetermineKeepYears() {
		$argv[] = "xxx";
		$argv[] = __DIR__."/trim";
		$argv[] = "--years=1";
		$argv[] = "--to=2010-12-31";
		$trim = new TrimJob($argv);
		$expect = array();
		$keep = array("2010-01-01.yearly");
		foreach(glob(__DIR__."/trim/*") as $value) {
			if(!preg_match("/^2010-[0-9]{2}-[0-9]{2}\.yearly$/", basename($value))) {
				continue;
			}
			if(in_array(basename($value), $keep)) {
				continue;
			}
			$expect[] = $value;
		}
		$this->assertEquals($expect, $trim->determineDelete());
	}
}
