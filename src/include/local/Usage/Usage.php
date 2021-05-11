<?php
class Usage {
	private $backup;
	private $argv;
	private $filter;
	private $subdir;
	function __construct(array $argv) {
		$model = new ArgvUsage();
		if(count($argv)==1) {
			$reference = new ArgvReference($model);
			echo $reference->getReference();
			die();
		}
		$this->argv = new Argv($argv, $model);
		$this->backup = new Backup($this->argv->getPositional(0));
		$this->filter = new EntryFilter();
		if($this->argv->hasValue("subdir")) {
			$this->filter->setSubdir($this->argv->getValue("subdir"));
			$this->subdir = $this->argv->getValue("subdir");
		}
		if($this->argv->hasValue("from")) {
			$this->filter->setTo(JulianDate::fromString($this->argv->getValue("from")));
		}
		if($this->argv->hasValue("to")) {
			$this->filter->setTo(JulianDate::fromString($this->argv->getValue("to")));
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
	
	function run() {
		$entries = $this->backup->getCollection()->getFiltered($this->filter);
		$du = array();
		for($i=0;$i<$entries->getCount();$i++) {
			$entry = $entries->getEntry($i);
			$du[] = escapeshellarg($entry->getPath()."/".$this->subdir);
		}
		BackupJob::exec("du ".implode(" ", $du)." -shc");
	}
}