<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
class EntryFilterTest extends TestCase {
	function testConstruct() {
		$filter = new EntryFilter();
		$this->assertInstanceOf(EntryFilter::class, $filter);
	}
	
	function testMatchDaily() {
		$daily = new BackupEntry("tests/target/2010-01-01");
		$yearly = new BackupEntry("tests/target/2010-01-01.yearly");
		$filter = new EntryFilter();
		$filter->addPeriod(BackupEntry::DAILY);
		$this->assertEquals(TRUE, $filter->match($daily));
		$this->assertEquals(FALSE, $filter->match($yearly));
	}
	
	function testMatchWeekly() {
		$daily = new BackupEntry("tests/target/2010-01-03");
		$weekly = new BackupEntry("tests/target/2010-01-03.weekly");
		$filter = new EntryFilter();
		$filter->addPeriod(BackupEntry::WEEKLY);
		$this->assertEquals(TRUE, $filter->match($weekly));
		$this->assertEquals(FALSE, $filter->match($daily));
	}
	
	function testMatchMonthly() {
		$daily = new BackupEntry("tests/target/2010-01-01");
		$monthly = new BackupEntry("tests/target/2010-01-01.monthly");
		$filter = new EntryFilter();
		$filter->addPeriod(BackupEntry::MONTHLY);
		$this->assertEquals(TRUE, $filter->match($monthly));
		$this->assertEquals(FALSE, $filter->match($daily));
	}

	function testMatchYearly() {
		$daily = new BackupEntry("tests/target/2010-01-01");
		$yearly = new BackupEntry("tests/target/2010-01-01.yearly");
		$filter = new EntryFilter();
		$filter->addPeriod(BackupEntry::YEARLY);
		$this->assertEquals(TRUE, $filter->match($yearly));
		$this->assertEquals(FALSE, $filter->match($daily));
	}
	
	function testMatchAll() {
		$daily = new BackupEntry("tests/target/2010-01-01");
		$weekly = new BackupEntry("tests/target/2010-01-03.weekly");
		$monthly = new BackupEntry("tests/target/2010-01-01.monthly");
		$yearly = new BackupEntry("tests/target/2010-01-01.yearly");
		$filter = new EntryFilter();
		$this->assertEquals(TRUE, $filter->match($daily));
		$this->assertEquals(TRUE, $filter->match($weekly));
		$this->assertEquals(TRUE, $filter->match($monthly));
		$this->assertEquals(TRUE, $filter->match($yearly));
	}
	
	function testMatchMultiple() {
		$daily = new BackupEntry("tests/target/2010-01-01");
		$weekly = new BackupEntry("tests/target/2010-01-03.weekly");
		$monthly = new BackupEntry("tests/target/2010-01-01.monthly");
		$yearly = new BackupEntry("tests/target/2010-01-01.yearly");
		$filter = new EntryFilter();
		$filter->addPeriod(BackupEntry::WEEKLY);
		$filter->addPeriod(BackupEntry::MONTHLY);
		$this->assertEquals(FALSE, $filter->match($daily));
		$this->assertEquals(TRUE, $filter->match($weekly));
		$this->assertEquals(TRUE, $filter->match($monthly));
		$this->assertEquals(FALSE, $filter->match($yearly));
	}
	
	function testMatchSubdir() {
		$daily = new BackupEntry("tests/target/2010-01-01");
		$weekly = new BackupEntry("tests/target/2010-01-03.weekly");
		$filter = new EntryFilter();
		$filter->setSubdir("subdir");
		$this->assertEquals(TRUE, $filter->match($daily));
		$this->assertEquals(FALSE, $filter->match($weekly));
	}
	
	function testMatchTo() {
		$array[] = new BackupEntry("tests/target/2010-01-01");
		$array[] = new BackupEntry("tests/target/2010-01-01.monthly");
		$array[] = new BackupEntry("tests/target/2010-01-01.yearly");
		$array[] = new BackupEntry("tests/target/2010-01-02");
		$array[] = new BackupEntry("tests/target/2010-01-03");
		$array[] = new BackupEntry("tests/target/2010-01-03.weekly");
		$filter = new EntryFilter();
		$filter->setTo(Date::fromIsodate("2010-01-02"));
		$matched = array();
		$expected = array_slice($array, 0, 4);
		foreach($array as $value) {
			if(!$filter->match($value)) {
				continue;
			}
			$matched[] = $value;
		}
		$this->assertEquals($matched, $expected);
	}
	
	function testMatchFrom() {
		$array[] = new BackupEntry("tests/target/2010-01-01");
		$array[] = new BackupEntry("tests/target/2010-01-01.monthly");
		$array[] = new BackupEntry("tests/target/2010-01-01.yearly");
		$array[] = new BackupEntry("tests/target/2010-01-02");
		$array[] = new BackupEntry("tests/target/2010-01-03");
		$array[] = new BackupEntry("tests/target/2010-01-03.weekly");
		$filter = new EntryFilter();
		$filter->setFrom(Date::fromIsodate("2010-01-02"));
		$matched = array();
		$expected = array_slice($array, 3);
		foreach($array as $value) {
			if(!$filter->match($value)) {
				continue;
			}
			$matched[] = $value;
		}
		$this->assertEquals($matched, $expected);
	}

	function testMatchFromTo() {
		$array[] = new BackupEntry("tests/target/2010-01-01");
		$array[] = new BackupEntry("tests/target/2010-01-01.monthly");
		$array[] = new BackupEntry("tests/target/2010-01-01.yearly");
		$array[] = new BackupEntry("tests/target/2010-01-02");
		$array[] = new BackupEntry("tests/target/2010-01-03");
		$array[] = new BackupEntry("tests/target/2010-01-03.weekly");
		$filter = new EntryFilter();
		$filter->setFrom(Date::fromIsodate("2010-01-02"));
		$filter->setFrom(Date::fromIsodate("2010-01-03"));
		$matched = array();
		$expected = array_slice($array, 4);
		foreach($array as $value) {
			if(!$filter->match($value)) {
				continue;
			}
			$matched[] = $value;
		}
		$this->assertEquals($matched, $expected);
	}
	
}
