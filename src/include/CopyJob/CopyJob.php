<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license GPLv3
 */
class CopyJob {
	private $source;
	private $target;
	#private $argv;
	private $filter;
	private $total = 0;
	private $progress = 1;
	private $max = NULL;
	private $run = TRUE;
	private $showProgress = FALSE;
	private $silent = FALSE;
	function __construct(string $source, string $target) {
		$this->source = new Backup($source);
		$this->target = new Backup($target);
		
		$this->filter = new EntryFilter();
		#if($this->argv->hasValue("from")) {
		#	$this->filter->setFrom(JulianDate::fromString($this->argv->getValue("from")));
		#}
		#if($this->argv->hasValue("to")) {
		#	$this->filter->setTo(JulianDate::fromString($this->argv->getValue("to")));
		#}
		#if($this->argv->getBoolean("daily")) {
		#	$this->filter->addPeriod(BackupEntry::DAILY);
		#}
		#if($this->argv->getBoolean("weekly")) {
		#	$this->filter->addPeriod(BackupEntry::WEEKLY);
		#}
		#if($this->argv->getBoolean("monthly")) {
		#	$this->filter->addPeriod(BackupEntry::MONTHLY);
		#}
		#if($this->argv->getBoolean("yearly")) {
		#	$this->filter->addPeriod(BackupEntry::YEARLY);
		#}
	}
	
	static function fromArgv(array $array): CopyJob {
		$model = new ArgvCopy();
		if(count($array)==1) {
			$reference = new ArgvReference($model);
			echo $reference->getReference();
			die();
		}
		$argv = new Argv($array, $model);
		$source = $argv->getPositional(0);
		$target = $argv->getPositional(1);
		
		$copyjob = new CopyJob($source, $target);
		
		if($argv->hasValue("from")) {
			$copyjob->filter->setFrom(JulianDate::fromString($argv->getValue("from")));
		}
		if($argv->hasValue("to")) {
			$copyjob->filter->setTo(JulianDate::fromString($argv->getValue("to")));
		}
		if($argv->getBoolean("daily")) {
			$copyjob->filter->addPeriod(BackupEntry::DAILY);
		}
		if($argv->getBoolean("weekly")) {
			$copyjob->filter->addPeriod(BackupEntry::WEEKLY);
		}
		if($argv->getBoolean("monthly")) {
			$copyjob->filter->addPeriod(BackupEntry::MONTHLY);
		}
		if($argv->getBoolean("yearly")) {
			$copyjob->filter->addPeriod(BackupEntry::YEARLY);
		}
		
		if($argv->hasValue("max")) {
			$copyjob->setMax($argv->getValue("max"));
		}
		
		$copyjob->setRun($argv->getBoolean("run"));
		$copyjob->setProgress($argv->getBoolean("progress"));
		$copyjob->setSilent($argv->getBoolean("silent"));
	return $copyjob;
	}
	
	function setMax(int $max) {
		$this->max = $max;
	}
	
	function setProgress(bool $progress) {
		$this->progress = $progress;
	}
	
	function setRun(bool $run) {
		$this->run = $run;
	}
	
	function setSilent(bool $silent) {
		$this->silent = $silent;
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
		if($this->max!==NULL && $this->max>=0) {
			$diff = array_slice($diff, 0, $this->max);
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
		
		$rsync = new Command("rsync");
		$rsync->addParameter($sourcePath);
		$rsync->addParameter($targetTemp);
		$rsync->addParameter("--delete");
		$rsync->addParameter("-avz");
		if(!$this->silent) {
			$rsync->showCommand();
			$rsync->showOutput();
		}
		
		
		if($link) {
			$linkDest = $this->target->getLocation()."/".$this->target->getLatest()->getBasename()."/";
			$rsync->addParameter("--link-dest", $linkDest);
			$paramRS[] = "--link-dest=".escapeshellarg($linkDest);
		}
		#$rsync = "rsync ".implode(" ", $paramRS);
		#echo $rsync.PHP_EOL;
		if($this->showProgress) {
			$rsync->setPrefix($sourceBasename." (".$this->progress."/".$this->total."): ");
		}
		#if($this->argv->getBoolean("progress")) {
		#	BackupJob::exec($rsync, $sourceBasename." (".$this->progress."/".$this->total.")");
		#} else {
		#	BackupJob::exec($rsync);
		#}
		$rsync->exec();
		
		$mv = new Command("mv");
		$mv->addParameter($targetTemp);
		$mv->addParameter($targetFinal);
		if(!$this->silent) {
			$rsync->showCommand();
			$rsync->showOutput();
		}
		$mv->exec();
		
		$paramMV[] = escapeshellarg($targetTemp);
		$paramMV[] = escapeshellarg($targetFinal);
		#$mv = "mv ".implode(" ", $paramMV);
		#echo $mv.PHP_EOL;
		#BackupJob::exec($mv);
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
		if(!$this->run) {
			echo "Please use --run to copy entries.".PHP_EOL;
			return;
		}
		//getDiff will return a limited amount of entries if --max is set.
		//However, it will be refreshed; therefore, a total countdown of all
		//folders copied must be used as well.
		$max = NULL;
		$this->total = count($diff);
		if($this->max!==NULL) {
			$max = $this->max;
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
