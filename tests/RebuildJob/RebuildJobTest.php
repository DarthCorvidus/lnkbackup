<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
class RebuildJobTest extends TestCase {
	protected function setUp() {
		mkdir(__DIR__."/rebuild");
		$date = JulianDate::fromString("2017-01-01");
		for($i=0;$i<10;$i++) {
			mkdir(__DIR__."/rebuild/".$date->getIsodate());
			$date = $date->addUnit(1, JulianDate::DAY);
		}
	}
	
	function tearDown() {
		exec("rm ".escapeshellarg(__DIR__."/rebuild")." -rf");
	}

	/**
	 * Test Rebuild All Preview
	 * 
	 * Shows preview of which entries would be created as well as a message to
	 * use --run.
	 */
	function testRebuildAllPreview() {
		$expect = array("2017-01-01.weekly", "2017-01-01.monthly", "2017-01-01.yearly", "2017-01-08.weekly");
		$model = new ArgvRebuild();
		$array = array();
		$array[] = "lnkcopy.php";
		$array[] = __DIR__."/rebuild";
		$array[] = "--weekly";
		$array[] = "--monthly";
		$array[] = "--yearly";
		
		$job = RebuildJob::fromArgv($array);
		$output = "";
		
		foreach($expect as $value) {
			$datepart = substr($value, 0, strpos($value, "."));
			$output .= $datepart." to ".$value.PHP_EOL;
		}
		$output .= "Please use --run to rebuild entries.".PHP_EOL;
		$this->expectOutputString($output);
		$job->run();
	}

	
	/**
	 * Test Rebuild All
	 * 
	 * Rebuild all periodic entries for a backup folder.
	 */
	function testRebuildAll() {
		$expect = array("2017-01-01.weekly", "2017-01-01.monthly", "2017-01-01.yearly", "2017-01-08.weekly");
		$model = new ArgvRebuild();
		$array = array();
		$array[] = "lnkcopy.php";
		$array[] = __DIR__."/rebuild";
		$array[] = "--weekly";
		$array[] = "--monthly";
		$array[] = "--yearly";
		$array[] = "--run";
		
		$job = RebuildJob::fromArgv($array);
		$output = "";
		
		foreach($expect as $value) {
			$datepart = substr($value, 0, strpos($value, "."));
			$output .= "cp ".escapeshellarg(__DIR__."/rebuild/".$datepart)." ".escapeshellarg(__DIR__."/rebuild/temp.rebuild")." ".escapeshellarg("-al").PHP_EOL;
			$output .= "mv ".escapeshellarg(__DIR__."/rebuild/temp.rebuild")." ".escapeshellarg(__DIR__."/rebuild/".$value).PHP_EOL;
			$output .= PHP_EOL;
		}
		$this->expectOutputString($output);
		$job->run();
		foreach($expect as $value) {
			$this->assertFileExists(__DIR__."/rebuild/".$value);
		}
	}

	/**
	 * Test rebuild all cleanup
	 * 
	 * Assumes that a temp.rebuild has been left over from a previous run, which
	 * has to be deleted first.
	 */
	function testRebuildAllCleanup() {
		$expect = array("2017-01-01.weekly", "2017-01-01.monthly", "2017-01-01.yearly", "2017-01-08.weekly");
		mkdir(__DIR__."/rebuild/temp.rebuild");
		$model = new ArgvRebuild();
		$array = array();
		$array[] = "lnkcopy.php";
		$array[] = __DIR__."/rebuild";
		$array[] = "--weekly";
		$array[] = "--monthly";
		$array[] = "--yearly";
		$array[] = "--run";
		
		$job = RebuildJob::fromArgv($array);
		$job->run();
		$output = "";
		$output .= "Skipping over 'temp.rebuild': invalid isodate, must be YYYY-MM-DD\n";
		$output .= "rm ".escapeshellarg(__DIR__."/rebuild/temp.rebuild")." ".escapeshellarg("-rf").PHP_EOL;
		foreach($expect as $value) {
			$datepart = substr($value, 0, strpos($value, "."));
			$output .= "cp ".escapeshellarg(__DIR__."/rebuild/".$datepart)." ".escapeshellarg(__DIR__."/rebuild/temp.rebuild")." ".escapeshellarg("-al").PHP_EOL;
			$output .= "mv ".escapeshellarg(__DIR__."/rebuild/temp.rebuild")." ".escapeshellarg(__DIR__."/rebuild/".$value).PHP_EOL;
			$output .= PHP_EOL;
		}
		$this->expectOutputString($output);
		$job->run();
		foreach($expect as $value) {
			$this->assertFileExists(__DIR__."/rebuild/".$value);
		}
	}

	/**
	 * Test rebuild max
	 * 
	 * Test to rebuild a maximum amount of entries in one run.
	 */
	function testRebuildMax() {
		$expect = array("2017-01-01.weekly", "2017-01-01.monthly");
		$model = new ArgvRebuild();
		$array = array();
		$array[] = "lnkcopy.php";
		$array[] = __DIR__."/rebuild";
		$array[] = "--weekly";
		$array[] = "--monthly";
		$array[] = "--yearly";
		$array[] = "--max=2";
		$array[] = "--run";
		
		$job = RebuildJob::fromArgv($array);
		$job->run();
		$output = "";
		foreach($expect as $value) {
			$datepart = substr($value, 0, strpos($value, "."));
			$output .= "cp ".escapeshellarg(__DIR__."/rebuild/".$datepart)." ".escapeshellarg(__DIR__."/rebuild/temp.rebuild")." ".escapeshellarg("-al").PHP_EOL;
			$output .= "mv ".escapeshellarg(__DIR__."/rebuild/temp.rebuild")." ".escapeshellarg(__DIR__."/rebuild/".$value).PHP_EOL;
			$output .= PHP_EOL;
		}
		$this->expectOutputString($output);
		$job->run();
		foreach($expect as $value) {
			$this->assertFileExists(__DIR__."/rebuild/".$value);
		}

	}

	/**
	 * Test rebuild weekly
	 * 
	 * Test to rebuild weekly entries only.
	 */
	function testRebuildWeekly() {
		$expect = array("2017-01-01.weekly", "2017-01-08.weekly");
		$model = new ArgvRebuild();
		$array = array();
		$array[] = "lnkcopy.php";
		$array[] = __DIR__."/rebuild";
		$array[] = "--weekly";
		$array[] = "--run";
		
		$job = RebuildJob::fromArgv($array);
		$output = "";
		
		foreach($expect as $value) {
			$datepart = substr($value, 0, strpos($value, "."));
			$output .= "cp ".escapeshellarg(__DIR__."/rebuild/".$datepart)." ".escapeshellarg(__DIR__."/rebuild/temp.rebuild")." ".escapeshellarg("-al").PHP_EOL;
			$output .= "mv ".escapeshellarg(__DIR__."/rebuild/temp.rebuild")." ".escapeshellarg(__DIR__."/rebuild/".$value).PHP_EOL;
			$output .= PHP_EOL;
		}
		$this->expectOutputString($output);
		$job->run();
		foreach($expect as $value) {
			$this->assertFileExists(__DIR__."/rebuild/".$value);
		}
	}

	/**
	 * Test rebuild monthly
	 * 
	 * Test to rebuild monthly entries only.
	 */
	function testRebuildMonthly() {
		$expect = array("2017-01-01.monthly");
		$array = array();
		$array[] = "lnkcopy.php";
		$array[] = __DIR__."/rebuild";
		$array[] = "--monthly";
		$array[] = "--run";
		
		$job = RebuildJob::fromArgv($array);
		$output = "";
		
		foreach($expect as $value) {
			$datepart = substr($value, 0, strpos($value, "."));
			$output .= "cp ".escapeshellarg(__DIR__."/rebuild/".$datepart)." ".escapeshellarg(__DIR__."/rebuild/temp.rebuild")." ".escapeshellarg("-al").PHP_EOL;
			$output .= "mv ".escapeshellarg(__DIR__."/rebuild/temp.rebuild")." ".escapeshellarg(__DIR__."/rebuild/".$value).PHP_EOL;
			$output .= PHP_EOL;
		}
		$this->expectOutputString($output);
		$job->run();
		foreach($expect as $value) {
			$this->assertFileExists(__DIR__."/rebuild/".$value);
		}
	}
	
	/**
	 * Test rebuild yearly
	 * 
	 * Test to rebuild yearly entries only
	 */
	function testRebuildYearly() {
		$expect = array("2017-01-01.yearly");
		$array = array();
		$array[] = "lnkcopy.php";
		$array[] = __DIR__."/rebuild";
		$array[] = "--yearly";
		$array[] = "--run";
		
		$job = RebuildJob::fromArgv($array);
		$output = "";
		
		foreach($expect as $value) {
			$datepart = substr($value, 0, strpos($value, "."));
			$output .= "cp ".escapeshellarg(__DIR__."/rebuild/".$datepart)." ".escapeshellarg(__DIR__."/rebuild/temp.rebuild")." ".escapeshellarg("-al").PHP_EOL;
			$output .= "mv ".escapeshellarg(__DIR__."/rebuild/temp.rebuild")." ".escapeshellarg(__DIR__."/rebuild/".$value).PHP_EOL;
			$output .= PHP_EOL;
		}
		$this->expectOutputString($output);
		$job->run();
		foreach($expect as $value) {
			$this->assertFileExists(__DIR__."/rebuild/".$value);
		}
	}
}
