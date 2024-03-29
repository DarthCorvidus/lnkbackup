<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license GPLv3
 */
class RebuildJob {
	private $backup;
	private $rebuild = array();
	private $i = 0;
	private $max;
	private $run = true;
	private $silent = false;
	function __construct(string $path) {
		$this->backup = new Backup($path);
	}

	static function fromArgv(array $array): RebuildJob {
		$model = new ArgvRebuild();
		if(count($array)<=1) {
			$reference = new ArgvReference($model);
			echo $reference->getReference();
			die();
		}
		$argv = new Argv($array, $model);
		$rebuild = new RebuildJob($argv->getPositional(0));

		if($argv->getBoolean("weekly")) {
			$rebuild->addRebuild(BackupEntry::WEEKLY);
		}
		if($argv->getBoolean("monthly")) {
			$rebuild->addRebuild(BackupEntry::MONTHLY);
		}
		if($argv->getBoolean("yearly")) {
			$rebuild->addRebuild(BackupEntry::YEARLY);
		}
		
		if($argv->hasValue("max")) {
			$rebuild->setMax((int)$argv->getValue("max"));
		}
		
		$rebuild->setRun($argv->getBoolean("run"));
		$rebuild->setSilent($argv->getBoolean("silent"));
	return $rebuild;
	}
	
	public function addRebuild(string $rebuild) {
		$this->rebuild[] = $rebuild;
	}
	
	public function setMax(int $max) {
		$this->max = $max;
	}
	
	public function setRun(bool $run) {
		$this->run = $run;
	}
	
	public function setSilent(bool $silent) {
		$this->silent = $silent;
	}
	
	private function createCopy(string $source, string $location, string $finalBase) {
		if(!$this->run) {
			echo basename($source)." to ".$finalBase.PHP_EOL;
		return;
		}
		
		$commands = array();
		
		$temp = $location."/temp.rebuild";
		$final = $location."/".$finalBase;
		if(file_exists($temp)) {
			$rm = new Command("rm");
			$rm->addParameter($temp);
			$rm->addParameter("-rf");
			$commands[] = $rm;
			
			#$rm = "rm ".escapeshellarg($temp)." -rf";
			#echo $rm.PHP_EOL;
			#BackupJob::exec($rm);
		}
		$cp = new Command("cp");
		$cp->addParameter($source);
		$cp->addParameter($temp);
		$cp->addParameter("-al");
		$commands[] = $cp;
		
		$mv = new Command("mv");
		$mv->addParameter($temp);
		$mv->addParameter($final);
		$commands[] = $mv;
		
		foreach($commands as $value) {
			if(!$this->silent) {
				$value->showCommand();
				$value->showOutput();
			}
			$value->exec();
		}
		
		#$cp = "cp ". escapeshellarg($source)." ".escapeshellarg($temp)." -al";
		#echo $cp.PHP_EOL;
		#BackupJob::exec($cp);
		#$mv = "mv ". escapeshellarg($temp)." ".escapeshellarg($final);
		#echo $mv.PHP_EOL;
		#BackupJob::exec($mv);
		if(!$this->silent) {
			echo PHP_EOL;
		}
		
	}
	
	private function rebuild(string $period, BackupEntry $entry): bool {
		if($this->max!==NULL && $this->i==$this->max) {
			return false;
		}
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
			/**
			 * Handling of --max is a little bit more difficult here. --max is
			 * supposed to limit the amount of actions done, but one day may
			 * result in up to three rebuilds. If just $i was checked, then at
			 * worst --max=1 could result in three actions on the first of a
			 * year.
			 * Therefore, progress is tracked globally and checked within
			 * rebuild() as well.
			 */
			if($this->max!==NULL && $this->i==$this->max) {
				break;
			}
			$entry = $entries->getEntry($i);
			if($entry->getPeriod()!=BackupEntry::DAILY) {
				continue;
			}
			$basename = $entry->getBasename();
			if($entry->getDate()->getFormat("N")==7 && $this->rebuild(BackupEntry::WEEKLY, $entry)) {
				$this->createCopy($entry->getPath(), $location, $basename.".weekly");
				$this->i++;
			}
			if($entry->getDate()->getFormat("d")=="01" && $this->rebuild(BackupEntry::MONTHLY, $entry)) {
				$this->createCopy($entry->getPath(), $location, $basename.".monthly");
				$this->i++;
			}
			if($entry->getDate()->getFormat("m-d")=="01-01" && $this->rebuild(BackupEntry::YEARLY, $entry)) {
				$this->createCopy($entry->getPath(), $location, $basename.".yearly");
				$this->i++;
			}

			#echo $entry->getPath().PHP_EOL;
		}
		if(!$this->run) {
			echo "Please use --run to rebuild entries.".PHP_EOL;
		}
	}
}