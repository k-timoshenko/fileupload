<?php

use League\Flysystem\Adapter\Local as LocalFSAdapter;
use League\Flysystem\Filesystem;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = null)
 *
 * @SuppressWarnings(PHPMD)
 */
class UnitTester extends \Codeception\Actor
{
    use _generated\UnitTesterActions;

    /**
     * Define custom actions here
     */

    /**
     * @return \tkanstantsin\fileupload\FileManager
     * @throws \LogicException
     * @throws \ErrorException
     */
    public function getFileManager(): \tkanstantsin\fileupload\FileManager
    {
        return new \tkanstantsin\fileupload\FileManager([
            'aliasArray' => [
                'test-alias' => [
                    'maxCount' => 1,
                ],
            ],
            'contentFS' => $this->getContentFS(),
            'cacheFS' => $this->getCacheFS(),
            'formatterConfigArray' => [
                'watermark-test' => [
                    'class' => \tkanstantsin\fileupload\formatter\Image::class,
                    'width' => 500,
                    'height' => 500,
                    'mode' => \tkanstantsin\fileupload\formatter\Image::RESIZE_OUTBOUND,
                    'formatAdapterArray' => [],
                ],
            ],
        ]);
    }

    /**
     * @return Filesystem
     * @throws \LogicException
     */
    public function getContentFS(): Filesystem
    {
        return new Filesystem(new LocalFSAdapter(__DIR__ . '/../../tmp/content', LOCK_EX, LocalFSAdapter::DISALLOW_LINKS));
    }

    /**
     * @return Filesystem
     * @throws \LogicException
     */
    public function getCacheFS(): Filesystem
    {
        return new Filesystem(new LocalFSAdapter(__DIR__ . '/../../tmp/cache', LOCK_EX, LocalFSAdapter::DISALLOW_LINKS));
    }

    /**
     * Removes all temp files before or after test.
     * @throws \LogicException
     */
    public function cleanUpFS(): void
    {
        foreach ([
                     $this->getContentFS(),
                     $this->getCacheFS(),
                 ] as $filesystem) {
            /* @var Filesystem $filesystem */
            $fileArray = $filesystem->listContents('/');

            foreach ($fileArray as $file) {
                if ($file['basename'] === '.gitignore') {
                    continue;
                }

                $file['type'] === 'file'
                    ? $filesystem->delete($file['path'])
                    : $filesystem->deleteDir($file['path']);
            }
            
        }
    }
}
