<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
class BackupJobTest extends TestCase {
	function getBackupJob($forceDate): BackupJob {
		$argv = array();
		$argv[] = "lnkbackup.php";
		$argv[] = __DIR__."/conf.d/local-empty.conf";
		$argv[] = "--force-date=".$forceDate;
		$argv[] = "--silent";
		$model = new ArgvBackup();
		$args = new Argv($argv, $model);
		$config = JobConfig::fromFile(__DIR__."/conf.d/local-empty.conf");
		$backup = new BackupJob($config, $args);
	return $backup;
	}

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

	function getCopyPeriodicWeeklyNonexistent() {
		$job = $this->getBackupJob("2010-01-03");
		$commands = $job->getCopyPeriodic("weekly");
		$expected[] = "cp ". escapeshellarg("tests/target.empty/substring")." ".escapeshellargs("tests/target.empty/substring");
	}
	
	/**
	 * Test get rsync for empty
	 * 
	 * If a backup folder is completely empty, lnkbackup will backup to
	 * temp.create and then move to current/forced date daily entry.
	 * Note that --delete is used, should temp.create already exist.
	 */
	function testGetRsyncForEmpty() {
		$command[] = "rsync";
		$command[] = escapeshellarg("tests/source/");
		$command[] = escapeshellarg("tests/target.empty//temp.create");
		$command[] = escapeshellarg("-avz");
		$command[] = escapeshellarg("--exclude-from")."=".escapeshellarg(__DIR__."/conf.d/exclude-local.txt");
		$command[] = escapeshellarg("--delete");
		$job = $this->getBackupJob("2010-01-01");
		$expected[] = implode(" ", $command);
		$expected[] = "mv ".escapeshellarg("tests/target.empty//temp.create")." ".escapeshellarg("tests/target.empty//2010-01-01");
		$commands = $job->getBackupOnEmptyCommands();
		$this->assertEquals($expected[0], $commands[0]->buildCommand());
		$this->assertEquals($expected[1], $commands[1]->buildCommand());
	}

	/**
	 * Test get rsync for empty same
	 * 
	 * If a backup folder contains the same entry as for current/forced date,
	 * lnkbackup will copy over existing entry using --delete.
	 * without backup entries.
	 */
	function testGetRsyncForEmptySame() {
		exec("mkdir ".__DIR__."/target.empty/2010-01-01");
		$command[] = "rsync";
		$command[] = escapeshellarg("tests/source/");
		$command[] = escapeshellarg("tests/target.empty//2010-01-01");
		$command[] = escapeshellarg("-avz");
		$command[] = escapeshellarg("--exclude-from")."=".escapeshellarg(__DIR__."/conf.d/exclude-local.txt");
		$command[] = escapeshellarg("--delete");
		$job = $this->getBackupJob("2010-01-01");
		$expected[] = implode(" ", $command);
		$commands = $job->getBackupOnEmptyCommands();
		$this->assertEquals($expected[0], $commands[0]->buildCommand());
	}
	
	function testGetRemoveCopy() {
		$file = __DIR__."/target.empty/2010-01-01.monthly";
		exec("mkdir ". escapeshellarg($file));
		$expected = "rm ".escapeshellarg($file)." ".escapeshellarg("-rf");
		
		$job = $this->getBackupJob("2010-01-01");
		$commands = $job->getRemoveCopy($file);
		$this->assertEquals($expected, $commands[0]->buildCommand());
	}
	
	function testCopyPeriodicMonthly() {
		$file = __DIR__."/target.empty/2010-01-01";
		exec("mkdir ". escapeshellarg($file));
		$job = $this->getBackupJob("2010-01-01");
		$array = $job->getCopyPeriodic("monthly");
		$expect[] = "cp ".escapeshellarg("tests/target.empty//2010-01-01")." ".escapeshellarg("tests/target.empty//temp.monthly")." ". escapeshellarg("-al");
		$expect[] = "mv ".escapeshellarg("tests/target.empty//temp.monthly")." ".escapeshellarg("tests/target.empty//2010-01-01.monthly");
		$this->assertEquals($expect[0], $array[0]->buildCommand());
		$this->assertEquals($expect[1], $array[1]->buildCommand());
		$this->assertEquals(2, count($array));
	}
	
	function testGetCopyWMY() {
		$expect[] = "cp ".escapeshellarg("tests/target.empty//2017-01-01")." ".escapeshellarg("tests/target.empty//temp.weekly")." ". escapeshellarg("-al");
		$expect[] = "mv ".escapeshellarg("tests/target.empty//temp.weekly")." ".escapeshellarg("tests/target.empty//2017-01-01.weekly");

		$expect[] = "cp ".escapeshellarg("tests/target.empty//2017-01-01")." ".escapeshellarg("tests/target.empty//temp.monthly")." ". escapeshellarg("-al");
		$expect[] = "mv ".escapeshellarg("tests/target.empty//temp.monthly")." ".escapeshellarg("tests/target.empty//2017-01-01.monthly");

		$expect[] = "cp ".escapeshellarg("tests/target.empty//2017-01-01")." ".escapeshellarg("tests/target.empty//temp.yearly")." ". escapeshellarg("-al");
		$expect[] = "mv ".escapeshellarg("tests/target.empty//temp.yearly")." ".escapeshellarg("tests/target.empty//2017-01-01.yearly");
		
		$file = __DIR__."/target.empty/2017-01-01";
		exec("mkdir ". escapeshellarg($file));
		
		$job = $this->getBackupJob("2017-01-01");
		$array = $job->getCopyWMY("2017-01-01");
		foreach($expect as $key => $value) {
			$this->assertEquals($value, $array[$key]->buildCommand());
		}
		$this->assertEquals(6, count($array));
	}
	
	function testGetCopyWMYPreexisting() {
		$expect[] = "rm ".escapeshellarg("tests/target.empty//2017-01-01.weekly")." ".escapeshellarg("-rf");
		$expect[] = "cp ".escapeshellarg("tests/target.empty//2017-01-01")." ".escapeshellarg("tests/target.empty//temp.weekly")." ". escapeshellarg("-al");
		$expect[] = "mv ".escapeshellarg("tests/target.empty//temp.weekly")." ".escapeshellarg("tests/target.empty//2017-01-01.weekly");

		$expect[] = "rm ".escapeshellarg("tests/target.empty//2017-01-01.monthly")." ".escapeshellarg("-rf");
		$expect[] = "cp ".escapeshellarg("tests/target.empty//2017-01-01")." ".escapeshellarg("tests/target.empty//temp.monthly")." ". escapeshellarg("-al");
		$expect[] = "mv ".escapeshellarg("tests/target.empty//temp.monthly")." ".escapeshellarg("tests/target.empty//2017-01-01.monthly");

		$expect[] = "rm ".escapeshellarg("tests/target.empty//2017-01-01.yearly")." ".escapeshellarg("-rf");
		$expect[] = "cp ".escapeshellarg("tests/target.empty//2017-01-01")." ".escapeshellarg("tests/target.empty//temp.yearly")." ". escapeshellarg("-al");
		$expect[] = "mv ".escapeshellarg("tests/target.empty//temp.yearly")." ".escapeshellarg("tests/target.empty//2017-01-01.yearly");
		
		$file = __DIR__."/target.empty/2017-01-01";
		exec("mkdir ". escapeshellarg($file));
		exec("mkdir ". escapeshellarg($file.".weekly"));
		exec("mkdir ". escapeshellarg($file.".monthly"));
		exec("mkdir ". escapeshellarg($file.".yearly"));
		
		$job = $this->getBackupJob("2017-01-01");
		$array = $job->getCopyWMY("2017-01-01");
		foreach($expect as $key => $value) {
			$this->assertEquals($value, $array[$key]->buildCommand());
		}
		$this->assertEquals(9, count($array));
	}

}
