<?php

/*
 * This file is part of the Glavweb UploaderBundle package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\UploaderBundle\Naming;

use Glavweb\UploaderBundle\File\FileInterface;

/**
 * Interface NamerInterface
 *
 * @package Glavweb\UploaderBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
interface NamerInterface
{
    /**
     * Name a given file and return the name
     *
     * @param FileInterface $file
     * @return string
     */
    public function name(FileInterface $file);
}
