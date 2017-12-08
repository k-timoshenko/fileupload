<?php

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


    public function getId(): ?int;


    /**
     * Alias of associated model
     * @return null|string
     */
    public function getModelAlias(): ?string;


    /**
     * Id of associated model
     * @return int|null
     */
    public function getModelId(): ?int;


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


    public function getSize(): int;

    public function setSize(int $size): void;


    public function getType(): int;

    public function setType(int $type): void;


    public function getMimeType(): ?string;

    public function setMimeType(string $mimeType): void;


    public function getHash(): string;

    public function setHash(string $hash): void;


    public function getCreatedAt(): int;

    public function setCreatedAt(int $createdAt): void;


    public function getUpdatedAt(): ?int;

    public function setUpdatedAt(int $updatedAt): void;
}