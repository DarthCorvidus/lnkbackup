<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license GPLv3
 */
class Backup {
	private $total = array();
	private $daily = array();
	private $weekly = array();
	private $monthly = array();
	private $yearly = array();
	private $all;
	private $temporaryCreate = false;
	private $temporaryDelete = false;
	private $location;
	function __construct(string $location) {
		$this->location = $location;
		$this->refresh();
	}
	
	function getLocation(): string {
		return $this->location;
	}
	
	function refresh() {
		$this->all = new EntryCollection();
		$this->daily = array();
		$this->total = array();
		$this->weekly = array();
		$this->monthly = array();
		$this->yearly = array();
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
			$this->total[] = $current;
			if($current->isDaily()) {
				$this->daily[] = $current;
			}
			if($current->isWeekly()) {
				$this->weekly[] = $current;
			}
			if($current->isMonthly()) {
				$this->monthly[] = $current;
			}
			if($current->isYearly()) {
				$this->yearly[] = $current;
			}
		}
	}
	
	function getCollection():EntryCollection {
		return $this->all;
	}
	
	function getTotal():array {
		return $this->total;
	}
	
	function getDaily():array {
		return $this->daily;
	}
	
	function getWeekly():array {
		return $this->weekly;
	}
	
	function getMonthly():array {
		return $this->monthly;
	}
	
	function getYearly():array {
		return $this->yearly;
	}
	
	function hasTemporary(): bool {
		
	}

	public function getFirst(): BackupEntry {
		return $this->total[0];
	}
	
	public function getLatest(): BackupEntry {
		return $this->total[count($this->total)-1];
	}

	public function getLatestPrevious(): BackupEntry {
		return $this->total[count($this->total)-2];
	}
	
	public function hasEntries():bool {
		return count($this->total)!==0;
	}
	
	public function getDailyCount():int {
		return count($this->daily);
	}
	
	public function isEmpty():bool {
		return empty($this->total);
	}
}
