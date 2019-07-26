<?php
class Usage {
	private $backup;
	private $argv;
	private $filter;
	private $subdir;
	function __construct(array $argv) {
		$this->backup = new Backup($argv[1]);
		$model = new ArgvUsage();
		$this->argv = new Argv($argv, $model);
		$this->filter = new EntryFilter();
		if($this->argv->hasValue("subdir")) {
			$this->filter->setSubdir($this->argv->getValue("subdir"));
			$this->subdir = $this->argv->getValue("subdir");
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