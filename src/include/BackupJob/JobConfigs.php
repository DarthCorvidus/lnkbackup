<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license GPLv3
 */
class JobConfigs {
	private $jobs = array();
	function __construct($file) {
		if(!file_exists($file)) {
			throw new Exception("file or folder ".$file." does not exist.");
		}
		if(is_file($file)) {
			$this->jobs[] = JobConfig::fromFile($file);
			return;
		}
		foreach(glob($file."/*.conf") as $value) {
			try {
				$config = JobConfig::fromFile($value);
				$this->jobs[] = $config;
			} catch(Exception $e) {
				echo "Error in ".$value.": ".$e->getMessage().PHP_EOL;
			}
		}
	}
	
	function getCount(): int {
		return count($this->jobs);
	}
	
	function getJob(int $i): JobConfig {
		return $this->jobs[$i];
	}
}