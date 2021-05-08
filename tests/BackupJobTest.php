<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
class BackupJobTest extends TestCase {
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
			$backup->execute();
		}
		foreach($target as $value) {
			$this->assertFileExists(__DIR__."/target.empty/".$value);
			exec("rm -r ".escapeshellarg(__DIR__."/target.empty/".$value));
		}
	}
}
