<?php
/**
 * @copyright (c) 2007, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license LGPL
 */
/**
 * Loader is an autoloader to be used by PHPs autoloading.
 */
class Loader {
	private $index;
	private $indexed = FALSE;
	private $repositories = array();
	private $indexFile = NULL;
	private $stat = array();
	private $veto = array();
	private $silent = FALSE;
	/**
	 * @param string a valid path  
	 */
	function __construct($dir) {
		$this->repositories[] = $dir;
		if(!is_dir($dir)) {
			throw new Exception("directory given to ".__CLASS__." does not exist or is not a directory");
		}
	}
	
	function silent() {
		$this->silent = TRUE;
	}
	
	/**
	 * path to a file that will store the index
	 * 
	 * Without an index, Loader will recursively traverse ALL repositories once the first class is requested.
	 * Obviously, this consumes a lot of resources on startup. When using an index, the result of the first
	 * traversal will be stored within this file als a simple <class>:<path> pair.<br />
	 * As soon as a class is not found, Loader will assume that the index has changed and build it anew.
	 * @param string a filename. Be cautious, as Loader will overwrite any existing file.
	 */
	function useIndex($index) {
		$this->indexFile = $index;
		if(!file_exists($this->indexFile)) {
			$this->buildIndex();
			$this->writeIndex();
		} else {
			$this->loadIndex();			
		}
	}
	/**
	 * adds veto files
	 * 
	 * adds pathnames that will be ignored by Loader when traversing repositories. Useful to tell Loader to
	 * ignore directories such as .svn. 
	 */
	function addVeto($veto) {
		$this->veto[] = $veto;
	}
	
	/**
	 * adds another repository
	 * 
	 * Adds another repository to the existing one(s). Note that if a class exists within different repositories,
	 * the one within the last added repository will be used.  
	 */
	function addRepository($dir) {
		if(!is_dir($dir)) {
			throw new Exception("directory given to ".__CLASS__." does not exist or is not a directory");
		}
	$this->repositories[] = $dir;
	}
	
	private function loadIndex() {
		$this->index = NULL;
		if(!$fh = fopen($this->indexFile, "r")) {
			throw new Exception("could not open index file for read access");
		}
		while($line = fgets($fh, 1024)) {
			$array = explode(":", $line, 2);
			$this->index[$array[0]] = trim($array[1]);
		}
		fclose($fh);
	}
	
	private function writeIndex() {
		if($this->indexFile==NULL) {
			return;
		}
		if(!$fh = fopen($this->indexFile, "w+")) {
			throw new Exception("could not open index file for write access");
		}
		foreach($this->index as $key => $value) {
			fwrite($fh, $key.":".$value."\n");	
		}
		
		fclose($fh);
	}
	
	private function recurse($path) {
		$dh = dir($path);
		while($file = $dh->read()) {
			$filepath = $dh->path."/".$file;
			if(is_dir($dh->path."/".$file) and !$this->isVetoed($file)) {
				$this->recurse($path."/".$file);
			}
			if(!is_dir($dh->path."/".$file)) {
				$array = explode(".", $file);
				$count = count($array);
				if($array[$count-1]=="php" and isset($array[$count-2])) {
					unset($array[$count-1]);
					#print_r($array);
					if(in_array($array[$count-2], array("interface", "class", "abstract"))) {
						unset($array[$count-2]);
					}
					$this->index[implode(".", $array)] = $filepath;
				}
			
			}
			
		}
		$dh->close();
	}

	private function isVetoed($file) {
		if($file=="." or $file=="..") {
			return true;
		}
		if(empty($this->veto)) {
			return false;
		}
		if(in_array($file, $this->veto)) {
			return true;
		}
	return false;	
	}
	
	/**
	 * Load class
	 * 
	 * Load will try to load a class and throw an error if class does not exist.
	 * @param string name of the class 
	 */
	function load($class) {
		if(!$this->request($class) && $this->silent===FALSE) {
			throw new Exception("class ".$class." is nowhere to be found");
		}
	}
	
	/**
	 * Request class
	 * 
	 * Request will try to load a class, returning true if it exists and false if it does not,
	 * so that another method can be tried
	 * @param string name of the class
	 * @return boolean
	 */
	function request($class) {
		// build index if no index file is used
		if($this->indexFile==NULL) {
			$this->buildIndex();
		}
		// if a class is not found, he'll try to rebuild the index
		if(!isset($this->index[$class]) and $this->indexFile!=NULL) {
			$this->buildIndex();
			// write only if rebuilding resulted in finding the class, to save system resources
			if(isset($this->index[$class])) {
				$this->writeIndex();	
			}
			
		}
	
		if(!isset($this->index[$class])) {
			return false;			
		} else {
			require_once($this->index[$class]);
			return true;
		}
	}
	/**
	 * this function will build the index per manum
	 */
	private function buildIndex() {
		if($this->indexed == TRUE) {
			return;
		}
		$this->stat["time"] = microtime(TRUE);
		foreach($this->repositories as $key => $value) {
			$this->recurse($value);
		}
		$now = microtime(TRUE);
		$this->stat["time"] = $now-$this->stat["time"];
	$this->indexed = TRUE;
	}
	
	function getStat() {
		return $this->stat;
	}
	/**
	 * registers loader as autoload callback with spl_autoload_register
	 */
	function SplRegister() {
		spl_autoload_register(array($this, "load"));
	}
	
}
?>