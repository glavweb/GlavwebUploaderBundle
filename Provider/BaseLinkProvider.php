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

/**
 * Class BaseLinkProvider
 *
 * @package Glavweb\UploaderBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
abstract class BaseLinkProvider extends BaseProvider implements ProviderLinkInterface
{
    /**
     * @var string
     */
    protected $link;

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @return int
     */
    public function getProviderType()
    {
        return ProviderTypes::LINK;
    }
}