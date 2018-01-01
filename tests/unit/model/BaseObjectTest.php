<?php

namespace unit\model;

use tkanstantsin\fileupload\config\InvalidConfigException;
use tkanstantsin\fileupload\stub\BaseObjectStub;

class BaseObjectTest extends \Unit
{
    /**
     * @throws InvalidConfigException
     */
    public function testCorrectConfig(): void
    {
        $baseObject = new BaseObjectStub([]);
        $this->assertNull($baseObject->property);

        $baseObject = new BaseObjectStub([
            'property' => 'some not-null value',
        ]);
        $this->assertNotNull($baseObject->property);
    }

    /**
     * @throws InvalidConfigException
     */
    public function testInvalidConfig(): void
    {
        $this->expectException(InvalidConfigException::class);
        new BaseObjectStub([
            'wrongname' => 'some value',
        ]);
    }
}