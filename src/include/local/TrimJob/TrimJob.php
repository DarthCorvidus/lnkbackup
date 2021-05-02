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
	function __construct(array $argv) {
		$model = new ArgvTrim();
		if(count($argv)==1) {
			$reference = new ArgvReference($model);
			echo $reference->getReference();
			die();
		}
		$this->args = new Argv($argv, $model);
		$this->backup = new Backup($argv[1]);
		$this->filter = new EntryFilter();
		$this->now = Date::fromIsodate($this->args->getValue("to"));
		$this->filter->setTo($this->now);
		
		if($this->args->hasValue("from")) {
			$this->filter->setFrom(Date::fromIsodate($this->args->getValue("from")));
		}
		if($this->args->hasValue("subdir")) {
			$this->filter->setSubdir($this->args->getValue("subdir"));
			$this->subdir = $this->args->getValue("subdir");
		}
		$this->paramConst["weeks"] = array(BackupEntry::WEEKLY, Date::WEEK);
		$this->paramConst["months"] = array(BackupEntry::MONTHLY, Date::MONTH);
		$this->paramConst["years"] = array(BackupEntry::YEARLY, Date::YEAR);
	}
	
	private function addDays(BackupEntry $entry, array $delete): array {
		if($this->args->hasValue("max") && count($delete)>=$this->args->getValue("max")) {
			return $delete;
		}
		if(!$this->args->hasValue("days")) {
			return $delete;
		}
		if($entry->getPeriod()!= BackupEntry::DAILY) {
			return $delete;
		}
		$entryDate = $entry->getDate();
		$diff = $this->now->getNumeric()-$entryDate->getNumeric();
		if($diff>=$this->args->getValue("days")) {
			$delete[] = $entry->getPath();
		} else {
			$this->keep[] = $entry->getBasename();
		}
	return $delete;
	}

	private function addDelete(BackupEntry $entry, array $delete, string $param): array {
		if($this->args->hasValue("max") && count($delete)>=$this->args->getValue("max")) {
			return $delete;
		}
		if(!$this->args->hasValue($param)) {
			return $delete;
		}
		if($entry->getPeriod()!= $this->paramConst[$param][0]) {
			return $delete;
		}
		$first = Date::fromInt($this->now->getNumeric());
		$first->floor($this->paramConst[$param][1]);
		/**
		 * The week begins with Monday, however, the weekly backup is done on
		 * sunday. It seems to be more intuitive to have a weekly backup done
		 * on sunday, after the week has passed.
		 * Which begs the question why monthly/yearly backups aren't done on the
		 * last day of their respective periods?
		 */
		if($param=="weeks") {
			$first->subtractUnit(1, Date::DAY);
		}
		$first->subtractUnit($this->args->getValue($param), $this->paramConst[$param][1]);
		if($entry->getDate()->getNumeric()<=$first->getNumeric()) {
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
	
	function run() {
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
		echo "Delete:".PHP_EOL;
		foreach($delete as $key => $value) {
			echo "    ".$value.PHP_EOL;
		}
		echo "Keep:".PHP_EOL;
		foreach($this->keep as $key => $value) {
			echo "    ".$value.PHP_EOL;
		}
		if($this->args->getBoolean("run")) {
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