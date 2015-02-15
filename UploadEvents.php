<?php

namespace Glavweb\UploaderBundle;

final class UploadEvents
{
    const PRE_UPLOAD        = 'glavweb_uploader.pre_upload';
    const POST_UPLOAD       = 'glavweb_uploader.post_upload';
    const POST_PERSIST      = 'glavweb_uploader.post_persist';
    const VALIDATION        = 'glavweb_uploader.validation';
}
