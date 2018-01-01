<?php

class Unit extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @inheritdoc
     * @Override
     * @throws \LogicException
     */
    protected function _before()
    {
        $this->tester->cleanUpFS();
    }
}