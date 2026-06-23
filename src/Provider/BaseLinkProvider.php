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
 * Class BaseLinkProvider.
 *
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
abstract class BaseLinkProvider extends BaseProvider
{
    public function getProviderType(): int
    {
        return ProviderTypes::LINK;
    }
}
