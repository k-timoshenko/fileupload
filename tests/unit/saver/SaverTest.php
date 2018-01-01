<?php


class SaverTest extends Unit
{
    public function testWhetherSaved()
    {
        // TODO: create component which would generate FileManager and it's components.
        // TODO: use memory adapter and create each time completely new FileManager.
        /**
         * Steps:
         * - get FS
         * - get IFile
         * - get asset path
         *
         * - check if NOT saved
         *
         * --//--
         * - write something random in asset path in FS
         * - check if IS saved
         *
         * --//--
         * - write something random in asset path in FS
         * - set updatedAt into future
         * - check if NOT saved
         */
    }

    public function testWrite()
    {
        /**
         * Steps:
         * - get saver
         * - try write null or false -> get false
         *
         * - get saver
         * - try write anything else -> get true or exception
         */
    }

    public function testSave()
    {
        /**
         * Steps:
         * - get manager, fs, file
         * - save file into path -> return true and file created in fs
         */
    }

    public function testSaveSkipIfFileExist()
    {
        /**
         * Steps:
         * - get manager, fs, file
         * - write file into path
         * - set updateAt = time() - 10
         * - save file -> return true, but file not changed
         */
    }

    public function testForceSave()
    {
        /**
         * Steps:
         * - get manager, fs, file
         * - add empty file into path
         * - save with force flag -> file must be changed
         */
    }
}