<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
class JobConfigsTest extends TestCase {
	function testConstructFile() {
		$jobs = new JobConfigs(__DIR__."/conf.d/local.conf");
		$this->assertInstanceOf(JobConfigs::class, $jobs);
		$this->assertEquals(1, $jobs->getCount());
	}
	
	function testConstructFolderProduction() {
		$this->expectOutputString("Error in ".__DIR__."/conf.d/damaged.conf: [\"exclude\"]: path does not exist.\n");
		$jobs = new JobConfigs(__DIR__."/conf.d");
		$this->assertInstanceOf(JobConfigs::class, $jobs);
		$this->assertEquals(2, $jobs->getCount());
	}
}
