<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license GPLv3
 */
class RebuildJob {
	private $argv;
	private $backup;
	function __construct(array $argv) {
		$model = new ArgvRebuild();
		$this->argv = new Argv($argv, $model);
		$this->backup = new Backup($argv[1]);
	}
	
	private function createCopy(string $source, string $location, string $finalBase) {
		if(!$this->argv->getBoolean("run")) {
			echo basename($source)." to ".$finalBase.PHP_EOL;
		return;
		}
		$temp = $location."/temp.rebuild";
		$final = $location."/".$finalBase;
		echo $source.PHP_EOL;
		echo $temp.PHP_EOL;
		echo $final.PHP_EOL.PHP_EOL;
		if(file_exists($temp)) {
			$rm = "rm ".escapeshellarg($temp)." -rvf";
			echo $rm.PHP_EOL;
			BackupJob::exec();
		}
		$cp = "cp ". escapeshellarg($source)." ".escapeshellarg($temp)." -al";
		echo $cp.PHP_EOL;
		BackupJob::exec($cp);
		$mv = "mv ". escapeshellarg($temp)." ".escapeshellarg($final);
		echo $mv.PHP_EOL;
		BackupJob::exec($mv);
		echo PHP_EOL;
	}
			
	function run() {
		$entries = $this->backup->getCollection();
		$location = $this->backup->getLocation();
		for($i=0;$i<$entries->getCount();$i++) {
			$entry = $entries->getEntry($i);
			if($entry->getPeriod()!=BackupEntry::DAILY) {
				continue;
			}
			$basename = $entry->getBasename();
			if($entry->getDate()->getDate("N")==7 && !file_exists($location."/".$basename.".weekly")) {
				$this->createCopy($entry->getPath(), $location, $basename.".weekly");
			}
			if($entry->getDate()->getDate("m")=="01" && !file_exists($location."/".$basename.".monthly")) {
				$this->createCopy($entry->getPath(), $location, $basename.".monthly");
			}
			if($entry->getDate()->getDate("m-d")=="01-01" && !file_exists($location."/".$basename.".yearly")) {
				$this->createCopy($entry->getPath(), $location, $basename.".yearly");
			}

			#echo $entry->getPath().PHP_EOL;
		}
		if(!$this->argv->getBoolean("run")) {
			echo "Please use --run to rebuild entries.".PHP_EOL;
		}
	}
}