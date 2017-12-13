<?php

namespace Helper;

use tkanstantsin\fileupload\model\IFile;

/**
 * Class TestFile
 */
class TestFile implements IFile
{
    /**
     * @var int|null
     */
    public $id;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return void
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @see Type
     * @return int
     */
    public function getType(): int
    {
        // TODO: Implement getType() method.
    }

    /**
     * @see Type
     * @param int $type
     */
    public function setType(int $type): void
    {
        // TODO: Implement setType() method.
    }

    /**
     * Alias of associated model
     * @return null|string
     */
    public function getModelAlias(): ?string
    {
        // TODO: Implement getModelAlias() method.
    }

    /**
     * @param string $alias
     */
    public function setModelAlias(string $alias): void
    {
        // TODO: Implement setModelAlias() method.
    }

    /**
     * Id of associated model
     * @return int|null
     */
    public function getModelId(): ?int
    {
        // TODO: Implement getModelId() method.
    }

    /**
     * @param int $modelId
     */
    public function setModelId(int $modelId): void
    {
        // TODO: Implement setModelId() method.
    }

    /**
     * Get name without extension
     * @return null|string
     */
    public function getName(): ?string
    {
        // TODO: Implement getName() method.
    }

    /**
     * Set name without extension
     * @param string $name
     */
    public function setName(string $name): void
    {
        // TODO: Implement setName() method.
    }

    /**
     * Generate full file name (name itself + extension if exists)
     * @return null|string
     */
    public function getFullName(): ?string
    {
        // TODO: Implement getFullName() method.
    }

    /**
     * Get file extension
     * @return null|string
     */
    public function getExtension(): ?string
    {
        // TODO: Implement getExtension() method.
    }

    /**
     * Set file extension
     * @param string $extension
     */
    public function setExtension(string $extension): void
    {
        // TODO: Implement setExtension() method.
    }

    /**
     * Size int bytes
     * @return int
     */
    public function getSize(): int
    {
        // TODO: Implement getSize() method.
    }

    /**
     * Set size
     * @param int $size
     */
    public function setSize(int $size): void
    {
        // TODO: Implement setSize() method.
    }

    /**
     * @return null|string
     */
    public function getMimeType(): ?string
    {
        // TODO: Implement getMimeType() method.
    }

    /**
     * @param string $mimeType
     */
    public function setMimeType(string $mimeType): void
    {
        // TODO: Implement setMimeType() method.
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        // TODO: Implement getHash() method.
    }

    /**
     * @param string $hash
     */
    public function setHash(string $hash): void
    {
        // TODO: Implement setHash() method.
    }

    /**
     * Unix timestamp
     * @return int
     */
    public function getCreatedAt(): int
    {
        // TODO: Implement getCreatedAt() method.
    }

    /**
     * Unix timestamp
     * @param int $createdAt
     */
    public function setCreatedAt(int $createdAt): void
    {
        // TODO: Implement setCreatedAt() method.
    }

    /**
     * Unix timestamp
     * @return int|null
     */
    public function getUpdatedAt(): ?int
    {
        // TODO: Implement getUpdatedAt() method.
    }

    /**
     * Unix timestamp. MUST be set on create
     * @param int $updatedAt
     */
    public function setUpdatedAt(int $updatedAt): void
    {
        // TODO: Implement setUpdatedAt() method.
    }
}