<?php

namespace Keboola\SlicedFilesPacker;

use PHPUnit\Framework\TestCase;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class AppTest extends TestCase
{

    public function testPacker()
    {
        // create data dirs
        $fs = new Filesystem();
        $finder = new Finder();
        $inputTablesDir = sys_get_temp_dir() . '/input';
        $outputFilesDir = sys_get_temp_dir() . '/output';
        $fs->mkdir([$inputTablesDir, $outputFilesDir]);

        // create test files
        $fs->dumpFile($inputTablesDir . '/323428022_comments.0', <<< EOF
1,"Short text","Whatever"
2,"Long text Long text Long text","Something else"
EOF
        );

        $fs->dumpFile($inputTablesDir . '/323428022_comments.1', <<< EOF
3,"Short text","Whatever"
4,"Long text Long text Long text","Something else"
EOF
        );
        $fs->dumpFile($inputTablesDir . '/323428022_comments.manifest', <<< EOF
{
    "id": 323428022,
    "name": "comments",
    "created": "2017-10-10T15:46:12+0200",
    "is_public": false,
    "is_encrypted": true,
    "is_sliced": true,
    "tags": [],
    "max_age_days": 180,
    "size_bytes": 4240
}
EOF
        );

        $app = new App();
        $app->run($inputTablesDir, $outputFilesDir);

        $foundFiles = $finder->files()->in($outputFilesDir);
        $this->assertCount(1, $foundFiles);
        $gzFiles = $foundFiles->name('*.zip');
        $filesIterator = $gzFiles->getIterator();

        $filesIterator->rewind();

        $zip = new \ZipArchive();

        if ($zip->open($filesIterator->current()->getRealPath()) !== true) {
            throw new \Exception('Cannot open created zip package.');
        }
        $this->assertEquals(2, $zip->numFiles);
    }
}
