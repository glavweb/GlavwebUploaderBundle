<?php


namespace Glavweb\UploaderBundle\Util;


/**
 * Class StringUtils
 *
 * @package Glavweb\UploaderBundle\Util
 *
 * @author Sergey Zvyagintsev <nitron.ru@gmail.com>
 */
class StringUtils
{

    /**
     * @param string $url
     * @return bool
     */
    public static function isUrl($url)
    {
        $url        = filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED);
        $urlDetails = parse_url($url);

        return isset($urlDetails['scheme']) && in_array($urlDetails['scheme'], array('http', 'https'));
    }
}