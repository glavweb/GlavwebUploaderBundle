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
 * Class BaseFileProvider
 *
 * @package Glavweb\UploaderBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
abstract class BaseFileProvider extends BaseProvider implements ProviderFileInterface
{
    /**
     * @var FileInterface
     */
    protected $file;

    /**
     * @return FileInterface
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return int
     */
    public function getProviderType()
    {
        return ProviderTypes::FILE;
    }
}