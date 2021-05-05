<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
class BackupTest extends TestCase {
	private function getEntries(): array {
		$array[] = new BackupEntry(__DIR__."/target/2010-01-01");
		$array[] = new BackupEntry(__DIR__."/target/2010-01-01.monthly");
		$array[] = new BackupEntry(__DIR__."/target/2010-01-01.yearly");
		$array[] = new BackupEntry(__DIR__."/target/2010-01-02");
		$array[] = new BackupEntry(__DIR__."/target/2010-01-03");
		$array[] = new BackupEntry(__DIR__."/target/2010-01-03.weekly");
	return $array;
	}

	function testConstruct() {
		$entry = new Backup(__DIR__."/target");
		$this->assertInstanceOf(Backup::class, $entry);
	}

	#function testConstructInvalid() {
	#	$this->expectException(Exception::class);
	#	$entry = new Backup(__DIR__."tests/targett/");
	#}
	
	function testGetLocation() {
		$backup = new Backup(__DIR__."/target");
		$this->assertEquals(__DIR__."/target", $backup->getLocation());
	}

	function testGetCollection() {
		$backup = new Backup(__DIR__."/target");
		$this->assertInstanceOf(EntryCollection::class, $backup->getCollection());
		$this->assertEquals(6, $backup->getCollection()->getCount());
		$array = $this->getEntries();
		foreach($array as $key => $value) {
			$this->assertEquals($array[$key], $backup->getCollection()->getEntry($key));
		}
	}
	
	function testGetFirst() {
		$backup = new Backup(__DIR__."/target");
		$entries = $this->getEntries();
		$this->assertEquals($entries[0], $backup->getFirst());
	}
	
	function testGetLatest() {
		$backup = new Backup(__DIR__."/target");
		$entries = $this->getEntries();
		$this->assertEquals($entries[5], $backup->getLatest());
	}
	
	function testGetLatestPrevious() {
		$backup = new Backup(__DIR__."/target");
		$entries = $this->getEntries();
		$this->assertEquals($entries[4], $backup->getLatestPrevious());
	}
	
	function testHasEntries() {
		$backup = new Backup(__DIR__."/target.empty");
		$this->assertEquals(FALSE, $backup->hasEntries());
		$backup = new Backup(__DIR__."/target");
		$this->assertEquals(TRUE, $backup->hasEntries());
	}

	function testIsEmpty() {
		$backup = new Backup(__DIR__."/target.empty");
		$this->assertEquals(TRUE, $backup->isEmpty());
		$backup = new Backup(__DIR__."/target");
		$this->assertEquals(FALSE, $backup->isEmpty());
	}
	
	function testGetDailyCount() {
		$backup = new Backup(__DIR__."/target");
		$this->assertEquals(3, $backup->getDailyCount());
	}
}
