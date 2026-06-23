<?php

namespace Glavweb\UploaderBundle\Util;

use Glavweb\UploaderBundle\Exception\Base64DecodingException;
use Glavweb\UploaderBundle\Exception\FileNotFoundException;
use Glavweb\UploaderBundle\File\FileInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class FileUtils.
 *
 * @author Sergey Zvyagintsev <nitron.ru@gmail.com>
 */
class FileUtils
{
    public static function saveFileWithNewVersion(FileInterface $file): string
    {
        $pathParts = pathinfo($file->getPathname());
        $directory = $pathParts['dirname'];

        $filenameFarts = explode('_', $pathParts['filename']);
        if (\count($filenameFarts) > 1) {
            ++$filenameFarts[1];
        } else {
            $filenameFarts[1] = '1';
        }

        $fileName = implode('_', $filenameFarts).'.'.$pathParts['extension'];

        $newFile = $file->move($directory, $fileName);

        return $newFile->getPathname();
    }

    /**
     * @throws Base64DecodingException
     * @throws FileNotFoundException
     */
    public static function getTempFileByUrl(string $link): ?File
    {
        $source = null;
        $target = null;
        $path = tempnam(sys_get_temp_dir(), 'gup');

        try {
            if (StringUtils::isUrl($link)) {
                $source = fopen($link, 'r');
                if (!$source) {
                    throw new FileNotFoundException(\sprintf("File '%s' not found", $link));
                }

                $target = fopen($path, 'w');

                if (!stream_copy_to_stream($source, $target)) {
                    throw new \RuntimeException('Stream copy failed');
                }
            } else {
                $fileContents = base64_decode($link);
                if ('' === $fileContents || '0' === $fileContents) {
                    throw new Base64DecodingException("File can't be decoded from string");
                }

                $target = fopen($path, 'w');

                if (!fwrite($target, $fileContents)) {
                    throw new \RuntimeException('Unable to write content into temporal file');
                }
            }

            $path = stream_get_meta_data($target)['uri'];
        } finally {
            if (\is_resource($source)) {
                fclose($source);
            }

            if (\is_resource($target)) {
                fclose($target);
            }
        }

        return new File($path);
    }

    /**
     * Fix bug with cyrillic symbols.
     */
    public static function basename(string $fileName): string
    {
        return substr(strrchr($fileName, \DIRECTORY_SEPARATOR), 1) ?: $fileName;
    }
}
