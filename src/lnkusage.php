#!/usr/bin/php
<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license GPLv3
 */
$scriptdir = __DIR__;
#Include
include $scriptdir."/include/lib/Loader.php";
$loader = new Loader($scriptdir."/include/lib");
$loader->addRepository($scriptdir."/include/local");
$loader->SplRegister();
#/Include
try {
	$usage = new Usage($argv);
	$usage->run();
} catch (ArgvException $e) {
	echo $e->getMessage().PHP_EOL;
}