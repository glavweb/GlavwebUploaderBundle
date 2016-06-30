<?php

namespace Glavweb\UploaderBundle\Manager;

use Glavweb\UploaderBundle\Exception\ProviderNotFoundException;
use Glavweb\UploaderBundle\Exception\RequestEmptyException;
use Glavweb\UploaderBundle\File\FileInterface;
use Glavweb\UploaderBundle\Model\MediaInterface;
use Glavweb\UploaderBundle\Provider\ProviderFileInterface;
use Glavweb\UploaderBundle\Provider\ProviderInterface;
use Glavweb\UploaderBundle\Provider\ProviderTypes;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class UploaderManager
 * @package Glavweb\UploaderBundle\Manager
 */
class UploaderManager implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var \Glavweb\UploaderBundle\Model\ModelManagerInterface
     */
    protected $modelManager;

    /**
     * @var \Glavweb\UploaderBundle\Storage\StorageInterface
     */
    protected $storage;

    /**
     * @var array
     */
    private $providers = null;

    /**
     * @var array
     */
    private $blackListExtensions = array(
        'php'
    );

    /**
     * @var \Metadata\Driver\DriverInterface
     */
    private $driverAnnotation;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return \Glavweb\UploaderBundle\Storage\StorageInterface
     */
    public function getStorage()
    {
        if (!$this->storage) {
            $this->storage = $this->container->get($this->config['storage']);
        }

        return $this->storage;
    }

    /**
     * @return \Glavweb\UploaderBundle\Driver\AnnotationDriver
     */
    public function getDriverAnnotation()
    {
        if (!$this->driverAnnotation) {
            $this->driverAnnotation = $this->container->get('glavweb_uploader.data_driver.annotation');
        }

        return $this->driverAnnotation;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RequestStack
     */
    public function getRequestStack()
    {
        if (!$this->requestStack) {
            $this->requestStack = $this->container->get('request_stack');
        }

        return $this->requestStack;
    }

    /**
     * @return \Glavweb\UploaderBundle\Model\ModelManagerInterface
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
     */
    public function upload($link, $context, $requestId = null)
    {
        $provider = $this->getProvider($context, $link);

        if (!$provider) {
            throw new ProviderNotFoundException('Provider not found.');
        }

        $useOrphanage      = $this->getContextConfig($context, 'use_orphanage');
        $thumbnailPath     = null;
        $contentPath       = null;
        $name              = $provider->getName();

        if ($provider instanceof ProviderFileInterface) {
            $uploadedFile = $this->uploadFile($provider->getFile(), $context);

            $contentPath = basename($uploadedFile->getPathname());
            if (@getimagesize($uploadedFile->getPathname())) {
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

                $thumbnailPath = basename($uploadedFile->getPathname());
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
        $this->getModelManager()->updateMedia($media, true);

        return array(
            'uploadedFile' => $uploadedFile,
            'media'        => $media
        );
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
        $namer = $this->container->get($this->getContextConfig($context, 'namer'));
        $name  = $namer->name($file);

        $this->checkHackingName($name);

        $uploadedFile = $this->getStorage()->upload($file, $directory, $name);

        return $uploadedFile;
    }

    /**
     * @param string               $context
     * @param string|FileInterface $link
     * @return ProviderInterface|null
     * @throws \RuntimeException
     */
    public function getProvider($context, $link)
    {
        if (is_string($link)) {
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
            $providers = array();
            foreach ($this->getContextConfig($context, 'providers') as $providerName) {
                $provider = $this->container->get($providerName);
                if (!$provider instanceof ProviderInterface) {
                    throw new \RuntimeException('Class "' . get_class($provider) . '" is not provider.');
                }

                $providers[$provider->getProviderType()][] = $provider;
            }

            $this->providers = $providers;
        }

        if ($type) {
            return isset($this->providers[$type]) ? $this->providers[$type] : array();
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
     * @param $entity
     * @param null $requestId
     * @param array $options
     * @throws RequestEmptyException
     * @throws \Glavweb\UploaderBundle\Exception\RequestEmptyException
     */
    public function handleUpload($entity, $requestId=null, $options = array())
    {
        $request = $this->getRequestStack()->getCurrentRequest();
        $requestId = $request->get('_glavweb_uploader_request_id');

        if (!$requestId) {
            throw new RequestEmptyException();
        }

        $driverAnnotation = $this->getDriverAnnotation();
        $data = $driverAnnotation->loadDataForClass(new \ReflectionClass($entity));

        foreach ($data as $property) {
            $context = $property['mapping'];
            $mediaEntities = $entity->$property['nameGetFunction']();

            $this->removeMarkedMedia($context, $requestId);
            $this->renameMarkedMedia($context, $requestId);
            $uploadedMediaEntities = $this->uploadOrphans($context, $requestId);

            $this->addMediaEntities($uploadedMediaEntities, $entity, $property );

            $positions = explode(',', $request->get('_glavweb_uploader_sorted_array')[$context]);

            $this->sortMedia($mediaEntities, $positions);
        }
    }

    /**
     * @param $mediaEntities
     * @param $entity
     * @param $property
     */
    private function addMediaEntities($mediaEntities, $entity, $property)
    {
        $nameAddFunction = $property['nameAddFunction'];
        $nameGetFunction = $property['nameGetFunction'];
        $mapping         = $property['mapping'];

        $contextConfig = $this->config['mappings'][$mapping];

        $entityMedia = $entity->$nameGetFunction();

        $maxFiles = $contextConfig['max_files'] - $entityMedia->count();

        if (!empty($mediaEntities)) {
            foreach ($mediaEntities as $mediaEntity) {
                if ($maxFiles == 0) {
                    break;
                }
                $entity->$nameAddFunction($mediaEntity);
                $maxFiles--;
            }
        }
    }

    /**
     * @param $entities
     * @param $positions
     */
    public function sortMedia($entities, $positions)
    {
        $this->getModelManager()->sortMedia($entities, $positions);
    }

    /**
     * @param string $context
     * @param string $requestId
     * @return array
     */
    public function uploadOrphans($context, $requestId)
    {
        $medias = $this->getModelManager()->findOrphans($context, $requestId);

        // update file models
        $uploadMedias = array();
        foreach ($medias as $media) {
            $media->setIsOrphan(false);
            $media->setRequestId(null);

            $uploadMedias[] = $media;
        }

        return $uploadMedias;
    }

    /**
     * @param string $context
     * @param string $requestId
     */
    public function removeMarkedMedia($context, $requestId)
    {
        $this->getModelManager()->removeMarkedMedia($context, $requestId);
    }

    /**
     * @param string $context
     * @param string $requestId
     */
    public function renameMarkedMedia($context, $requestId)
    {
        $this->getModelManager()->renameMarkedMedia($context, $requestId);
    }

    /**
     * Clear orphanage
     */
    public function clearOrphanage()
    {
        $this->getModelManager()->removeOrphans($this->config['orphanage']['lifetime']);
    }

    /**
     * @param MediaInterface $media
     */
    public function removeMediaFromStorage(MediaInterface $media)
    {
        $storage  = $this->getStorage();
        $context  = $media->getContext();

        $directory = $this->getContextConfig($context, 'upload_directory');

        $files = array();
        if (($contentPath = $media->getContentPath())) {
            $files[] = $storage->getFile($directory, $contentPath);
        }

        if (($thumbnailPath = $media->getThumbnailPath()) && $thumbnailPath != $contentPath) {
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
    protected function getContextConfig($context, $option = null)
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
     * @param $name
     */
    protected function checkHackingName($name)
    {
        $pathinfo = pathinfo($name);
        $extension = isset($pathinfo['extension']) ? $pathinfo['extension'] : null;

        if (!$extension) {
            throw new \RuntimeException('Extension not found.');
        }

        if (in_array($extension, $this->blackListExtensions)) {
            throw new \RuntimeException('Extension "' . $extension . '" not supported.');
        }
    }
}
