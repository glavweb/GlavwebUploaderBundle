<?php

namespace Glavweb\UploaderBundle\Util;

use Glavweb\UploaderBundle\Exception\Base64DecodingException;
use Glavweb\UploaderBundle\Exception\FileNotFoundException;
use Glavweb\UploaderBundle\File\FileInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class FileUtils
 *
 * @package Glavweb\UploaderBundle\Util
 *
 * @author Sergey Zvyagintsev <nitron.ru@gmail.com>
 */
class FileUtils
{
    /**
     * @param FileInterface $file
     * @return string
     */
    public static function saveFileWithNewVersion(FileInterface $file)
    {
        $pathParts = pathinfo($file->getPathname());
        $directory = $pathParts['dirname'];

        $filenameFarts = explode('_', $pathParts['filename']);
        if (count($filenameFarts) > 1) {
            $filenameFarts[1]++;

        } else {
            $filenameFarts[1] = '1';
        }
        $fileName = implode('_', $filenameFarts) . '.' . $pathParts['extension'];

        $newFile = $file->move($directory, $fileName);

        return $newFile->getPathname();
    }

    /**
     * @param string $link
     * @return File|null
     * @throws Base64DecodingException
     * @throws FileNotFoundException
     */
    public static function getTempFileByUrl($link)
    {
        $source = null;
        $target = null;
        $path = null;

        try {
            if (StringUtils::isUrl($link)) {
                $source = fopen($link, 'rb');
                if (!$source) {
                    throw new FileNotFoundException("File '$link' not found");
                }

                $target = tmpfile();

                if (!stream_copy_to_stream($source, $target)) {
                    throw new \RuntimeException('Stream copy failed');
                }
            } else {
                $fileContents = base64_decode($link);
                if (!$fileContents) {
                    throw new Base64DecodingException("File can't be decoded from string");
                }

                $target = tmpfile();

                if (!fwrite($target, $fileContents)) {
                    throw new \RuntimeException('Unable to write content into temporal file');
                }
            }

            $path = stream_get_meta_data($target)['uri'];
        } finally {
            if (is_resource($source)) {
                fclose($source);
            }
            if (is_resource($target)) {
                fclose($target);
            }
        }

        return new File($path);
    }

    /**
     * Fix bug with cyrillic symbols
     *
     * @param string $fileName
     * @return string
     */
    public static function basename(string $fileName): string
    {
        $newContentPath = rawurldecode(basename(rawurlencode($fileName)));
    }
}
