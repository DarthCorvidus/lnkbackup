<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
class BackupJobTest extends TestCase {
	/**
	 * Test backup to empty
	 * 
	 * Test backing up to an empty folder, using --force-date to simulate
	 * several daily runs. Test if every expected folder is created accordingly.
	 * When calling a job on an empty directory, the first Job must not use
	 * --link-dest.
	 * 
	 */
	function testBackupToEmpty() {
		$dates = array("2010-01-01", "2010-01-02", "2010-01-03");
		$target = array("2010-01-01", "2010-01-01.monthly", "2010-01-01.yearly", "2010-01-02", "2010-01-03", "2010-01-03.weekly");
		foreach($dates as $key => $value) {
			$argv = array();
			$argv[] = "lnkbackup.php";
			$argv[] = __DIR__."/conf.d/local-empty.conf";
			$argv[] = "--force-date=".$value;
			$argv[] = "--silent";
			$model = new ArgvBackup();
			$args = new Argv($argv, $model);
			$config = JobConfig::fromFile(__DIR__."/conf.d/local-empty.conf");
			$backup = new BackupJob($config, $args);
			$this->expectOutputString("");
			$backup->execute();
		}
		foreach($target as $value) {
			$this->assertFileExists(__DIR__."/target.empty/".$value);
		}
	}
	
	function tearDown() {
		foreach(glob(__DIR__."/target.empty/*") as $value) {
			if(is_dir($value)) {
				exec("rm -r ".escapeshellarg($value));
			}
		}
		
	}
	
	/**
	 * Test Backup to same empty
	 * 
	 * Assume that a job was called on an empty target directory and only one
	 * value was copied over. If called again, lnkbackup should only look for
	 * differences instead, using --delete, without using --link-dest.
	 */
	function testBackupToSameEmpty() {
		exec("mkdir ".escapeshellarg(__DIR__."/target.empty/2010-01-01"));
		$argv = array();
		$argv[] = "lnkbackup.php";
		$argv[] = __DIR__."/conf.d/local-empty.conf";
		$argv[] = "--force-date=2010-01-01";
		$argv[] = "--silent";
		$model = new ArgvBackup();
		$args = new Argv($argv, $model);
		$config = JobConfig::fromFile(__DIR__."/conf.d/local-empty.conf");
		$backup = new BackupJob($config, $args);
		$this->expectOutputString("");
		$backup->execute();
	}
	
	/**
	 * Test Backup To Same Filled
	 * 
	 * Same as above, but with using --link-dest.
	 */
	function testBackupToSameFilled() {
		exec("mkdir ".escapeshellarg(__DIR__."/target.empty/2010-01-01"));
		exec("mkdir ".escapeshellarg(__DIR__."/target.empty/2010-01-02"));
		exec("touch ".escapeshellarg(__DIR__."/target.empty/2010-01-02/file02.txt"));
		$argv = array();
		$argv[] = "lnkbackup.php";
		$argv[] = __DIR__."/conf.d/local-empty.conf";
		$argv[] = "--force-date=2010-01-02";
		$argv[] = "--silent";
		$model = new ArgvBackup();
		$args = new Argv($argv, $model);
		$config = JobConfig::fromFile(__DIR__."/conf.d/local-empty.conf");
		$backup = new BackupJob($config, $args);
		$this->expectOutputString("");
		$backup->execute();
		$this->assertFileExists(__DIR__."/target.empty/2010-01-02/subdir");
		$this->assertFileExists(__DIR__."/target.empty/2010-01-02/file.txt");
		$this->assertEquals(FALSE, file_exists(__DIR__."/target.empty/2010-01-02/file02.txt"));
	}

}
