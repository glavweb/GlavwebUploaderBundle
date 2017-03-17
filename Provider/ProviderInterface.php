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
 * Interface ProviderInterface
 *
 * @package Glavweb\UploaderBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
interface ProviderInterface
{
    /**
     * @param string|FileInterface $link
     * @return void
     */
    public function parse($link);

    /**
     * @return boolean
     */
    public function isParsed();

    /**
     * @param string|FileInterface $link
     * @return boolean
     */
    public function checkLink($link);

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return string
     */
    public function getProviderName();

    /**
     * @return int
     */
    public function getProviderType();

    /**
     * @return string
     */
    public function getProviderReference();

    /**
     * @return string
     */
    public function getWidth();

    /**
     * @return string
     */
    public function getHeight();

    /**
     * @return string
     */
    public function getContentType();

    /**
     * @return string
     */
    public function getContentSize();

    /**
     * @return string
     */
    public function getThumbnailUrl();

    /**
     * @param MediaInterface $media
     * @param array $options
     * @return string
     */
    public function display(MediaInterface $media, array $options = array());
}