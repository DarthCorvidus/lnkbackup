<?php
$scriptdir = __DIR__;
require_once $scriptdir."/src/include/lib/Loader.php";
require_once $scriptdir."/vendor/plibv4/command/src/Command.php";
$loader = new Loader($scriptdir."/src/include/lib");
$loader->silent();
$loader->addRepository($scriptdir."/src/include/local");
$loader->SplRegister();
