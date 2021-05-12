<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license GPLv3
 */
class EntryCollection {
	private $entries = array();
	private $basenames = array();
	
	/**
	 * from Array
	 * 
	 * Constructs an instance of EntryCollection directly from an array
	 * containing BackupEntry as values.
	 * @param array $array
	 * @return EntryCollection
	 */
	static function fromArray(array $array): EntryCollection {
		$ec = new EntryCollection();
		foreach($array as $value) {
			$ec->addEntry($value);
		}
	return $ec;
	}
	
	/**
	 * Add Entry
	 * 
	 * Add an instance of BackupEntry to the collection.
	 * @param BackupEntry $entry
	 */
	function addEntry(BackupEntry $entry) {
		$this->entries[] = $entry;
		$this->basenames[] = $entry->getBasename();
	}
	
	/**
	 * Has Basename
	 * 
	 * Checks if a certain basename exists within the collection.
	 * @param string $basename
	 * @return bool
	 */
	function hasBasename(string $basename): bool {
		return in_array($basename, $this->basenames);
	}
	
	/**
	 * Get Count
	 * 
	 * Get the count of BackupEntry's instances.
	 * @return int
	 */
	function getCount(): int {
		return count($this->entries);
	}
	
	/**
	 * Get Entry
	 * 
	 * Get a certain instance of BackupEntry from the collection. This should
	 * not ever fail due to user input, but because of programming errors alone.
	 * @param int $i
	 * @return BackupEntry
	 * @throws OutOfRangeException
	 */
	function getEntry(int $i): BackupEntry {
		if(!isset($this->entries[$i])) {
			throw new OutOfRangeException("index ".$i." out of range.");
		}
		return $this->entries[$i];
	}
	
	/**
	 * Get Filtered
	 * 
	 * Returns a new instance of EntryCollection containing all instances of
	 * BackupEntry which match against an instance of EntryFilter.
	 * @param EntryFilter $filter
	 * @return \EntryCollection
	 */
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
