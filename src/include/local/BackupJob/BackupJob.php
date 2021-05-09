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
	private $silent;
	function __construct(JobConfig $config, Argv $arg) {
		$this->config = $config;
		$this->silent = $arg->getBoolean("silent");
		$this->date = Date::fromIsodate($arg->getValue("force-date"));
		$this->backup = new Backup($this->config->getTarget());
	}
	
	private function silence(Command $command) {
		if(!$this->silent) {
			$command->showCommand();
			$command->showOutput();
		}
	}
	
	private function addExclude(Command $rsyncCommand) {
		if($this->config->hasExclude()) {
			$rsyncCommand->addParameter("--exclude-from", realpath($this->config->getExclude()));
		}
	}
	
	static function exec(string $command, $prefix = NULL) {
		$handle = popen($command, "r");
		if($prefix!=NULL) {
			$prefix .= ": ";
		}
		while($line = fgets($handle)) {
			echo $prefix.$line;
		}
		fclose($handle);
	}
	
	function removeCopy(string $copy) {
		if(trim($copy)=="" || trim($copy)=="/") {
			throw new Exception("parameter \$copy is empty or /, this should never happen!");
		}
		
		if(file_exists($copy)) {
			$rm = new Command("rm");
			$this->silence($rm);
			$rm->addParameter($copy);
			$rm->addParameter("-rf");
			$rm->exec();
		}
	}
	
	private function copyPeriodic(string $suffix) {
		$source = $this->config->getTarget()."/".$this->date->getDate("Y-m-d");
		$temp = $this->config->getTarget()."/temp.".$suffix;
		$final = $this->config->getTarget()."/".$this->date->getDate("Y-m-d").".".$suffix;
		
		$this->removeCopy($temp);
		$this->removeCopy($final);

		$cp = new Command("cp");
		$this->silence($cp);
		$cp->addParameter($source);
		$cp->addParameter($temp);
		$cp->addParameter("-al");
		$cp->exec();
		
		$mv = new Command("mv");
		$this->silence($mv);
		$mv->addParameter($temp);
		$mv->addParameter($final);
		$mv->exec();
	}
	
	private function copyWMY(string $final) {
		if($this->date->getDate("N")==7) {
			$this->copyPeriodic("weekly");
		}
		if($this->date->getDate("d")==="01") {
			$this->copyPeriodic("monthly");
		}
		if($this->date->getDate("m-d")==="01-01") {
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
			$rsync = new Command("rsync");
			$this->silence($rsync);

			$rsync->addParameter($source);
			$rsync->addParameter($temp);
			$this->addExclude($rsync);
			$rsync->addParameter("-avz");
			$rsync->addParameter("--delete");
			$rsync->exec();
			
			#$command = "rsync ".escapeshellarg($source)." ".escapeshellarg($temp)." ".$exclude." -avz --delete";
			#echo $command.PHP_EOL;
			#BackupJob::exec($command);
	
			$mv = new Command("mv");
			$this->silence($mv);
			$mv->addParameter($temp);
			$mv->addParameter($final);
			$mv->exec();

			#$command = "mv ".$temp." ".$final;
			#echo $command.PHP_EOL;
			$this->copyWMY($final);
		} else {
			$rsync = new Command("rsync");
			$this->silence($rsync);
			$rsync->addParameter($source);
			$rsync->addParameter($final);
			$this->addExclude($rsync);
			$rsync->addParameter("-avz");
			$rsync->addParameter("--delete");
			$rsync->exec();
			
			#$command = "rsync ".escapeshellarg($source)." ".escapeshellarg($final)." ".$exclude." -avz --delete";
			#echo $command.PHP_EOL;
			#BackupJob::exec($command);
			$this->copyWMY($final);
		}
		
	}
	
	function getEmptyCommands(): array {
		$commands = array();
		$temp = $this->config->getTarget()."/temp.create";
		$final = $this->config->getTarget()."/".$this->date->getDate("Y-m-d");
		$source = $this->config->getSource();
		$exclude = NULL;
		if(!file_exists($final)) {
			$rsync = new Command("rsync");
			$this->silence($rsync);

			$rsync->addParameter($source);
			$rsync->addParameter($temp);
			$rsync->addParameter("-avz");
			$this->addExclude($rsync);
			$rsync->addParameter("--delete");
			$commands[] = $rsync;
			
			#$command = "rsync ".escapeshellarg($source)." ".escapeshellarg($temp)." ".$exclude." -avz --delete";
			#echo $command.PHP_EOL;
			#BackupJob::exec($command);
	
			$mv = new Command("mv");
			$this->silence($mv);
			$mv->addParameter($temp);
			$mv->addParameter($final);
			$commands[] = $mv;
		} else {
			$rsync = new Command("rsync");
			$this->silence($rsync);
			$rsync->addParameter($source);
			$rsync->addParameter($final);
			$rsync->addParameter("-avz");
			$this->addExclude($rsync);
			$rsync->addParameter("--delete");
			$commands[] = $rsync;
		}
	return $commands;
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
		
		$latest = realpath($this->backup->getLatest()->getPath());
		$temp = $this->config->getTarget()."/temp.create";
		$final = $this->config->getTarget()."/".$this->date->getDate("Y-m-d");
		$source = $this->config->getSource();
		$exclude = NULL;
		if($this->config->hasExclude()) {
			$exclude = "--exclude-from=".escapeshellarg($this->config->getExclude());
		}
		if(!file_exists($final)) {
			$rsync = new Command("rsync");
			$this->silence($rsync);
			$rsync->addParameter($source);
			$rsync->addParameter($temp);
			$rsync->addParameter("--link-dest", $latest);
			$this->addExclude($rsync);
			$rsync->addParameter("-avz");
			$rsync->addParameter("--delete");
			#$command = "rsync ".escapeshellarg($source)." ".escapeshellarg($temp)." --link-dest=".escapeshellarg($latest)." ".$exclude." -avz --delete";
			#echo $command.PHP_EOL;
			#BackupJob::exec($command);
			$rsync->exec();
			
			$mv = new Command("mv");
			$this->silence($mv);
			$mv->addParameter($temp);
			$mv->addParameter($final);
			$mv->exec();
			#$command = "mv ".$temp." ".$final;
			#echo $command.PHP_EOL;
			#BackupJob::exec($command);
			$this->copyWMY($final);
		} else {
			$latestPrevious = realpath($this->backup->getLatestPrevious()->getPath());
			$rsync = new Command("rsync");
			$this->silence($rsync);
			$rsync->addParameter($source);
			$rsync->addParameter($final);
			$rsync->addParameter("--link-dest", $latestPrevious);
			$this->addExclude($rsync);
			$rsync->addParameter("-avz");
			$rsync->addParameter("--delete");
			$rsync->exec();
			#$command = "rsync ".escapeshellarg($source)." ".escapeshellarg($final)." --link-dest=".escapeshellarg($latestPrevious)." ".$exclude." -avz --delete";
			#echo $command.PHP_EOL;
			#BackupJob::exec($command);
			$this->copyWMY($final);
		}
	}
}
