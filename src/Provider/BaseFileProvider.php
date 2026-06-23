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

/**
 * Class BaseFileProvider.
 *
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
abstract class BaseFileProvider extends BaseProvider implements ProviderFileInterface
{
    protected FileInterface $file;

    public function getFile(): FileInterface
    {
        return $this->file;
    }

    public function getProviderType(): int
    {
        return ProviderTypes::FILE;
    }
}
