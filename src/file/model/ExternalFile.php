<?php
/**
 * Created by PhpStorm.
 * User: konstantin
 * Date: 1/6/18
 * Time: 3:12 PM
 */

namespace tkanstantsin\fileupload\model;

use Mimey\MimeTypes;
use tkanstantsin\fileupload\config\InvalidConfigException;
use tkanstantsin\fileupload\model\Type as FileType;

class ExternalFile extends BaseObject implements IFile
{
    /**
     * Length of hash from path
     * @see ExternalFile::generateId()
     */
    private const ID_PATH_HASH_LENGTH = 4;

    /**
     * @var int|null
     */
    private $id;
    /**
     * @var string|null
     */
    private $modelAlias;
    /**
     * @var int|null
     */
    private $modelId;
    /**
     * @var string|null
     */
    private $name;
    /**
     * @var string|null
     */
    private $extension;
    /**
     * @var int|null
     */
    private $size;
    /**
     * @see FileType
     * @var int|null
     */
    private $type;
    /**
     * @var string|null
     */
    private $mimeType;
    /**
     * @var string|null
     */
    private $hash;
    /**
     * @var int|null
     */
    private $createdAt;
    /**
     * @var int|null
     */
    private $updatedAt;

    /**
     * Path to file in filesystem
     * @var string|null
     */
    private $actualPath;

    /**
     * Build IFile object based only on file path, model alias and id
     * @param null|string $path
     * @param array $config
     * Config MUST contain following fields:
     * - modelAlias
     * - modelId
     *
     * @return ExternalFile|null
     * @throws \tkanstantsin\fileupload\config\InvalidConfigException
     */
    public static function buildFromPath(?string $path, array $config = []): ?self
    {
        // detect if file path actually is url
        if (preg_match('/^(https?:)?\/\//', $path)) {
            // TODO: create special file that will not be processed.
            return null;
        }
        if ($path === null) {
            return null;
        }

        $file = new static($config);
        if ($file->getModelAlias() === null) {
            throw new InvalidConfigException('Model alias must be defined');
        }
        if ($file->getModelId() === null) {
            throw new InvalidConfigException('Model id must be defined');
        }

        $file->setActualPath($path);
        $file->setId($file->generateId());
        $file->setHash(crc32($file->getId()));
        $file->setCreatedAt($file->getCreatedAt() ?? 0);
        $file->setUpdatedAt($file->getUpdatedAt() ?? 0);

        if ($file->getMimeType() === null && $file->getExtension() !== null) {
            $mimeType = (new MimeTypes())->getMimeType($file->getExtension());
            if ($mimeType !== null) {
                $file->setMimeType($mimeType);
            }
        }

        if ($file->getType() === null) {
            $file->setType(FileType::getByMimeType($file->getMimeType()));
        }

        $file->setName(pathinfo($path, PATHINFO_FILENAME));
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if ($extension !== '') {
            $file->setExtension($extension);
        }

        return $file;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return void
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Alias of associated model
     * @return null|string
     */
    public function getModelAlias(): ?string
    {
        return $this->modelAlias;
    }

    /**
     * @param string $alias
     */
    public function setModelAlias(string $alias): void
    {
        $this->modelAlias = $alias;
    }

    /**
     * Id of associated model
     * @return int|null
     */
    public function getModelId(): ?int
    {
        return $this->modelId;
    }

    /**
     * @param int $modelId
     */
    public function setModelId(int $modelId): void
    {
        $this->modelId = $modelId;
    }

    /**
     * Get name without extension
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set name without extension
     *
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Generate full file name (name itself + extension if exists)
     * @return null|string
     */
    public function getFullName(): ?string
    {
        return $this->name . ($this->extension !== null ? '.' . $this->extension : '');
    }

    /**
     * Get file extension
     * @return null|string
     */
    public function getExtension(): ?string
    {
        return $this->extension;
    }

    /**
     * Set file extension
     *
     * @param string $extension
     */
    public function setExtension(string $extension): void
    {
        $this->extension = $extension;
    }

    /**
     * @return int
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * @param int $size
     */
    public function setSize(int $size): void
    {
        $this->size = $size;
    }

    /**
     * @return int
     */
    public function getType(): ?int
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType(int $type): void
    {
        $this->type = $type;
    }

    /**
     * @return null|string
     */
    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    /**
     * @param string $mimeType
     */
    public function setMimeType(string $mimeType): void
    {
        $this->mimeType = $mimeType;
    }

    /**
     * @return string
     */
    public function getHash(): ?string
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     */
    public function setHash(string $hash): void
    {
        $this->hash = $hash;
    }

    /**
     * @return int
     */
    public function getCreatedAt(): ?int
    {
        return $this->createdAt;
    }

    /**
     * @param int $createdAt
     */
    public function setCreatedAt(int $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return int|null
     */
    public function getUpdatedAt(): ?int
    {
        return $this->updatedAt;
    }

    /**
     * @param int $updatedAt
     */
    public function setUpdatedAt(int $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return string
     */
    public function getActualPath(): ?string
    {
        return $this->actualPath;
    }

    /**
     * @param string $actualPath
     */
    public function setActualPath(string $actualPath): void
    {
        $this->actualPath = $actualPath;
    }


    /**
     * Returns existed id or generate unique id based on model id and file path.
     * @return int
     */
    protected function generateId(): int
    {
        if ($this->getId() !== null) {
            return $this->getId();
        }

        $maxIdLength = \mb_strlen((string) PHP_INT_MAX) - 1;

        // try generate unique id for file
        $id = mb_substr(crc32($this->getModelId()), $maxIdLength - self::ID_PATH_HASH_LENGTH)
            . mb_substr(crc32($this->getActualPath()), self::ID_PATH_HASH_LENGTH);

        return (int) $id;
    }
}