<?php

require_once(dirname(__FILE__) . "/../vendor/autoload.php");

$arguments = getopt("d::", array("data::"));
if (!isset($arguments["data"])) {
    print "Data folder not set.";
    exit(1);
}

try {
    print "Preparing merged file for upload";
    $app = new \Keboola\SlicedFilesPacker\App();
    $app->run(
        $arguments["data"] . "/in/tables",
        $arguments["data"] . "/out/files"
    );
    print "Preparation done";
} catch (\Exception $e) {
    print $e->getMessage();
    exit(2);
}

exit(0);
