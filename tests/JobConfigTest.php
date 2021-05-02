<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
class JobConfigTest extends TestCase {
	function testConstructPrivate() {
		$this->expectException(Error::class);
		$job = new JobConfig();
	}
	
	function testFromFile() {
		$job = JobConfig::fromFile(__DIR__."/conf.d/local.ini");
		$this->assertInstanceOf(JobConfig::class, $job);
	}
	
	function testFromArray() {
		$array["source"] = __DIR__."/source/";
		$array["target"] = __DIR__."/target/";
		$array["exclude"] = __DIR__."/conf.d/exclude-local.txt";
		$job = JobConfig::fromArray($array);
		$this->assertInstanceOf(JobConfig::class, $job);
	}
}
