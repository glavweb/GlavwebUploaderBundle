<?php

/*
 * This file is part of the Glavweb UploaderBundle package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\UploaderBundle\Provider;

use Glavweb\UploaderBundle\File\FileInterface;
use Glavweb\UploaderBundle\Model\MediaInterface;

/**
 * Interface ProviderInterface.
 *
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
interface ProviderInterface
{
    public function parse(FileInterface|string $link): void;

    public function isParsed(): bool;

    public function checkLink(FileInterface|string $link): bool;

    public function getName(): ?string;

    public function getDescription(): ?string;

    public function getProviderName(): string;

    public function getProviderType(): int;

    public function getProviderReference(): ?string;

    public function getWidth(): ?int;

    public function getHeight(): ?int;

    public function getContentType(): ?string;

    public function getContentSize(): ?int;

    public function getThumbnailUrl(): ?string;

    public function display(MediaInterface $media, array $options = []): string;
}
