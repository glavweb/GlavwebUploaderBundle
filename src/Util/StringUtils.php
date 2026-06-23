<?php

namespace Glavweb\UploaderBundle\Util;

/**
 * Class StringUtils.
 *
 * @author Sergey Zvyagintsev <nitron.ru@gmail.com>
 */
class StringUtils
{
    public static function isUrl(string $url): bool
    {
        $url = filter_var($url, \FILTER_VALIDATE_URL, \FILTER_FLAG_PATH_REQUIRED);
        $urlDetails = parse_url($url);

        return isset($urlDetails['scheme']) && \in_array($urlDetails['scheme'], ['http', 'https'], true);
    }
}
