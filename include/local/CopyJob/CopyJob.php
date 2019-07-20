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
		if($this->argv->hasValue("max") && $this->argv->getValue("max")!=0) {
			$diff = array_slice($diff, 0, $this->argv->getValue("max"));
		}
	return $diff;
	}
	
	private function copy(string $sourceBasename, bool $link) {
		$sourcePath = $this->source->getLocation()."/".$sourceBasename."/";
		$targetTemp = $this->target->getLocation()."/temp/";
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
		BackupJob::exec($rsync);
		#exec($rsync);
		$paramMV[] = escapeshellarg($targetTemp);
		$paramMV[] = escapeshellarg($targetFinal);
		$mv = "mv ".implode(" ", $paramMV);
		echo $mv.PHP_EOL;
		BackupJob::exec($mv);
		
		#exec($mv);
	}
			
	function run() {
		$diff = $this->getDiff();
		if($this->argv->getBoolean("dry")) {
			foreach($diff as $value) {
				echo $value.PHP_EOL;
			}
		return;
		}
		//getDiff will return a limited amount of entries if --max is set.
		//However, it will be refreshed; therefore, a total countdown of all
		//folders copied must be used as well.
		$max = NULL;
		if($this->argv->getValue("max")!=0) {
			$max = (int)$this->argv->getValue("max");
		}
		while(!empty($diff)) {
			if($max!==NULL && $max===0) {
				break;
			}
			if($this->target->getCollection()->getCount()==0) {
				echo "Erste Kopie ".$diff[0].PHP_EOL;
				$this->copy($diff[0], false);
			} else {
				$this->copy($diff[0], true);
				echo "Weitere Kopie ".$diff[0]." → ".$this->target->getLatest()->getBasename().PHP_EOL;
			}
			if($max!==NULL) {
				$max--;
			}
			$this->target->refresh();
			$diff = $this->getDiff();
		}
	}
}
