<?php

namespace Glavweb\UploaderBundle\Provider;

/**
 * Class BaseProvider
 * @package Glavweb\UploaderBundle\Provider
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