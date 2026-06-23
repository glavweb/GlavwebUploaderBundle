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

use Glavweb\UploaderBundle\Driver\AttributeDriver;
use Glavweb\UploaderBundle\Entity\Media;
use Glavweb\UploaderBundle\Exception\CropImageException;
use Glavweb\UploaderBundle\Exception\ProviderNotFoundException;
use Glavweb\UploaderBundle\File\FileInterface;
use Glavweb\UploaderBundle\Model\MediaInterface;
use Glavweb\UploaderBundle\Model\ModelManagerInterface;
use Glavweb\UploaderBundle\Provider\ImageProvider;
use Glavweb\UploaderBundle\Provider\ProviderFileInterface;
use Glavweb\UploaderBundle\Provider\ProviderInterface;
use Glavweb\UploaderBundle\Provider\ProviderTypes;
use Glavweb\UploaderBundle\Storage\StorageInterface;
use Glavweb\UploaderBundle\Util\FileUtils;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class UploaderManager.
 *
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class UploaderManager
{
    protected ?ModelManagerInterface $modelManager = null;

    protected ?StorageInterface $storage = null;

    private ?array $providers = null;

    /**
     * @var string[]
     */
    private array $blackListExtensions = [
        'php',
    ];

    private ?AttributeDriver $driverAnnotation = null;

    private ?RequestStack $requestStack = null;

    private readonly Filesystem $filesystem;

    public function __construct(
        private array $config,
        private readonly ContainerInterface $container,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
        $this->filesystem = new Filesystem();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getStorage(): StorageInterface
    {
        if (!$this->storage instanceof StorageInterface) {
            $this->storage = $this->container->get($this->config['storage']);
        }

        return $this->storage;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getDriverAnnotation(): AttributeDriver
    {
        if (!$this->driverAnnotation instanceof AttributeDriver) {
            $this->driverAnnotation = $this->container->get('glavweb_uploader.data_driver.attribute');
        }

        return $this->driverAnnotation;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getRequestStack(): RequestStack
    {
        if (!$this->requestStack instanceof RequestStack) {
            $this->requestStack = $this->container->get('request_stack');
        }

        return $this->requestStack;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getModelManager(): ModelManagerInterface
    {
        if (!$this->modelManager instanceof ModelManagerInterface) {
            $this->modelManager = $this->container->get($this->config['model_manager']);
        }

        return $this->modelManager;
    }

    /**
     * Upload file.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ProviderNotFoundException
     */
    public function upload(FileInterface|string $link, string $context, string $requestId): array
    {
        $this->providers = null;
        $provider = $this->getProvider($context, $link);

        if (!$provider instanceof ProviderInterface) {
            throw new ProviderNotFoundException('Provider not found.');
        }

        $useOrphanage = $this->getContextConfig($context, 'use_orphanage');
        $thumbnailPath = null;
        $contentPath = null;
        $name = $provider->getName();
        $uploadedFile = null;

        if ($provider instanceof ProviderFileInterface) {
            $uploadedFile = $this->uploadFile($provider->getFile(), $context);

            $contentPath = FileUtils::basename($uploadedFile->getPathname());
            if ($provider instanceof ImageProvider) {
                $thumbnailPath = $contentPath;
            }

            if ($link != $provider->getFile()) {
                $name = $uploadedFile->getClientOriginalName();
            }
        }

        if (!$thumbnailPath) {
            $thumbnailUrl = $provider->getThumbnailUrl();
            if ($thumbnailUrl) {
                $tmpFile = $this->getStorage()->uploadTmpFileByLink($thumbnailUrl);
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
            'media' => $media,
        ];
    }

    /**
     * Upload file.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function uploadFile(FileInterface $file, string $context): FileInterface
    {
        $directory = $this->getContextConfig($context, 'upload_directory');

        // Upload file
        $namer = $this->container->get($this->getContextConfig($context, 'namer'));
        $name = $namer->name($file);

        $this->checkHackingName($name);

        return $this->getStorage()->upload($file, $directory, $name);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getProvider(string $context, FileInterface|string $link): ?ProviderInterface
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

        $providers = $this->getProviders($context, ProviderTypes::FILE);
        foreach ($providers as $provider) {
            if ($provider->checkLink($link)) {
                $provider->parse($link);

                return $provider;
            }
        }

        return null;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getProviders(string $context, ?string $type = null): array
    {
        if (null === $this->providers) {
            $providers = [];
            foreach ($this->getContextConfig($context, 'providers') as $providerName) {
                $provider = $this->container->get($providerName);
                if (!$provider instanceof ProviderInterface) {
                    throw new \RuntimeException('Class "'.$provider::class.'" is not provider.');
                }

                $providers[$provider->getProviderType()][] = $provider;
            }

            $this->providers = $providers;
        }

        if ($type) {
            return $this->providers[$type] ?? [];
        }

        return $this->providers;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ProviderNotFoundException
     */
    public function getProviderByName(string $name): ProviderInterface
    {
        $provider = null;
        if ($this->container->has($name)) {
            $provider = $this->container->get($name);
        }

        if (!$provider instanceof ProviderInterface) {
            throw new ProviderNotFoundException('Provider not found.');
        }

        return $provider;
    }

    /**
     * @return MediaInterface[] Array of uploaded media entities
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function handleUpload(string $requestId): array
    {
        $this->eventDispatcher->addListener(KernelEvents::TERMINATE, fn () => $this->removeMarkedMedia($requestId));
        $this->renameMarkedMedia($requestId);

        return $this->uploadOrphans($requestId);
    }

    /**
     * @param MediaInterface[] $medias    Array of medias
     * @param array            $positions Array of positions medias like as [mediaId, mediaId,  ...]
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function sortMedias(array $medias, array $positions): void
    {
        $this->getModelManager()->sortMedias($medias, $positions);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function uploadOrphans(string $requestId): array
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function removeMarkedMedia(string $requestId): void
    {
        $this->getModelManager()->removeMarkedMedia($requestId);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function renameMarkedMedia(string $requestId): void
    {
        $this->getModelManager()->renameMarkedMedia($requestId);
    }

    /**
     * Clear orphanage.
     */
    public function clearOrphanage(): void
    {
        $this->getModelManager()->removeOrphans($this->config['orphanage']['lifetime']);

        $chunkedUploadDirectoryPath = $this->getChunkedUploadDirectoryPath();

        if (!is_dir($chunkedUploadDirectoryPath)) {
            return;
        }

        $oldFilesFinder = new Finder();
        $oldFilesFinder->in($chunkedUploadDirectoryPath)
                       ->files()
                       ->date('before 1 hour ago');

        $this->filesystem->remove(iterator_to_array($oldFilesFinder));

        $emptyDirFinder = new Finder();
        $emptyDirFinder->in($chunkedUploadDirectoryPath)
            ->directories()
            ->filter(static function (\SplFileInfo $dirInfo): bool {
                $dirFinder = new Finder();
                $dirFinder->in($dirInfo->getRealPath())->files();

                return !$dirFinder->count();
            });

        $this->filesystem->remove(iterator_to_array($emptyDirFinder));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function removeMediaFromStorage(MediaInterface $media): void
    {
        $storage = $this->getStorage();
        $context = $media->getContext();

        $directory = $this->getContextConfig($context, 'upload_directory');

        $files = [];
        if (($contentPath = $media->getContentPath()) && $storage->isFile($directory, $contentPath)) {
            $files[] = $storage->getFile($directory, $contentPath);
        }

        if ($thumbnailPath = $media->getThumbnailPath() && $thumbnailPath != $contentPath && $storage->isFile($directory, $contentPath)) {
            $files[] = $storage->getFile($directory, $thumbnailPath);
        }

        foreach ($files as $file) {
            $storage->removeFile($file);
        }
    }

    /**
     * @throws \RuntimeException
     */
    public function getContextConfig(string $context, ?string $option = null)
    {
        if (!isset($this->config['mappings'][$context])) {
            throw new \RuntimeException('Context "'.$context.'" not defined.');
        }

        $contextConfig = $this->config['mappings'][$context];

        if ($option) {
            if (!isset($contextConfig[$option])) {
                throw new \RuntimeException('Context "'.$context.'" option "'.$option.'" not defined.');
            }

            return $contextConfig[$option];
        }

        return $contextConfig;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    protected function checkHackingName($name): void
    {
        $pathinfo = pathinfo((string) $name);
        $extension = $pathinfo['extension'] ?? null;

        if (!$extension) {
            throw new \RuntimeException('Extension not found.');
        }

        if (\in_array($extension, $this->blackListExtensions)) {
            throw new \RuntimeException('Extension "'.$extension.'" not supported.');
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws CropImageException
     * @throws NotFoundExceptionInterface
     */
    public function cropImage(Media $media, array $cropData): void
    {
        $storage = $this->getStorage();
        $context = $media->getContext();
        $contentPath = $media->getContentPath();
        $directory = $this->getContextConfig($context, 'upload_directory');

        if ('glavweb_uploader.provider.image' !== $media->getProviderName()) {
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

    public function isChunkUpload(Request $request): bool
    {
        return (bool) $request->getPayload()->get($this->config['chunk_upload']['total_count_request_parameter']);
    }

    /**
     * @throws \Exception
     */
    public function handleChunkUpload(Request $request, File $file): ?File
    {
        $config = $this->config['chunk_upload'];
        $payload = $request->getPayload();
        $fileId = $payload->get($config['file_id_request_parameter']);
        $chunkIndex = $payload->get($config['current_index_request_parameter']);
        $chunkTotal = $payload->get($config['total_count_request_parameter']);

        $this->addFileChunk($file, $fileId, $chunkIndex);

        if ($this->hasAllFileChunks($fileId, $chunkTotal)) {
            return $this->concatFileChunks($fileId);
        }

        return null;
    }

    public function addFileChunk(File $file, string $fileId, int $chunkIndex): void
    {
        $chunksDirectoryPath = $this->getChunksDirectoryPath($fileId);
        $targetPath = $chunksDirectoryPath.\DIRECTORY_SEPARATOR.$chunkIndex;

        $this->filesystem->mkdir($chunksDirectoryPath);
        $this->filesystem->rename($file->getRealPath(), $targetPath);
    }

    public function hasAllFileChunks(string $fileId, int $chunkTotal): bool
    {
        $finder = new Finder();

        $chunksDirectoryPath = $this->getChunksDirectoryPath($fileId);

        $finder->in($chunksDirectoryPath)->files();

        if ($finder->count() === $chunkTotal) {
            foreach ($finder as $chunk) {
                if (!is_readable($chunk->getRealPath())) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @throws \Exception
     */
    public function concatFileChunks(string $fileId): File
    {
        $finder = new Finder();

        $chunksDirectoryPath = $this->getChunksDirectoryPath($fileId);
        $fileDirectoryPath = $this->getConcatenatedFileDirectoryPath();
        $filePath = $fileDirectoryPath.\DIRECTORY_SEPARATOR.$fileId;

        $finder->in($chunksDirectoryPath)->files()->sortByName(true);

        $this->filesystem->mkdir($fileDirectoryPath);

        try {
            $target = fopen($filePath, 'a');

            foreach ($finder as $chunk) {
                try {
                    $source = fopen($chunk->getRealPath(), 'r');
                    stream_copy_to_stream($source, $target);

                    $this->filesystem->remove($chunk->getRealPath());
                } finally {
                    if (isset($source) && \is_resource($source)) {
                        fclose($source);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->filesystem->remove($filePath);

            throw $e;
        } finally {
            if (isset($target) && \is_resource($target)) {
                fclose($target);
            }

            $this->filesystem->remove($chunksDirectoryPath);
        }

        return new File($filePath);
    }

    private function getConcatenatedFileDirectoryPath(): string
    {
        return $this->getChunkedUploadDirectoryPath().\DIRECTORY_SEPARATOR.'files';
    }

    private function getChunksDirectoryPath(string $fileId): string
    {
        $ds = \DIRECTORY_SEPARATOR;

        return $this->getChunkedUploadDirectoryPath().$ds.'chunks'.$ds.$fileId;
    }

    private function getChunkedUploadDirectoryPath(): string
    {
        return $this->config['temp_directory'].\DIRECTORY_SEPARATOR.'chunked-upload';
    }
}
