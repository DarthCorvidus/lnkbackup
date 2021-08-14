<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license GPLv3
 */
class TrimJob {
	private $backup;
	private $args;
	private $filter;
	private $now;
	private $paramConst;
	private $keep = array();
	private $subdir;
	private $max;
	private $days;
	private $period = array();
	private $run = true;
	function __construct(string $path) {
		$this->backup = new Backup($path);
		
		$this->filter = new EntryFilter();
		$this->now = new JulianDate();
		
		$this->paramConst["weeks"] = array(BackupEntry::WEEKLY, JulianDate::WEEK);
		$this->paramConst["months"] = array(BackupEntry::MONTHLY, JulianDate::MONTH);
		$this->paramConst["years"] = array(BackupEntry::YEARLY, JulianDate::YEAR);
	}
	
	static function paramConversion() {
		
	}
	
	static function fromArgv(array $argv): TrimJob {
		$model = new ArgvTrim();
		if(count($argv)==1) {
			$reference = new ArgvReference($model);
			echo $reference->getReference();
			die();
		}
		$args = new Argv($argv, $model);

		$trimjob = new TrimJob($args->getPositional(0));
		$trimjob->now = JulianDate::fromString($args->getValue("to"));
		$trimjob->filter->setTo($trimjob->now);		
		
		if($args->hasValue("from")) {
			$trimjob->filter->setFrom(JulianDate::fromString($args->getValue("from")));
		}
		
		if($args->hasValue("subdir")) {
			$trimjob->filter->setSubdir($args->getValue("subdir"));
			$trimjob->setSubdir($args->getValue("subdir"));
		}
		
		if($args->hasValue("max")) {
			$trimjob->setMax((int)$args->getValue("max"));
		}
		
		if($args->hasValue("days")) {
			$trimjob->setPeriod("days", (int)$args->getValue("days"));
		}
		
		foreach($trimjob->paramConst as $key => $value) {
			if($args->hasValue($key)) {
				$trimjob->setPeriod($key, $args->getValue($key));
			}
		}
		
		$trimjob->setRun($args->getBoolean("run"));
		#print_r($trimjob->period);
		#throw new Exception();
	return $trimjob;
	}
	
	function setRun(bool $run) {
		$this->run = $run;
	}
	
	function setSubdir(string $subdir) {
		$this->subdir = $subdir;
	}
	
	function setMax(int $max) {
		$this->max = $max;
	}
	
	function setDays(int $days) {
		$this->days = $days;
	}
	
	function setPeriod(string $period, int $amount) {
		$this->period[$period] = $amount;
	}
	
	private function addDays(BackupEntry $entry, array $delete): array {
		if($this->max!==NULL && count($delete)>=$this->max) {
			return $delete;
		}
		if(!isset($this->period["days"])) {
			return $delete;
		}
		if($entry->getPeriod()!= BackupEntry::DAILY) {
			return $delete;
		}
		$entryDate = $entry->getDate();
		$diff = $this->now->toInt()-$entryDate->toInt();
		if($diff>=$this->period["days"]) {
			$delete[] = $entry->getPath();
		} else {
			$this->keep[] = $entry->getBasename();
		}
	return $delete;
	}

	private function addDelete(BackupEntry $entry, array $delete, string $param): array {
		if($this->max!==NULL && count($delete)>=$this->max) {
			return $delete;
		}
		if(!isset($this->period[$param])) {
			return $delete;
		}
		if($entry->getPeriod()!= $this->paramConst[$param][0]) {
			return $delete;
		}
		$first = JulianDate::fromInt($this->now->toInt());
		$first = $first->getFirstOf($this->paramConst[$param][1]);
		/**
		 * The week begins with Monday, however, the weekly backup is done on
		 * sunday. It seems to be more intuitive to have a weekly backup done
		 * on sunday, after the week has passed.
		 * Which begs the question why monthly/yearly backups aren't done on the
		 * last day of their respective periods?
		 */
		if($param=="weeks") {
			$first = $first->addUnit(-1, JulianDate::DAY);
		}

		$first = $first->addUnit(-$this->period[$param], $this->paramConst[$param][1]);
		if($entry->getDate()->toInt()<=$first->toInt()) {
			$delete[] = $entry->getPath();
		} else {
			$this->keep[] = $entry->getBasename();
		}
	return $delete;
	}
	
	private function delete(string $path) {
		$fullpath = $path."/".$this->subdir;
		echo $fullpath.PHP_EOL;
		if($path=="/" || $path=="") {
			die();
		}
		echo $this->backup->getLocation().PHP_EOL;
		$tempdir = $this->backup->getLocation()."/temp.delete";
		if(file_exists($tempdir)) {
			$trm = "rm ".escapeshellarg($tempdir)." -rf";
			echo $trm.PHP_EOL;
			BackupJob::exec($trm);
		}
		$mv = "mv ".escapeshellarg($fullpath)." ".escapeshellarg($tempdir);
		BackupJob::exec($mv);
		echo $mv.PHP_EOL;
		$rm = "rm ".escapeshellarg($tempdir)." -rf";
		echo $rm.PHP_EOL;
		BackupJob::exec($rm);
	}
	
	function determineDelete(): array {
		$delete = array();
		$entries = $this->backup->getCollection()->getFiltered($this->filter);
		for($i = 0;$i< $entries->getCount();$i++) {
			$entry = $entries->getEntry($i);
			$delete = $this->addDays($entry, $delete);
			$delete = $this->addDelete($entry, $delete, "weeks");
			$delete = $this->addDelete($entry, $delete, "years");
			$delete = $this->addDelete($entry, $delete, "months");
			#$delete = $this->addMonths($entry, $delete);
			#$delete = $this->addYears($entry, $delete);
		}
	return $delete;
	}
	
	function run() {
		$delete = $this->determineDelete();
		echo "Delete:".PHP_EOL;
		foreach($delete as $key => $value) {
			echo "    ".$value.PHP_EOL;
		}
		echo "Keep:".PHP_EOL;
		foreach($this->keep as $key => $value) {
			echo "    ".$value.PHP_EOL;
		}
		if($this->run) {
			echo "Deleting:".PHP_EOL;
			foreach($delete as $key=> $value) {
				$this->delete($value);
			}
		} else {
			echo "Please use --run to delete entries.".PHP_EOL;
			return;
		}
	}
}
