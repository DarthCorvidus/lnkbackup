<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license GPLv3
 */
class EntryFilter {
	private $period;
	private $periods = array();
	private $from;
	private $to;
	private $subdir;
	/**
	 * Set Subdir
	 * 
	 * BackupEntry has to have a certain subdirectory to match
	 * @param string $subdir
	 */
	function setSubdir(string $subdir) {
		$this->subdir = $subdir;
	}
	
	/**
	 * Add Period
	 * 
	 * BackupEntry has to have one or more periods to match.
	 * @param string $period daily, weekly, monthly, yearly
	 */
	function addPeriod(string $period) {
		$this->periods[] = $period;
	}
	
	/**
	 * Set Period
	 * 
	 * BackupEntry has to have specific period to match
	 * @param string $period daily, weekly, monthly, yearly
	 */
	function setPeriod(string $period){ 
		$this->period = $period;
	}
	
	/**
	 * Set From
	 * 
	 * BackupEntry has to have the same or a higher date to match.
	 * 
	 * @param Date $date
	 */
	function setFrom(Date $date) {
		$this->from = $date;
	}

	/**
	 * Set To
	 * 
	 * BackupEntry has to have the same or a lower date to match
	 * @param Date $date
	 */
	function setTo(Date $date) {
		$this->to = $date;
	}
	
	/**
	 * Match
	 * 
	 * Check if a BackupEntry matches.
	 * @param BackupEntry $entry
	 * @return boolean
	 */
	function match(BackupEntry $entry): bool {
		if(!empty($this->periods) && !in_array($entry->getPeriod(), $this->periods)) {
			return false;
		}
		if($this->period!=NULL && $this->period!=$entry->getPeriod()) {
			return false;
		}
		if($this->from!=NULL && $entry->getDate()->getNumeric()<$this->from->getNumeric()) {
			return false;
		}
		
		if($this->to!=NULL && $entry->getDate()->getNumeric()>$this->to->getNumeric()) {
			return false;
		}
		
		if($this->subdir!=NULL && !$entry->hasSubdir($this->subdir)) {
			return false;
		}
	return true;
	}
}
