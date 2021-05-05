<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license GPLv3
 */
class Backup {
	private $daily = array();
	private $all;
	private $location;
	function __construct(string $location) {
		$this->location = $location;
		$this->all = new EntryCollection();
		$this->refresh();
	}
	
	function getLocation(): string {
		return $this->location;
	}
	
	function refresh() {
		$this->all = new EntryCollection();
		$this->daily = array();
		foreach(glob($this->location."/*") as $key => $value) {
			if(!is_dir($value)) {
				continue;
			}
			try {
				$current = new BackupEntry($value);
			} catch (Exception $e) {
				echo "Skipping over '".basename($value)."': ".$e->getMessage().PHP_EOL;
				continue;
			}
			$this->all->addEntry($current);
			if($current->isDaily()) {
				$this->daily[] = $current;
			}
		}
	}
	
	function getCollection():EntryCollection {
		return $this->all;
	}
	
	public function getFirst(): BackupEntry {
		return $this->all->getEntry(0);
	}
	
	public function getLatest(): BackupEntry {
		return $this->all->getEntry($this->all->getCount()-1);
	}

	public function getLatestPrevious(): BackupEntry {
		return $this->all->getEntry($this->all->getCount()-2);
	}
	
	public function hasEntries():bool {
		return $this->all->getCount()!==0;
	}
	
	public function getDailyCount():int {
		return count($this->daily);
	}
	
	public function isEmpty():bool {
		return !$this->hasEntries();
	}
}
