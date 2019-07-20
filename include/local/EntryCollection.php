<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license GPLv3
 */
class EntryCollection {
	private $entries = array();
	private $basenames = array();
	function __construct() {
		$this->basenames[] = array();
	}
	
	function addEntry(BackupEntry $entry) {
		$this->entries[] = $entry;
		$this->basenames[] = $entry->getBasename();
	}
	
	function hasBasename(string $basename): bool {
		return in_array($basename, $this->basenames);
	}
	
	function getCount(): int {
		return count($this->entries);
	}
	
	function getEntry(int $i): BackupEntry {
		return $this->entries[$i];
	}
	
	function getFiltered(EntryFilter $filter): EntryCollection {
		$new = new EntryCollection();
		foreach($this->entries as $value) {
			if(!$filter->match($value)) {
				continue;
			}
			$new->addEntry($value);
		}
	return $new;
	}
}
