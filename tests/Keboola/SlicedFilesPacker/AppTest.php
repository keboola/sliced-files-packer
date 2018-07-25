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
        $inputTablesDir = sys_get_temp_dir() . '/input' . uniqid();
        $outputFilesDir = sys_get_temp_dir() . '/output' . uniqid();
        $fs->mkdir([$inputTablesDir, $outputFilesDir]);

        // create test files
        $fs->dumpFile($inputTablesDir . '/323428022_comments/part.0', <<< EOF
1,"Short text","Whatever"
2,"Long text Long text Long text","Something else"
EOF
        );

        $fs->dumpFile($inputTablesDir . '/323428022_comments/part.1', <<< EOF
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

        $this->assertCount(2, (new Finder())->files()->in($outputFilesDir));
        $zipFiles = (new Finder())->files()->name('*.zip')->in($outputFilesDir);
        $this->assertCount(1, $zipFiles);

        $filesIterator = $zipFiles->getIterator();
        $filesIterator->rewind();
        $zip = new \ZipArchive();
        if ($zip->open($filesIterator->current()->getRealPath()) !== true) {
            throw new \Exception('Cannot open created zip package.');
        }
        $this->assertEquals(3, $zip->numFiles);

        // manifest
        $manifestFiles = (new Finder())->files()->in($outputFilesDir)->name('*.manifest');
        $this->assertCount(1, $manifestFiles);
        $filesIterator = $manifestFiles->getIterator();
        $filesIterator->rewind();

        $manifest = json_decode(file_get_contents($filesIterator->current()->getRealPath()));
        $this->assertNotEmpty($manifest->tags);
        $this->assertContains(getenv('KBC_COMPONENTID'), $manifest->tags);
    }

    public function testPackerEmptyDirs()
    {
        // create data dirs
        $fs = new Filesystem();
        $inputTablesDir = sys_get_temp_dir() . '/input' . uniqid();
        $outputFilesDir = sys_get_temp_dir() . '/output' . uniqid();
        $fs->mkdir([$outputFilesDir, $inputTablesDir . '/323428022_comments/']);

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
    "size_bytes": 0
}
EOF
        );

        $app = new App();
        $app->run($inputTablesDir, $outputFilesDir);

        $this->assertCount(2, (new Finder())->files()->in($outputFilesDir));
        $zipFiles = (new Finder())->files()->name('*.zip')->in($outputFilesDir);
        $this->assertCount(1, $zipFiles);

        $filesIterator = $zipFiles->getIterator();
        $filesIterator->rewind();
        $zip = new \ZipArchive();
        if ($zip->open($filesIterator->current()->getRealPath()) !== true) {
            throw new \Exception('Cannot open created zip package.');
        }
        $this->assertEquals(1, $zip->numFiles);

        // manifest
        $manifestFiles = (new Finder())->files()->in($outputFilesDir)->name('*.manifest');
        $this->assertCount(1, $manifestFiles);
        $filesIterator = $manifestFiles->getIterator();
        $filesIterator->rewind();

        $manifest = json_decode(file_get_contents($filesIterator->current()->getRealPath()));
        $this->assertNotEmpty($manifest->tags);
        $this->assertContains(getenv('KBC_COMPONENTID'), $manifest->tags);
    }
}
