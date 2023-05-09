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
     * @param array $parts
     * @return string
     */
    public static function path(string ...$parts): string
    {
        return implode(DIRECTORY_SEPARATOR, $parts);
    }

    /**
     * @param string      $filePath
     * @param string|null $extension
     * @return string
     */
    public static function appendExtension(string $filePath, ?string $extension): string
    {
        if ($extension) {
            return $filePath . '.' . $extension;
        }

        return $filePath;
    }

    /**
     * @param FileInterface $file
     * @return string
     */
    public static function saveFileWithNewVersion(FileInterface $file)
    {
        $pathParts = pathinfo($file->getPathname());
        $directory = $pathParts['dirname'];

        $filenameParts = explode('_', $pathParts['filename']);
        if (count($filenameParts) > 1) {
            $filenameParts[1]++;

        } else {
            $filenameParts[1] = '1';
        }
        $fileName = implode('_', $filenameParts) . '.' . $pathParts['extension'];

        $newFile = $file->move($directory, $fileName);

        return $newFile->getPathname();
    }

    /**
     * @param FileInterface $file
     * @param callable|null $isNameAllowed
     * @return string
     */
    public static function generateFileCopyBasename(FileInterface $file, callable $isNameAllowed = null): string
    {
        $fileInfo = pathinfo($file->getPathname());

        $name = $fileInfo['filename'];
        $extension = $fileInfo['extension'];
        $newCopyNumber = 0;
        $regexp = '/_copy(?:_(\d+))?$/';
        $originalName = $name;

        preg_match($regexp, $name, $matches);

        if ($matches) {
            [$match, $copyNumber] = $matches;

            if ($match) {
                if ($copyNumber) {
                    $newCopyNumber = (int)$copyNumber + 1;
                } else {
                    $newCopyNumber = 1;
                }

                $originalName = preg_replace($regexp, '', $name);
            }
        }

        do {
            $newName = $originalName . '_copy' . ($newCopyNumber > 0 ? '_' . $newCopyNumber : '');
            $newNameWithExtension = self::appendExtension($newName, $extension);
            $newCopyNumber++;
        } while ($isNameAllowed && !$isNameAllowed($newNameWithExtension));

        return $newNameWithExtension;
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
        return substr(strrchr($fileName, DIRECTORY_SEPARATOR), 1) ?: $fileName;
    }
}
