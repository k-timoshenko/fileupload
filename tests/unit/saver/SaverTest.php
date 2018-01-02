<?php
declare(strict_types=1);

namespace unit\saver;

use tkanstantsin\fileupload\formatter\Factory as FormatterFactory;
use tkanstantsin\fileupload\model\Type;
use tkanstantsin\fileupload\saver\Saver;
use tkanstantsin\fileupload\stub\formatter\StringFormatterStub;

/**
 * Class SaverTest
 *
 * @todo: use memory adapter and create each time completely new FileManager.
 */
class SaverTest extends \Unit
{
    /**
     * @throws \tkanstantsin\fileupload\config\InvalidConfigException
     * @throws \League\Flysystem\FileNotFoundException
     * @throws \ReflectionException
     */
    public function testWriteString(): void
    {
        // TODO: simplify preparation before test.
        $fileManager = $this->tester->getFileManager();
        // TODO: use constant instead of `test-alias` string.
        $file = $this->tester->createFile(Type::FILE, 'test-alias');
        $alias = $fileManager->getAliasConfig($file->getModelAlias());
        $formatter = new StringFormatterStub($file, $fileManager->contentFS);
        $path = $alias->getFilePath($file);

        /**
         * Steps:
         * - get saver
         * - try write stream -> get true
         */

        $this->assertFalse($fileManager->contentFS->has($path), 'File not exist before test');

        $saver = new Saver($file, $fileManager->contentFS, $path);
        $this->assertTrue($saver->save($formatter), 'File saved');

        $this->assertTrue($fileManager->contentFS->has($path), 'File saved');
    }

    public function testWriteResource()
    {
        /**
         * Steps:
         * - get saver
         * - try write stream -> get true
         */
    }

    public function testWriteFalse(): void
    {
        /**
         * Steps:
         * - get saver
         * - try write FALSE -> get false
         */
    }

    public function testSave(): void
    {
        /**
         * Steps:
         * - get manager, fs, file
         * - save file into path -> return true and file created in fs
         */
    }

    public function testSaveSkipIfFileExist(): void
    {
        /**
         * Steps:
         * - get manager, fs, file
         * - write file into path
         * - set updateAt = time() - 10
         * - save file -> return true, but file not changed
         */
    }

    public function testForceSave(): void
    {
        /**
         * Steps:
         * - get manager, fs, file
         * - add empty file into path
         * - save with force flag -> file must be changed
         */
    }
}