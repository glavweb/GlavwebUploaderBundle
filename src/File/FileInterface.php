<?php

/*
 * This file is part of the Glavweb UploaderBundle package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\UploaderBundle\File;

/**
 * Interface FileInterface.
 *
 * Every function in this interface should be considered unsafe.
 * They are only meant to abstract away some basic file functionality.
 * For safe methods rely on the parent functions.
 */
interface FileInterface
{
    /**
     * Returns the size of the file.
     */
    public function getSize(): int|false;

    /**
     * Returns the path of the file.
     */
    public function getPathname(): string;

    /**
     * Return the path of the file without the filename.
     */
    public function getPath(): string;

    /**
     * Returns the guessed mime type of the file.
     */
    public function getMimeType(): ?string;

    /**
     * Returns the basename of the file.
     */
    public function getBasename(): string;

    /**
     * Returns the guessed extension of the file.
     */
    public function getExtension(): ?string;

    /**
     * Returns the original file name.
     *
     * It is extracted from the request from which the file has been uploaded.
     * Then it should not be considered as a safe value.
     *
     * @return string|null The original name
     */
    public function getClientOriginalName(): string;

    /**
     * Returns the extension based on the mime type.
     *
     * If the mime type is unknown, returns null.
     *
     * This method uses the mime type as guessed by getMimeType()
     * to guess the file extension.
     *
     * @return string|null The guessed extension or null if it cannot be guessed
     *
     * @see ExtensionGuesser
     * @see getMimeType()
     */
    public function guessExtension(): ?string;

    /**
     * Moves the file to a new location.
     *
     * @param string      $directory The destination folder
     * @param string|null $name      The new file name
     *
     * @return static A File object representing the new file
     */
    public function move(string $directory, ?string $name = null): static;
}
