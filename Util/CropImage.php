<?php

/*
 * This file is part of the Glavweb UploaderBundle package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\UploaderBundle\Util;

use Glavweb\UploaderBundle\Exception\CropImageException;

/**
 * Class CropImage
 *
 * @package Glavweb\UploaderBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class CropImage
{
    /**
     * @param string $sourcePath
     * @param string $targetPath
     * @param array $cropData
     * @throws CropImageException
     */
    public static function crop(string $sourcePath, string $targetPath, array $cropData): bool
    {
        // crop
        $sourceImage = self::getImage($sourcePath);

        $croppedImage = imagecrop($sourceImage, [
            'x'      => $cropData['x'],
            'y'      => $cropData['y'],
            'width'  => $cropData['width'],
            'height' => $cropData['height']
        ]);

        if (!$croppedImage) {
            throw new CropImageException('Failed to crop the image file.');
        }

        $size = getimagesize($sourcePath);

        $notChanged =
            $size !== false &&
            $cropData['x'] == 0 &&
            $cropData['y'] == 0 &&
            $cropData['width'] == $size[0]  &&
            $cropData['height'] == $size[1]
        ;

        if ($notChanged) {
            return false;
        }

        self::saveImage($croppedImage, $targetPath);

        imagedestroy($sourceImage);
        imagedestroy($croppedImage);

        return true;
    }

    /**
     * @param string $sourcePath
     * @return resource
     * @throws CropImageException
     */
    private static function getImage(string $sourcePath)
    {
        $imageType = exif_imagetype($sourcePath);

        switch ($imageType) {
            case IMAGETYPE_GIF:
                $sourceImage = imagecreatefromgif($sourcePath);
                break;

            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;

            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
        }

        if (!$sourceImage) {
            throw new CropImageException('Failed to read the image file.');
        }

        return $sourceImage;
    }

    /**
     * @param resource $targetImage
     * @param string $targetPath
     * @throws CropImageException
     */
    private static function saveImage($targetImage, string $targetPath): void
    {
        $imageType = exif_imagetype($targetPath);

        $error = false;
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                if (!imagejpeg($targetImage, $targetPath, 99)) {
                    $error = true;
                }
                break;

            case IMAGETYPE_PNG:
                if (!imagepng($targetImage, $targetPath)) {
                    $error = true;
                }
                break;

            case IMAGETYPE_GIF:
                if (!imagegif($targetImage, $targetPath)) {
                    $error = true;
                }
                break;
        }

        if ($error) {
            throw new CropImageException('Failed to save the cropped image file.');
        }
    }
}