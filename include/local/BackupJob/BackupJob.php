<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license GPLv3
 */
class BackupJob {
	private $date;
	private $config;
	private $backup;
	function __construct(JobConfig $config, Argv $arg) {
		$this->config = $config;
		$this->date = Date::fromIsodate($arg->getValue("force-date"));
		$this->backup = new Backup($this->config->getTarget());
	}
	
	static function exec(string $command) {
		$handle = popen($command, "r");
		while($line = fgets($handle)) {
			echo $line;
		}
		fclose($handle);
	}
	
	private function removeCopy($copy) {
		if(trim($copy)=="" || trim($copy)=="/") {
			throw new Exception("parameter \$copy is empty or /, this should never happen!");
		}
		if(file_exists($copy)) {
			$command = "rm ".escapeshellarg($copy)." -rf";
			echo $command.PHP_EOL;
			self::exec($command);
		}
	}
	
	private function copyPeriodic(string $suffix) {
		$temp = $this->config->getTarget()."/temp";
		$final = $this->config->getTarget()."/".$this->date->getDate("Y-m-d");
		$this->removeCopy($temp.".".$suffix);
		$this->removeCopy($final.".".$suffix);
		$command = "cp ".escapeshellarg($final)." ".escapeshellarg($temp.".".$suffix)." -al";
		echo $command.PHP_EOL;
		self::exec($command);

		$command = "mv ".escapeshellarg($temp.".".$suffix)." ".escapeshellarg($final.".".$suffix);
		echo $command.PHP_EOL;
		self::exec($command);
	}
	
	private function copyWMY(string $final) {
		if($this->date->getDate("N")===7) {
			$this->copyPeriodic("weekly");
		}
		if($this->date->getDay()==1) {
			$this->copyPeriodic("monthly");
		}
		if($this->date->getDate("m-d")=="01-01") {
			$this->copyPeriodic("yearly");
		}
	}
	
	function executeEmpty() {
		$temp = $this->config->getTarget()."/temp.create";
		$final = $this->config->getTarget()."/".$this->date->getDate("Y-m-d");
		$source = $this->config->getSource();
		$exclude = NULL;
		if($this->config->hasExclude()) {
			$exclude = "--exclude-from=".escapeshellarg($this->config->getExclude());
		}

		if(!file_exists($final)) {
			$command = "rsync ".escapeshellarg($source)." ".escapeshellarg($temp)." ".$exclude." -avz --delete";
			echo $command.PHP_EOL;
			BackupJob::exec($command);
			$command = "mv ".$temp." ".$final;
			echo $command.PHP_EOL;
			BackupJob::exec($command);
			$this->copyWMY($final);
		} else {
			$command = "rsync ".escapeshellarg($source)." ".escapeshellarg($final)." ".$exclude." -avz --delete";
			echo $command.PHP_EOL;
			BackupJob::exec($command);
			$this->copyWMY($final);
		}
		
	}
	
	function execute() {
		if($this->backup->isEmpty()) {
			$this->executeEmpty();
			return;
		}

		if($this->backup->getDailyCount()==1 && $this->backup->getLatest()->getDate()->getNumeric()==$this->date->getNumeric()) {
			$this->executeEmpty();
			return;
		}
		
		$latest = $this->backup->getLatest()->getPath();
		$temp = $this->config->getTarget()."/temp.create";
		$final = $this->config->getTarget()."/".$this->date->getDate("Y-m-d");
		$source = $this->config->getSource();
		$exclude = NULL;
		if($this->config->hasExclude()) {
			$exclude = "--exclude-from=".escapeshellarg($this->config->getExclude());
		}
		if(!file_exists($final)) {
			$command = "rsync ".escapeshellarg($source)." ".escapeshellarg($temp)." --link-dest=".escapeshellarg($latest)." ".$exclude." -avz --delete";
			echo $command.PHP_EOL;
			BackupJob::exec($command);
			$command = "mv ".$temp." ".$final;
			echo $command.PHP_EOL;
			BackupJob::exec($command);
			$this->copyWMY($final);
		} else {
			$latestPrevious = $this->backup->getLatestPrevious()->getPath();
			$command = "rsync ".escapeshellarg($source)." ".escapeshellarg($final)." --link-dest=".escapeshellarg($latestPrevious)." ".$exclude." -avz --delete";
			echo $command.PHP_EOL;
			BackupJob::exec($command);
			$this->copyWMY($final);
		}
	}
}
