<?php

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Glavweb\UploaderBundle\GlavwebUploaderBundle;
use Liip\ImagineBundle\LiipImagineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;

return [
    FrameworkBundle::class => ['all' => true],
    LiipImagineBundle::class => ['all' => true],
    DoctrineBundle::class => ['all' => true],
    GlavwebUploaderBundle::class => ['all' => true],
];
