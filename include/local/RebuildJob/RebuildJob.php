<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license GPLv3
 */
class RebuildJob {
	private $argv;
	private $backup;
	private $rebuild = array();
	function __construct(array $argv) {
		$model = new ArgvRebuild();
		$this->argv = new Argv($argv, $model);
		$this->backup = new Backup($argv[1]);
		if($this->argv->getBoolean("weekly")) {
			$this->rebuild[] = BackupEntry::WEEKLY;
		}
		if($this->argv->getBoolean("monthly")) {
			$this->rebuild[] = BackupEntry::MONTHLY;
		}
		if($this->argv->getBoolean("yearly")) {
			$this->rebuild[] = BackupEntry::YEARLY;
		}
	}
	
	private function createCopy(string $source, string $location, string $finalBase) {
		if(!$this->argv->getBoolean("run")) {
			echo basename($source)." to ".$finalBase.PHP_EOL;
		return;
		}
		$temp = $location."/temp.rebuild";
		$final = $location."/".$finalBase;
		if(file_exists($temp)) {
			$rm = "rm ".escapeshellarg($temp)." -rvf";
			echo $rm.PHP_EOL;
			BackupJob::exec($rm);
		}
		$cp = "cp ". escapeshellarg($source)." ".escapeshellarg($temp)." -al";
		echo $cp.PHP_EOL;
		BackupJob::exec($cp);
		$mv = "mv ". escapeshellarg($temp)." ".escapeshellarg($final);
		echo $mv.PHP_EOL;
		BackupJob::exec($mv);
		echo PHP_EOL;
	}
	
	private function rebuild(string $period, BackupEntry $entry): bool {
		$target = $this->backup->getLocation()."/".$entry->getBasename().".".$period;
		if(file_exists($target)) {
			return false;
		}
		if(empty($this->rebuild)) {
			return true;
		}
		if(in_array($period, $this->rebuild)) {
			return true;
		}
	return false;
	}
	
	function run() {
		$entries = $this->backup->getCollection();
		$location = $this->backup->getLocation();
		$k = 0;
		for($i=0;$i<$entries->getCount();$i++) {
			if($this->argv->getValue("max")>=0 && $k==$this->argv->getValue("max")) {
				break;
			}
			$entry = $entries->getEntry($i);
			if($entry->getPeriod()!=BackupEntry::DAILY) {
				continue;
			}
			$basename = $entry->getBasename();
			if($entry->getDate()->getDate("N")==7 && $this->rebuild(BackupEntry::WEEKLY, $entry)) {
				$this->createCopy($entry->getPath(), $location, $basename.".weekly");
				$k++;
				continue;
			}
			if($entry->getDate()->getDate("m")=="01" && $this->rebuild(BackupEntry::MONTHLY, $entry)) {
				$this->createCopy($entry->getPath(), $location, $basename.".monthly");
				$k++;
				continue;
			}
			if($entry->getDate()->getDate("m-d")=="01-01" && $this->rebuild(BackupEntry::YEARLY, $entry)) {
				$this->createCopy($entry->getPath(), $location, $basename.".yearly");
				$k++;
				continue;
			}

			#echo $entry->getPath().PHP_EOL;
		}
		if(!$this->argv->getBoolean("run")) {
			echo "Please use --run to rebuild entries.".PHP_EOL;
		}
	}
}