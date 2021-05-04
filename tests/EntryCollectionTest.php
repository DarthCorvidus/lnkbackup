<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
class EntryCollectionTest extends TestCase {
	private function getEntries(): array {
		$array[] = new BackupEntry(__DIR__."/tests/target/2010-01-01");
		$array[] = new BackupEntry(__DIR__."/tests/target/2010-01-01.monthly");
		$array[] = new BackupEntry(__DIR__."/tests/target/2010-01-01.yearly");
		$array[] = new BackupEntry(__DIR__."/tests/target/2010-01-02");
		$array[] = new BackupEntry(__DIR__."/tests/target/2010-01-03");
		$array[] = new BackupEntry(__DIR__."/tests/target/2010-01-03.weekly");
	return $array;
	}

	function testConstruct() {
		$ec = new EntryCollection();
		$this->assertInstanceOf(EntryCollection::class, $ec);
	}
	
	function testFromArray() {
		$ec = EntryCollection::fromArray($this->getEntries());
		$this->assertInstanceOf(EntryCollection::class, $ec);
	}
	
	function testGetCount() {
		$ec = new EntryCollection();
		$this->assertEquals(0, $ec->getCount());
		foreach($this->getEntries() as $value) {
			$ec->addEntry($value);
		}
		$this->assertEquals(6, $ec->getCount());
	}
	
	function testHasBasename() {
		$ec = EntryCollection::fromArray($this->getEntries());
		$this->assertEquals(TRUE, $ec->hasBasename("2010-01-01.monthly"));
	}
	
	function testBasenameMissing() {
		$ec = EntryCollection::fromArray($this->getEntries());
		$this->assertEquals(FALSE, $ec->hasBasename("2010-02-01.monthly"));
	}
	
	function testGetEntry() {
		$array = $this->getEntries();
		$ec = EntryCollection::fromArray($array);
		$this->assertEquals($array[0], $ec->getEntry(0));
	}
	
	function testGetEntryMissing() {
		$ec = EntryCollection::fromArray($this->getEntries());
		$this->expectException(OutOfRangeException::class);
		$ec->getEntry(7);
	}
	
	function testGetFiltered() {
		$filter = new EntryFilter();
		$filter->addPeriod("yearly");
		$filter->addPeriod("monthly");
		$ec = EntryCollection::fromArray($this->getEntries());
		$filtered = $ec->getFiltered($filter);
		$this->assertEquals(2, $filtered->getCount());
		$this->assertEquals(FALSE, $filtered->hasBasename("2010-01-01"));
		$this->assertEquals(TRUE, $filtered->hasBasename("2010-01-01.monthly"));
		$this->assertEquals(TRUE, $filtered->hasBasename("2010-01-01.yearly"));
	}

}
