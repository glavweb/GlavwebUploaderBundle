<?php

/*
 * This file is part of the Glavweb UploaderBundle package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\UploaderBundle\Twig\Extension;

use Glavweb\UploaderBundle\Helper\MediaHelper;
use Glavweb\UploaderBundle\Manager\UploaderManager;
use Glavweb\UploaderBundle\Model\MediaInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Class UploaderExtension.
 */
class UploaderExtension extends AbstractExtension
{
    protected ?Request $request;

    public function __construct(RequestStack $requestStack, protected MediaHelper $mediaHelper, protected UploaderManager $uploaderManager)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName(): string
    {
        return 'glavweb_uploader_extension';
    }

    /**
     * @return TwigFilter[]
     */
    #[\Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('glavweb_uploader_thumbnail', $this->thumbnail(...)),
            new TwigFilter('glavweb_uploader_content_path', $this->contentPath(...)),
        ];
    }

    /**
     * @return TwigFunction[]
     */
    #[\Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('glavweb_uploader_render_files', $this->renderFiles(...)),
            new TwigFunction('glavweb_uploader_display', $this->display(...), ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param MediaInterface[] $medias
     */
    public function renderFiles(array $medias): string
    {
        $request = $this->request;
        $medias = array_map(function (MediaInterface $media): array {
            $context = $media->getContext();

            return [
                'id' => $media->getId(),
                'path' => $this->mediaHelper->getUploadDirectoryUrl($context).'/'.$media->getThumbnailPath(),
                'name' => $media->getName(),
            ];
        }, $medias);

        $output = '';
        $baseUrl = $request->getScheme().'://'.$request->getHttpHost().$request->getBasePath();
        foreach ($medias as $media) {
            $output .= sprintf(
                ' <div><a class="uploaded-file" target="_blank" rel="group_uploaded_file" href="%s%s">%s</a></div>',
                $baseUrl,
                $media['path'],
                $media['name']
            );
        }

        return $output;
    }

    public function thumbnail(MediaInterface $media, bool $isAbsolute = false): ?string
    {
        $thumbnailPath = $media->getThumbnailPath();
        if ($thumbnailPath) {
            $context = $media->getContext();

            return $this->mediaHelper->getUploadDirectoryUrl($context, $isAbsolute).'/'.$thumbnailPath;
        }

        return null;
    }

    public function contentPath(MediaInterface $media, bool $isAbsolute = false): ?string
    {
        $contentPath = $media->getContentPath();
        if ($contentPath) {
            $context = $media->getContext();

            return $this->mediaHelper->getUploadDirectoryUrl($context, $isAbsolute).'/'.$contentPath;
        }

        return null;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function display(MediaInterface $media, array $options = []): string
    {
        $provider = $this->uploaderManager->getProviderByName($media->getProviderName());

        return $provider->display($media, $options);
    }
}
