<?php

namespace Helper;

use Faker\Factory;
use tkanstantsin\fileupload\model\IFile;

class TestFileFactory
{
    private const IMAGE_PATH = '/tests/data/file/image';
    private const IMAGE_EXTENSION_ARRAY = ['jpg', 'jpeg', 'png', 'gif'];

    /**
     * @var Factory
     */
    private $factory;
    /**
     * @var \Mimey\MimeTypes
     */
    private $mimes;

    /**
     * TestFileFactory constructor.
     */
    public function __construct()
    {
        $this->factory = Factory::create();
        $this->mimes = new \Mimey\MimeTypes();
    }

    public function create(int $type, string $alias, array $params = []): IFile
    {
        $extension = $this->factory->randomElement(self::IMAGE_EXTENSION_ARRAY);
        $updatedAt = $this->factory->unixTime;
        $createdAt = $this->factory->unixTime($updatedAt);

        $file = new TestFile($params + [
                'id' => $this->factory->unique()->randomNumber,
                'modelAlias' => $alias,
                'modelId' => $this->factory->unique()->randomNumber,
                'name' => $this->factory->name,
                'extension' => $extension,
                'size' => $this->factory->randomNumber(),
                'type' => $type,
                'mimeType' => $this->mimes->getMimeType($extension),
                'hash' => $this->factory->md5,
                'createdAt' => $createdAt,
                'updatedAt' => $updatedAt,
            ]);

        return $file;
    }

}