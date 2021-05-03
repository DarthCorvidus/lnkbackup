<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
class BackupEntryTest extends TestCase {
	function testConstruct() {
		$entry = new BackupEntry("test/2010-01-01");
		$this->assertInstanceOf(BackupEntry::class, $entry);
	}

	function testConstructInvalidPeriod() {
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage("invalid period baktun for entry 2010-01-01.baktun");
		$entry = new BackupEntry("tests/target/2010-01-01.baktun");
	}
	
	function testGetPeriod() {
		$entry = new BackupEntry("tests/target/2010-01-01.yearly");
		$this->assertEquals("yearly", $entry->getPeriod());
	}
	
	function testGetDate() {
		$entry = new BackupEntry("tests/target/2010-01-01.yearly");
		$this->assertEquals("2010-01-01", $entry->getDate()->getIsodate());
	}
	
	function testIsDaily() {
		$entry = new BackupEntry("tests/target/2010-01-01");
		$this->assertEquals(TRUE, $entry->isDaily());
	}

	function testIsWeekly() {
		$entry = new BackupEntry("tests/target/2010-01-03.weekly");
		$this->assertEquals(TRUE, $entry->isWeekly());
	}

	function testIsMonthly() {
		$entry = new BackupEntry("tests/target/2010-01-01.monthly");
		$this->assertEquals(TRUE, $entry->isMonthly());
	}

	function testIsYearly() {
		$entry = new BackupEntry("tests/target/2010-01-01.yearly");
		$this->assertEquals(TRUE, $entry->isYearly());
	}
	
	function testHasSubdir() {
		$entry = new BackupEntry("tests/target/2010-01-01");
		$this->assertEquals(TRUE, $entry->hasSubdir("subdir"));
	}

	function testSubdirMissing() {
		$entry = new BackupEntry("tests/target/2010-01-01.yearly");
		$this->assertEquals(FALSE, $entry->hasSubdir("subdir"));
	}

	function testGetPath() {
		$entry = new BackupEntry("tests/target/2010-01-01.yearly");
		$this->assertEquals("tests/target/2010-01-01.yearly", $entry->getPath());
	}

	function testGetBasename() {
		$entry = new BackupEntry("tests/target/2010-01-01.yearly");
		$this->assertEquals("2010-01-01.yearly", $entry->getBasename());
	}

}
