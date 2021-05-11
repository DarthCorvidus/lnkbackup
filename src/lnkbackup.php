#!/usr/bin/php
<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license GPLv3
 */
require_once __DIR__."/../vendor/autoload.php";

$model = new ArgvBackup();
if(count($argv)==1) {
	$reference = new ArgvReference($model);
	echo $reference->getReference();
	die();
}

try {
	$args = new Argv($argv, $model);
} catch (ArgvException $e) {
	echo $e->getMessage().PHP_EOL;
	die();
}
$jobConfigs = new JobConfigs($args->getPositional(0));
for($i=0;$i<$jobConfigs->getCount();$i++) {
	$config = $jobConfigs->getJob($i);
	$job = new BackupJob($config, $args);
	$job->execute();
}