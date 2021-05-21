<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
class ValidateSourceTest extends TestCase {
	function testDirectoryNotLocal() {
		$this->assertEquals(FALSE, ValidateSource::isRemote("/local"));
		$this->assertEquals(FALSE, ValidateSource::isRemote("local/"));
	}
	
	function testDirectoryWithAtNotLocal() {
		$this->assertEquals(FALSE, ValidateSource::isRemote("/local@mybackup/"));
	}
	
	function testIsRemote() {
		$this->assertEquals(TRUE, ValidateSource::isRemote("example.com:/home/"));
	}
	
	function testDirectoryExists() {
		$validate = new ValidateSource();
		$this->assertEquals(NULL, $validate->validate(__DIR__));
	}
}

