<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license GPLv3
 */
class EntryFilter {
	private $period;
	private $from;
	private $to;
	private $days;
	private $subdir;
	function __construct() {
		;
	}
	
	function setSubdir(string $subdir) {
		$this->subdir = $subdir;
	}
	
	function setPeriod(string $period){ 
		$this->period = $period;
	}
	
	function setFrom(Date $date) {
		$this->from = $date;
	}

	function setTo(Date $date) {
		$this->to = $date;
	}
	
	function match(BackupEntry $entry) {
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
