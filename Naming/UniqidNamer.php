<?php

namespace Glavweb\UploaderBundle\Naming;

use Glavweb\UploaderBundle\File\FileInterface;

/**
 * Class UniqidNamer
 * @package Glavweb\UploaderBundle\Naming
 */
class UniqidNamer implements NamerInterface
{
    /**
     * @param FileInterface $file
     * @return string
     * @throws \RuntimeException
     */
    public function name(FileInterface $file)
    {
        $extension = $file->guessExtension();
        if (!$extension) {
            $extension = 'php';
//            throw new \RuntimeException('The extension cannot be guessed.');
        }

        $replace = array(
            'jpeg' => 'jpg'
        );
        $extension = strtr($extension, $replace);

        return sprintf('%s.%s', uniqid(), $extension);
    }
}
