<?php

/*
 * This file is part of the Glavweb UploaderBundle package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\UploaderBundle\Manager;

use Glavweb\UploaderBundle\Driver\AnnotationDriver;
use Glavweb\UploaderBundle\Exception\CropImageException;
use Glavweb\UploaderBundle\Exception\ProviderNotFoundException;
use Glavweb\UploaderBundle\File\FileInterface;
use Glavweb\UploaderBundle\File\FileMetadata;
use Glavweb\UploaderBundle\Model\MediaInterface;
use Glavweb\UploaderBundle\Model\ModelManagerInterface;
use Glavweb\UploaderBundle\Naming\NamerInterface;
use Glavweb\UploaderBundle\Provider\ImageProvider;
use Glavweb\UploaderBundle\Provider\ProviderFileInterface;
use Glavweb\UploaderBundle\Provider\ProviderInterface;
use Glavweb\UploaderBundle\Provider\ProviderTypes;
use Glavweb\UploaderBundle\Util\FileUtils;
use Glavweb\UploaderBundle\Storage\StorageInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class UploaderManager
 *
 * @package Glavweb\UploaderBundle\Manager
 * @author  Andrey Nilov <nilov@glavweb.ru>
 */
class UploaderManager implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var ModelManagerInterface
     */
    protected $modelManager;

    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @var array
     */
    private $providers = null;

    /**
     * @var array
     */
    private $blackListExtensions = [
        'php'
    ];

    /**
     * @var AnnotationDriver
     */
    private $driverAnnotation;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var array
     */
    private $config;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config     = $config;
        $this->filesystem = new Filesystem();
    }

    /**
     * @return StorageInterface
     */
    public function getStorage()
    {
        if (!$this->storage) {
            $this->storage = $this->container->get($this->config['storage']);
        }

        return $this->storage;
    }

    /**
     * @return AnnotationDriver
     */
    public function getDriverAnnotation()
    {
        if (!$this->driverAnnotation) {
            $this->driverAnnotation = $this->container->get('glavweb_uploader.data_driver.annotation');
        }

        return $this->driverAnnotation;
    }

    /**
     * @return RequestStack
     */
    public function getRequestStack()
    {
        if (!$this->requestStack) {
            $this->requestStack = $this->container->get('request_stack');
        }

        return $this->requestStack;
    }

    /**
     * @return ModelManagerInterface
     */
    public function getModelManager()
    {
        if (!$this->modelManager) {
            $this->modelManager = $this->container->get($this->config['model_manager']);
        }

        return $this->modelManager;
    }

    /**
     * Upload file
     *
     * @param string|FileInterface $link
     * @param string               $context
     * @param string               $requestId
     * @return array
     * @throws ProviderNotFoundException
     */
    public function upload($link, $context, $requestId)
    {
        $this->providers = null;
        $provider        = $this->getProvider($context, $link);

        if (!$provider) {
            throw new ProviderNotFoundException('Provider not found.');
        }

        $useOrphanage  = $this->getContextConfig($context, 'use_orphanage');
        $thumbnailPath = null;
        $contentPath   = null;
        $name          = $provider->getName();
        $uploadedFile  = null;

        if ($provider instanceof ProviderFileInterface) {
            $uploadedFile = $this->uploadFile($provider->getFile(), $context);

            $contentPath = FileUtils::basename($uploadedFile->getPathname());
            if ($provider instanceof ImageProvider) {
                $thumbnailPath = $contentPath;
            }

            if ($link !== $provider->getFile()) {
                $name = $uploadedFile->getClientOriginalName();
            }
        }

        if (!$thumbnailPath) {
            $thumbnailUrl = $provider->getThumbnailUrl();
            if ($thumbnailUrl) {
                $tmpFile      = $this->getStorage()->uploadTmpFileByLink($thumbnailUrl);
                $uploadedFile = $this->uploadFile($tmpFile, $context);

                $thumbnailPath = FileUtils::basename($uploadedFile->getPathname());
            }
        }

        $media = $this->getModelManager()->createMedia();
        $media->setContext($context);
        $media->setProviderName($provider->getProviderName());
        $media->setProviderReference($provider->getProviderReference());
        $media->setContentPath($contentPath);
        $media->setThumbnailPath($thumbnailPath);
        $media->setName($name);
        $media->setDescription($provider->getDescription());
        $media->setWidth($provider->getWidth());
        $media->setHeight($provider->getHeight());
        $media->setContentType($provider->getContentType());
        $media->setContentSize($provider->getContentSize());
        $media->setIsOrphan($useOrphanage);
        $media->setRequestId($requestId);
        $this->getModelManager()->updateMedia($media);

        return [
            'uploadedFile' => $uploadedFile,
            'media'        => $media
        ];
    }

    /**
     * Upload file
     *
     * @param FileInterface $file
     * @param string        $context
     * @return FileInterface
     */
    public function uploadFile(FileInterface $file, $context)
    {
        $directory = $this->getContextConfig($context, 'upload_directory');

        // Upload file
        /** @var NamerInterface $namer */
        $namer = $this->container->get($this->getContextConfig($context, 'namer'));
        $name  = $namer->name($file);

        $this->checkHackingName($name);

        return $this->getStorage()->upload($file, $directory, $name);
    }

    /**
     * @param string               $context
     * @param string|FileInterface $link
     * @return ProviderInterface|null
     * @throws \RuntimeException
     */
    public function getProvider($context, $link)
    {
        if (\is_string($link)) {
            $providers = $this->getProviders($context, ProviderTypes::LINK);
            foreach ($providers as $provider) {
                if ($provider->checkLink($link)) {
                    $provider->parse($link);

                    return $provider;
                }
            }

            $link = $this->getStorage()->uploadTmpFileByLink($link);
        }

        if ($link instanceof FileInterface) {
            $providers = $this->getProviders($context, ProviderTypes::FILE);
            foreach ($providers as $provider) {
                if ($provider->checkLink($link)) {
                    $provider->parse($link);

                    return $provider;
                }
            }
        }

        return null;
    }

    /**
     * @param string $context
     * @param string $type
     * @return array
     * @throws \RuntimeException
     */
    protected function getProviders($context, $type = null)
    {
        if ($this->providers === null) {
            $providers = [];
            foreach ($this->getContextConfig($context, 'providers') as $providerName) {
                $provider = $this->container->get($providerName);
                if (!$provider instanceof ProviderInterface) {
                    throw new \RuntimeException('Class "' . \get_class($provider) . '" is not provider.');
                }

                $providers[$provider->getProviderType()][] = $provider;
            }

            $this->providers = $providers;
        }

        if ($type) {
            return isset($this->providers[$type]) ? $this->providers[$type] : [];
        }

        return $this->providers;
    }

    /**
     * @param string $name
     * @return ProviderInterface
     * @throws \RuntimeException
     */
    public function getProviderByName($name)
    {
        $provider = null;
        if ($this->container->has($name)) {
            $provider = $this->container->get($name);
        }

        if (!$provider instanceof ProviderInterface) {
            throw new \RuntimeException('Provider not found.');
        }

        return $provider;
    }

    /**
     * @param string $requestId
     * @return MediaInterface[] Array of uploaded media entities
     */
    public function handleUpload($requestId)
    {
        $this->removeMarkedMedia($requestId);
        $this->renameMarkedMedia($requestId);

        return $this->uploadOrphans($requestId);
    }

    /**
     * @param MediaInterface $media
     * @return MediaInterface
     */
    public function copyMedia(MediaInterface $media): MediaInterface
    {
        $directory     = $this->getContextConfig($media->getContext(), 'upload_directory');
        $name          = $media->getContentPath();
        $thumbnailPath = $media->getThumbnailPath();

        $file     = $this->getStorage()->getFile($directory, $name);
        $fileCopy = $file->copy();
        $copyName = $fileCopy->getBasename();

        $thumbnailPath = $thumbnailPath === $name ? $copyName : $thumbnailPath;

        $mediaCopy = (clone $media)
            ->setContentPath($copyName)
            ->setThumbnailPath($thumbnailPath)
            ->setToken(null)
            ->setRequestId(null)
            ->setIsOrphan(false);

        $this->getModelManager()->updateMedia($mediaCopy);

        return $mediaCopy;
    }

    /**
     * @param MediaInterface[] $medias    Array of medias
     * @param array            $positions Array of positions medias like as [mediaId, mediaId,  ...]
     */
    public function sortMedias(array $medias, array $positions)
    {
        $this->getModelManager()->sortMedias($medias, $positions);
    }

    /**
     * @param string $requestId
     * @return array
     */
    public function uploadOrphans($requestId)
    {
        $medias = $this->getModelManager()->findOrphans($requestId);

        // update file models
        $uploadMedias = [];
        foreach ($medias as $media) {
            $media->setIsOrphan(false);
            $media->setRequestId(null);

            $uploadMedias[] = $media;
        }

        return $uploadMedias;
    }

    /**
     * @param string $requestId
     */
    public function removeMarkedMedia($requestId)
    {
        $this->getModelManager()->removeMarkedMedia($requestId);
    }

    /**
     * @param string $requestId
     */
    public function renameMarkedMedia($requestId)
    {
        $this->getModelManager()->renameMarkedMedia($requestId);
    }

    /**
     * Clear orphanage
     */
    public function clearOrphanage()
    {
        $this->getModelManager()->removeOrphans($this->config['orphanage']['lifetime']);

        $this->getStorage()->cleanup();
    }

    /**
     * @param FileInterface $file
     * @return void
     */
    public function removeFileFromStorage(FileInterface $file): void
    {
        $storage = $this->getStorage();

        $storage->removeFile($file);
    }

    /**
     * @param MediaInterface $media
     */
    public function removeMediaFromStorage(MediaInterface $media)
    {
        $storage = $this->getStorage();
        $context = $media->getContext();

        $directory = $this->getContextConfig($context, 'upload_directory');

        $files = [];
        if (($contentPath = $media->getContentPath()) && $storage->isFile($directory, $contentPath)) {
            $files[] = $storage->getFile($directory, $contentPath);
        }

        if (($thumbnailPath = $media->getThumbnailPath()) && $thumbnailPath !== $contentPath
            && $storage->isFile(
                $directory,
                $contentPath
            )) {
            $files[] = $storage->getFile($directory, $thumbnailPath);
        }

        foreach ($files as $file) {
            $storage->removeFile($file);
        }
    }

    /**
     * @param string $context
     * @param string $option
     * @return mixed
     * @throws \RuntimeException
     */
    public function getContextConfig($context, $option = null)
    {
        if (!isset($this->config['mappings'][$context])) {
            throw new \RuntimeException('Context "' . $context . '" not defined.');
        }

        $contextConfig = $this->config['mappings'][$context];

        if ($option) {
            return $contextConfig[$option];
        }

        return $contextConfig;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param $name
     */
    protected function checkHackingName($name)
    {
        $pathinfo  = pathinfo($name);
        $extension = isset($pathinfo['extension']) ? $pathinfo['extension'] : null;

        if (!$extension) {
            throw new \RuntimeException('Extension not found.');
        }

        if (\in_array($extension, $this->blackListExtensions, true)) {
            throw new \RuntimeException('Extension "' . $extension . '" not supported.');
        }
    }

    /**
     * @param MediaInterface $media
     * @param array          $cropData
     * @throws CropImageException
     */
    public function cropImage(MediaInterface $media, array $cropData): void
    {
        $storage     = $this->getStorage();
        $context     = $media->getContext();
        $contentPath = $media->getContentPath();
        $directory   = $this->getContextConfig($context, 'upload_directory');

        if ($media->getProviderName() !== 'glavweb_uploader.provider.image') {
            throw new CropImageException('The provider name must be "glavweb_uploader.provider.image".');
        }

        if (!$storage->isFile($directory, $contentPath)) {
            throw new CropImageException('File is not found.');
        }

        $file = $storage->getFile($directory, $contentPath);

        // update file name
        $newFilename = $this->getStorage()->cropImage($file, $cropData);
        $newContentPath = FileUtils::basename($newFilename);
        $media->setContentPath($newContentPath);
        $media->setThumbnailPath($newContentPath);

        $this->getModelManager()->updateMedia($media);
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function isChunkUpload(Request $request): bool
    {
        return (bool)$request->get($this->config['chunk_upload']['total_count_request_parameter']);
    }

    /**
     * @param Request $request
     * @param File    $file
     * @return FileInterface|null
     * @throws \Exception
     */
    public function handleChunkUpload(Request $request, File $file): ?FileInterface
    {
        $config     = $this->config['chunk_upload'];
        $fileId     = $request->get($config['file_id_request_parameter']);
        $chunkIndex = $request->get($config['current_index_request_parameter']);
        $chunkTotal = $request->get($config['total_count_request_parameter']);

        $this->getStorage()->addFileChunk($file, $fileId, $chunkIndex);

        if ($this->getStorage()->hasAllFileChunks($fileId, $chunkTotal)) {
            $imageWidth  = $request->get($config['image_width_request_parameter']);
            $imageHeight = $request->get($config['image_height_request_parameter']);
            $mimeType    = $request->get($config['type_request_parameter']);

            $metadata               = new FileMetadata();
            $metadata->originalName = $file instanceof UploadedFile ? $file->getClientOriginalName() : null;
            $metadata->mimeType     = $mimeType;
            $metadata->width        = $imageWidth ? (int)$imageWidth : 0;
            $metadata->height       = $imageHeight ? (int)$imageHeight : 0;
            $metadata->isImage      = str_starts_with($mimeType, 'image');

            return $this->getStorage()->concatFileChunks($file, $metadata, $fileId);
        }

        return null;
    }
}
