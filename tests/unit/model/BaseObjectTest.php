<?php


class BaseObjectTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testCorrectConfig()
    {
        /**
         * Steps:
         * - create fake base object with one property
         * - instantiate empty config -> property is null
         *
         * - instantiate with config with property -> property was set
         */
    }

    public function testInvalidConfig()
    {
        /**
         * Steps:
         * - declare expected exception @see \tkanstantsin\fileupload\config\InvalidConfigException
         * - instantiate with wrong property name
         */
    }
}