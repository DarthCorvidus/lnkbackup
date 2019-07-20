#!/usr/bin/php
<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license GPLv3
 */
$scriptdir = __DIR__;
require_once $scriptdir."/include/lib/Loader.php";
$loader = new Loader($scriptdir."/include/lib");
$loader->addRepository($scriptdir."/include/local");
$loader->SplRegister();

try {
	$model = new ArgvBackup();
	$args = new Argv($argv, $model);
} catch (Exception $e) {
	echo $e->getMessage().PHP_EOL;
	die();
}
$jobConfigs = new JobConfigs($argv[1]);
for($i=0;$i<$jobConfigs->getCount();$i++) {
	$config = $jobConfigs->getJob($i);
	$job = new BackupJob($config, $args);
	$job->execute();
}