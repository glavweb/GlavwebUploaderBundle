<?php

namespace Glavweb\UploaderBundle\Provider;

/**
 * Class ProviderLinkInterface
 * @package Glavweb\UploaderBundle\Provider
 */
interface ProviderLinkInterface extends ProviderInterface
{
    /**
     * @return string
     */
    public function getLink();
}