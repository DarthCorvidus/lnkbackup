#!/usr/bin/php
<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license GPLv3
 */
require_once __DIR__."/../vendor/autoload.php";
try {
	$copyjob = TrimJob::fromArgv($argv);
	$copyjob->run();
} catch (ArgvException $e) {
	echo $e->getMessage().PHP_EOL;
}