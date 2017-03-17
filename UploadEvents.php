<?php

/*
 * This file is part of the Glavweb UploaderBundle package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\UploaderBundle;

/**
 * Class UploadEvents
 *
 * @package Glavweb\UploaderBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
final class UploadEvents
{
    const PRE_UPLOAD   = 'glavweb_uploader.pre_upload';
    const POST_UPLOAD  = 'glavweb_uploader.post_upload';
    const POST_PERSIST = 'glavweb_uploader.post_persist';
    const VALIDATION   = 'glavweb_uploader.validation';
}
