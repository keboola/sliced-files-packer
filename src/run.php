<?php

require_once(dirname(__FILE__) . "/../vendor/autoload.php");
require_once(dirname(__FILE__) . "/common.php");

$arguments = getopt("d::", array("data::"));
if (!isset($arguments["data"])) {
    print "Data folder not set.";
    exit(1);
}

try {
    print "Creating zip package.\n";
    $app = new \Keboola\SlicedFilesPacker\App();
    $app->run(
        $arguments["data"] . "/in/files",
        $arguments["data"] . "/out/files"
    );
    print "Zip package created.\n";
} catch (\Keboola\SlicedFilesPacker\UserException $e) {
    print $e->getMessage();
    exit(1);
} catch (\Exception $e) {
    print $e->getMessage();
    exit(2);
}
exit(0);
