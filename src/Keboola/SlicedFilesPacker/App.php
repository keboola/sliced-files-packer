<?php

namespace Keboola\SlicedFilesPacker;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class App
{

    public function run($inputFilesFolderPath, $outputFilesFolderPath)
    {
        $fileManifest = $this->getManifestFile($inputFilesFolderPath);
        if (!$fileManifest->is_sliced) {
            throw new UserException('Input file is not sliced.');
        }

        $zip = new \ZipArchive();
        $zipFilePath = $outputFilesFolderPath . DIRECTORY_SEPARATOR . sprintf('%s.zip', $fileManifest->name);
        $zip->open($zipFilePath, \ZipArchive::CREATE);

        foreach ($this->getDataFiles($inputFilesFolderPath, $fileManifest->id) as $dataFile) {
            if (!$zip->addFile($dataFile->getRealPath(), $dataFile->getRelativePathname())) {
                throw new \Exception(
                    sprintf('Cannot add %s to package', $dataFile->getRealPath())
                );
            }
        }
        $zip->close();

        $this->createManifestFile($zipFilePath);
    }

    private function getManifestFile($inputFilesFolderPath)
    {
        $manifestFiles = (new Finder())->files()->in($inputFilesFolderPath)->name('*.manifest');

        if ($manifestFiles->count() === 0) {
            throw new UserException('Manifest file not found.');
        }

        if ($manifestFiles->count() > 1) {
            throw new UserException('Only one sliced file is supported.');
        }

        $iterator = $manifestFiles->getIterator();
        $iterator->rewind();
        return (new JsonDecode())->decode(file_get_contents($iterator->current()->getRealPath()), JsonEncoder::FORMAT);
    }

    private function getDataFiles($inputFilesFolderPath, $fileId)
    {
        return (new Finder())
            ->files()
            ->in($inputFilesFolderPath)
            ->name(sprintf('%s_*', $fileId))
            ->notName('*.manifest');
    }

    private function createManifestFile($zipFilePath)
    {
        (new Filesystem())->dumpFile($zipFilePath . '.manifest', json_encode([
            'is_encrypted' => true,
            'tags' => [
                getenv('KBC_COMPONENTID'),
            ],
        ]));
    }
}
