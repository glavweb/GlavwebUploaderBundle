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
 * Interface FileInterface
 *
 * Every function in this interface should be considered unsafe.
 * They are only meant to abstract away some basic file functionality.
 * For safe methods rely on the parent functions.
 *
 * @package Glavweb\UploaderBundle
 */
interface FileInterface
{
    /**
     * Returns the size of the file
     *
     * @return int
     */
    public function getSize();

    /**
     * Returns the path of the file
     *
     * @return string
     */
    public function getPathname();

    /**
     * Return the path of the file without the filename
     *
     * @return mixed
     */
    public function getPath();

    /**
     * Returns the guessed mime type of the file
     *
     * @return string
     */
    public function getMimeType();

    /**
     * @return bool
     */
    public function isImage(): ?bool;

    /**
     * @return int
     */
    public function getWidth(): ?int;

    /**
     * @return int
     */
    public function getHeight(): ?int;

    /**
     * Returns the basename of the file
     *
     * @return string
     */
    public function getBasename();

    /**
     * Returns the guessed extension of the file
     *
     * @return mixed
     */
    public function getExtension();

    /**
     * Returns the original file name.
     *
     * It is extracted from the request from which the file has been uploaded.
     * Then it should not be considered as a safe value.
     *
     * @return string|null The original name
     */
    public function getClientOriginalName();

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
    public function guessExtension();

    /**
     * Moves the file to a new location.
     *
     * @param string $directory The destination folder
     * @param string $name      The new file name
     *
     * @return self A File object representing the new file
     */
    public function move($directory, $name = null);

    /**
     * @param string|null $directory
     * @param string|null $name
     * @return FileInterface new file copy
     */
    public function copy(string $directory = null, string $name = null): FileInterface;
}
