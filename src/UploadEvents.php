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
 * Class UploadEvents.
 *
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
final class UploadEvents
{
    public const string PRE_UPLOAD = 'glavweb_uploader.pre_upload';

    public const string POST_UPLOAD = 'glavweb_uploader.post_upload';

    public const string POST_PERSIST = 'glavweb_uploader.post_persist';

    public const string VALIDATION = 'glavweb_uploader.validation';
}
