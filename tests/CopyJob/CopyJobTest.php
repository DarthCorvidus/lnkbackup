<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
class CopyJobTest extends TestCase {
	function tearDown() {
		foreach(glob(__DIR__."/../target.empty/*") as $value) {
			if(is_dir($value)) {
				exec("rm -r ".escapeshellarg($value));
			}
		}
	}

	function testConstruct() {
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
		$array[] = "--run";
		$model = new CopyJob($array);
		$this->assertInstanceof(CopyJob::class, $model);
	}
	
	function testCopyPreview() {
		$expected = array("2010-01-01", "2010-01-01.monthly", "2010-01-01.yearly", "2010-01-02", "2010-01-03", "2010-01-03.weekly");
		$array = array();
		$array[] = "lnkcopy.php";
		$array[] = __DIR__."/../target/";
		$array[] = __DIR__."/../target.empty/";
		$array[] = "--daily";
		$array[] = "--weekly";
		$array[] = "--monthly";
		$array[] = "--yearly";
		$job = new CopyJob($array);
		$output = "Entries to be copied:".PHP_EOL;
		foreach($expected as $value) {
			$output .= "\t".$value.PHP_EOL;
		}
		$output .= "Please use --run to copy entries.\n";
		$this->expectOutputString($output);
		$job->run();
		#foreach($expected as $value) {
		#	$this->assertFileExists(__DIR__."/../target.empty/".$value);
		#}
		
	}
	
	function testCopyAll() {
		$expected = array("2010-01-01", "2010-01-01.monthly", "2010-01-01.yearly", "2010-01-02", "2010-01-03", "2010-01-03.weekly");
		$array = array();
		$array[] = "lnkcopy.php";
		$array[] = __DIR__."/../target/";
		$array[] = __DIR__."/../target.empty/";
		$array[] = "--daily";
		$array[] = "--weekly";
		$array[] = "--monthly";
		$array[] = "--yearly";
		$array[] = "--run";
		$job = new CopyJob($array);
		$output = "Entries to be copied:".PHP_EOL;
		foreach($expected as $value) {
			$output .= "\t".$value.PHP_EOL;
		}
		$output .= "First copy 2010-01-01".PHP_EOL;
		
		for($i=1;$i<count($expected); $i++) {
			$output .= "Subsequent copy ".$expected[$i]." → ".$expected[$i-1].PHP_EOL;
		}
		
		$this->expectOutputString($output);
		$job->run();
		foreach($expected as $value) {
			$this->assertFileExists(__DIR__."/../target.empty/".$value);
		}
	}

	function testCopyDaily() {
		$expected = array("2010-01-01", "2010-01-02", "2010-01-03");
		$array = array();
		$array[] = "lnkcopy.php";
		$array[] = __DIR__."/../target/";
		$array[] = __DIR__."/../target.empty/";
		$array[] = "--daily";
		$array[] = "--run";
		$job = new CopyJob($array);
		$output = "Entries to be copied:".PHP_EOL;
		foreach($expected as $value) {
			$output .= "\t".$value.PHP_EOL;
		}
		$output .= "First copy 2010-01-01".PHP_EOL;
		
		for($i=1;$i<count($expected); $i++) {
			$output .= "Subsequent copy ".$expected[$i]." → ".$expected[$i-1].PHP_EOL;
		}
		
		$this->expectOutputString($output);
		$job->run();
		foreach($expected as $value) {
			$this->assertFileExists(__DIR__."/../target.empty/".$value);
		}
	}

	function testCopyWeekly() {
		$expected = array("2010-01-03.weekly");
		$array = array();
		$array[] = "lnkcopy.php";
		$array[] = __DIR__."/../target/";
		$array[] = __DIR__."/../target.empty/";
		$array[] = "--weekly";
		$array[] = "--run";
		$job = new CopyJob($array);
		$output = "Entries to be copied:".PHP_EOL;
		foreach($expected as $value) {
			$output .= "\t".$value.PHP_EOL;
		}
		$output .= "First copy 2010-01-03.weekly".PHP_EOL;
		
		for($i=1;$i<count($expected); $i++) {
			$output .= "Subsequent copy ".$expected[$i]." → ".$expected[$i-1].PHP_EOL;
		}
		
		$this->expectOutputString($output);
		$job->run();
		foreach($expected as $value) {
			$this->assertFileExists(__DIR__."/../target.empty/".$value);
		}
	}
	
	function testCopyMonthly() {
		$expected = array("2010-01-01.monthly");
		$array = array();
		$array[] = "lnkcopy.php";
		$array[] = __DIR__."/../target/";
		$array[] = __DIR__."/../target.empty/";
		$array[] = "--monthly";
		$array[] = "--run";
		$job = new CopyJob($array);
		$output = "Entries to be copied:".PHP_EOL;
		foreach($expected as $value) {
			$output .= "\t".$value.PHP_EOL;
		}
		$output .= "First copy 2010-01-01.monthly".PHP_EOL;
		$this->expectOutputString($output);
		$job->run();
		foreach($expected as $value) {
			$this->assertFileExists(__DIR__."/../target.empty/".$value);
		}
	}

	function testCopyYearly() {
		$expected = array("2010-01-01.yearly");
		$array = array();
		$array[] = "lnkcopy.php";
		$array[] = __DIR__."/../target/";
		$array[] = __DIR__."/../target.empty/";
		$array[] = "--yearly";
		$array[] = "--run";
		$job = new CopyJob($array);
		$output = "Entries to be copied:".PHP_EOL;
		foreach($expected as $value) {
			$output .= "\t".$value.PHP_EOL;
		}
		$output .= "First copy 2010-01-01.yearly".PHP_EOL;
		$this->expectOutputString($output);
		$job->run();
		foreach($expected as $value) {
			$this->assertFileExists(__DIR__."/../target.empty/".$value);
		}
	}
	
	function testCopyFrom() {
		$expected = array("2010-01-02", "2010-01-03", "2010-01-03.weekly");
		$array = array();
		$array[] = "lnkcopy.php";
		$array[] = __DIR__."/../target/";
		$array[] = __DIR__."/../target.empty/";
		$array[] = "--daily";
		$array[] = "--weekly";
		$array[] = "--monthly";
		$array[] = "--yearly";
		$array[] = "--from=2010-01-02";
		$array[] = "--run";
		$job = new CopyJob($array);
		$output = "Entries to be copied:".PHP_EOL;
		foreach($expected as $value) {
			$output .= "\t".$value.PHP_EOL;
		}
		$output .= "First copy 2010-01-02".PHP_EOL;
		
		for($i=1;$i<count($expected); $i++) {
			$output .= "Subsequent copy ".$expected[$i]." → ".$expected[$i-1].PHP_EOL;
		}
		
		$this->expectOutputString($output);
		$job->run();
		foreach($expected as $value) {
			$this->assertFileExists(__DIR__."/../target.empty/".$value);
		}
	}
	
	function testCopyTo() {
		$expected = array("2010-01-01", "2010-01-01.monthly", "2010-01-01.yearly", "2010-01-02");
		$array = array();
		$array[] = "lnkcopy.php";
		$array[] = __DIR__."/../target/";
		$array[] = __DIR__."/../target.empty/";
		$array[] = "--daily";
		$array[] = "--weekly";
		$array[] = "--monthly";
		$array[] = "--yearly";
		$array[] = "--to=2010-01-02";
		$array[] = "--run";
		$job = new CopyJob($array);
		$output = "Entries to be copied:".PHP_EOL;
		foreach($expected as $value) {
			$output .= "\t".$value.PHP_EOL;
		}
		$output .= "First copy 2010-01-01".PHP_EOL;
		
		for($i=1;$i<count($expected); $i++) {
			$output .= "Subsequent copy ".$expected[$i]." → ".$expected[$i-1].PHP_EOL;
		}
		
		$this->expectOutputString($output);
		$job->run();
		foreach($expected as $value) {
			$this->assertFileExists(__DIR__."/../target.empty/".$value);
		}
	}



}
