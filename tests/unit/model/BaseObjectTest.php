<?php


class BaseObjectTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testCorrectConfig(): void
    {
        $baseObject = new \tkanstantsin\fileupload\stub\BaseObjectStub([]);
        $this->assertNull($baseObject->property);

        $baseObject = new \tkanstantsin\fileupload\stub\BaseObjectStub([
            'property' => 'some not-null value',
        ]);
        $this->assertNotNull($baseObject->property);
    }

    public function testInvalidConfig(): void
    {
        $this->expectException(\tkanstantsin\fileupload\config\InvalidConfigException::class);
        new \tkanstantsin\fileupload\stub\BaseObjectStub([
            'wrongname' => 'some value',
        ]);
    }
}