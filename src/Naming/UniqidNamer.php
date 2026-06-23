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
 * Class UniqidNamer.
 *
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class UniqidNamer implements NamerInterface
{
    /**
     * @throws \RuntimeException
     */
    public function name(FileInterface $file): string
    {
        $extension = $file->guessExtension();
        if (!$extension) {
            throw new \RuntimeException('The extension cannot be guessed.');
        }

        $replace = [
            'jpeg' => 'jpg',
        ];
        $extension = strtr($extension, $replace);

        return \sprintf('%s.%s', uniqid(), $extension);
    }
}
