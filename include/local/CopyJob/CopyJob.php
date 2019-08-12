<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license GPLv3
 */
class CopyJob {
	private $source;
	private $target;
	private $argv;
	private $filter;
	private $total = 0;
	private $progress = 1;
	function __construct(array $array) {
		if(!isset($array[1])) {
			throw new Exception("first parameter (source) is missing");
		}
		if(!isset($array[2])) {
			throw new Exception("second parameter (target) is missing");
		}
		$this->source = new Backup($array[1]);
		$this->target = new Backup($array[2]);
		$model = new ArgvCopy();
		$this->argv = new Argv($array, $model);
		$this->filter = new EntryFilter();
		if($this->argv->hasValue("from")) {
			$this->filter->setFrom(Date::fromIsodate($this->argv->getValue("from")));
		}
		if($this->argv->hasValue("to")) {
			$this->filter->setTo(Date::fromIsodate($this->argv->getValue("to")));
		}
		if($this->argv->getBoolean("daily")) {
			$this->filter->addPeriod(BackupEntry::DAILY);
		}
		if($this->argv->getBoolean("weekly")) {
			$this->filter->addPeriod(BackupEntry::WEEKLY);
		}
		if($this->argv->getBoolean("monthly")) {
			$this->filter->addPeriod(BackupEntry::MONTHLY);
		}
		if($this->argv->getBoolean("yearly")) {
			$this->filter->addPeriod(BackupEntry::YEARLY);
		}
	}
	
	function getDiff(): array {
		$source = $this->source->getCollection()->getFiltered($this->filter);
		$target = $this->target->getCollection();
		$diff = array();
		for($i = 0; $i<$source->getCount();$i++) {
			$sourceBasename = $source->getEntry($i)->getBasename();
			//echo $source->getEntry($i)->getBasename().PHP_EOL;
			if(!$target->hasBasename($sourceBasename)) {
				$diff[] = $sourceBasename;
			}
		}
		if($this->argv->hasValue("max") && $this->argv->getValue("max")>=0) {
			$diff = array_slice($diff, 0, $this->argv->getValue("max"));
		}
	return $diff;
	}
	
	private function copy(string $sourceBasename, bool $link) {
		$sourcePath = $this->source->getLocation()."/".$sourceBasename."/";
		$targetTemp = $this->target->getLocation()."/temp.copy/";
		$targetFinal = $this->target->getLocation()."/".$sourceBasename."/";
		$paramRS[] = escapeshellarg($sourcePath);
		$paramRS[] = escapeshellarg($targetTemp);
		$paramRS[] = "--delete";
		$paramRS[] = "-avz";
		if($link) {
			$linkDest = $this->target->getLocation()."/".$this->target->getLatest()->getBasename()."/";
			$paramRS[] = "--link-dest=".escapeshellarg($linkDest);
		}
		$rsync = "rsync ".implode(" ", $paramRS);
		echo $rsync.PHP_EOL;
		if($this->argv->getBoolean("progress")) {
			BackupJob::exec($rsync, $sourceBasename." (".$this->progress."/".$this->total.")");
		} else {
			BackupJob::exec($rsync);
		}
		
		exec($rsync);
		$paramMV[] = escapeshellarg($targetTemp);
		$paramMV[] = escapeshellarg($targetFinal);
		$mv = "mv ".implode(" ", $paramMV);
		echo $mv.PHP_EOL;
		BackupJob::exec($mv);
		$this->progress++;
	}
			
	function run() {
		$diff = $this->getDiff();
		if(empty($diff)) {
			echo "No entries to be copied.".PHP_EOL;
			return;
		}
		echo "Entries to be copied:".PHP_EOL;
		foreach($diff as $value) {
			echo "\t".$value.PHP_EOL;
		}
		if(!$this->argv->getBoolean("run")) {
			echo "Please use --run to copy entries.".PHP_EOL;
			return;
		}
		//getDiff will return a limited amount of entries if --max is set.
		//However, it will be refreshed; therefore, a total countdown of all
		//folders copied must be used as well.
		$max = NULL;
		$this->total = count($diff);
		if($this->argv->getValue("max")!=-1) {
			$max = (int)$this->argv->getValue("max");
			$this->total = $max;
		}
		while(!empty($diff)) {
			if($max!==NULL && $max===0) {
				break;
			}
			if($this->target->getCollection()->getCount()==0) {
				echo "First copy ".$diff[0].PHP_EOL;
				$this->copy($diff[0], false);
			} else {
				$this->copy($diff[0], true);
				echo "Subsequent copy ".$diff[0]." → ".$this->target->getLatest()->getBasename().PHP_EOL;
			}
			if($max!==NULL) {
				$max--;
			}
			$this->target->refresh();
			$diff = $this->getDiff();
		}
	}
}
