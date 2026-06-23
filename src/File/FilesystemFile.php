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

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class FilesystemFile.
 *
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class FilesystemFile implements FileInterface
{
    public function __construct(private File $file, private readonly ?string $originalName = null)
    {
    }

    public function getExtension(): string
    {
        return $this->file->getExtension();
    }

    public function getSize(): int|false
    {
        return $this->file->getSize();
    }

    public function getPathname(): string
    {
        return $this->file->getPathname();
    }

    public function getPath(): string
    {
        return $this->file->getPath();
    }

    public function getMimeType(): ?string
    {
        return $this->file->getMimeType();
    }

    public function getBasename(): string
    {
        return $this->file->getBasename();
    }

    public function getClientOriginalName(): string
    {
        if ($this->originalName) {
            return $this->originalName;
        }

        if ($this->file instanceof UploadedFile) {
            return $this->file->getClientOriginalName();
        }

        return $this->file->getBasename();
    }

    public function guessExtension(): ?string
    {
        return $this->file->guessExtension();
    }

    public function move(string $directory, ?string $name = null): static
    {
        $this->file = $this->file->move($directory, $name);

        return $this;
    }
}
