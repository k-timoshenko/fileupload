<?php
declare(strict_types=1);

namespace tkanstantsin\fileupload\model;

/**
 * Interface IFile
 */
interface IFile
{

    /**
     * Filename if file haven't it.
     */
    public const DEFAULT_FILENAME = 'No name';

    /**
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * @param int $id
     * @return void
     */
    public function setId(int $id): void;


    /**
     * @see Type
     * @return int
     */
    public function getType(): ?int;

    /**
     * @see Type
     * @param int $type
     */
    public function setType(int $type): void;


    /**
     * Alias of associated model
     * @return null|string
     */
    public function getModelAlias(): ?string;

    /**
     * @param string $alias
     */
    public function setModelAlias(string $alias): void;


    /**
     * Id of associated model
     * @return int|null
     */
    public function getModelId(): ?int;

    /**
     * @param int $modelId
     */
    public function setModelId(int $modelId): void;


    /**
     * Get name without extension
     * @return null|string
     */
    public function getName(): ?string;

    /**
     * Set name without extension
     * @param string $name
     */
    public function setName(string $name): void;


    /**
     * Generate full file name (name itself + extension if exists)
     * @return null|string
     */
    public function getFullName(): ?string;


    /**
     * Get file extension
     * @return null|string
     */
    public function getExtension(): ?string;

    /**
     * Set file extension
     * @param string $extension
     */
    public function setExtension(string $extension): void;


    /**
     * Size int bytes
     * @return int
     */
    public function getSize(): ?int;

    /**
     * Set size
     * @param int $size
     */
    public function setSize(int $size): void;


    /**
     * @return null|string
     */
    public function getMimeType(): ?string;

    /**
     * @param string $mimeType
     */
    public function setMimeType(string $mimeType): void;


    /**
     * @return string
     */
    public function getHash(): ?string;

    /**
     * @param string $hash
     */
    public function setHash(string $hash): void;


    /**
     * Unix timestamp
     * @return int
     */
    public function getCreatedAt(): ?int;

    /**
     * Unix timestamp
     * @param int $createdAt
     */
    public function setCreatedAt(int $createdAt): void;


    /**
     * Unix timestamp
     * @return int|null
     */
    public function getUpdatedAt(): ?int;

    /**
     * Unix timestamp. MUST be set on create
     * @param int $updatedAt
     */
    public function setUpdatedAt(int $updatedAt): void;

}