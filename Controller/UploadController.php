<?php

namespace Glavweb\UploaderBundle\Controller;

use Glavweb\UploaderBundle\Entity\MediaMarkRemove;
use Glavweb\UploaderBundle\Entity\MediaMarkRename;
use Glavweb\UploaderBundle\ErrorHandler\ErrorHandlerInterface;
use Glavweb\UploaderBundle\Event\PostPersistEvent;
use Glavweb\UploaderBundle\Event\PostUploadEvent;
use Glavweb\UploaderBundle\Event\PreUploadEvent;
use Glavweb\UploaderBundle\Event\ValidationEvent;
use Glavweb\UploaderBundle\Exception\ProviderNotFoundException;
use Glavweb\UploaderBundle\File\FileInterface;
use Glavweb\UploaderBundle\File\FilesystemFile;
use Glavweb\UploaderBundle\Model\MediaInterface;
use Glavweb\UploaderBundle\Response\Response as UploaderResponse;
use Glavweb\UploaderBundle\Response\ResponseInterface;
use Glavweb\UploaderBundle\UploadEvents;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UploadController
 * @package Glavweb\UploaderBundle\Controller
 */
class UploadController extends Controller
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var ErrorHandlerInterface
     */
    protected $errorHandler;

    /**
     * @param array                 $config
     * @param ErrorHandlerInterface $errorHandler
     */
    public function __construct(array $config, ErrorHandlerInterface $errorHandler)
    {
        $this->config       = $config;
        $this->errorHandler = $errorHandler;
    }

    /**
     * @param Request $request
     * @param string  $context
     * @return JsonResponse
     */
    public function uploadAction(Request $request, $context)
    {
        $response = new UploaderResponse();
        $files    = $this->getFiles($request->files);

        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
        foreach ($files as $file) {
            try {
                $this->handleUpload($file, $response, $request, $context);

            } catch (UploadException $e) {
                $this->errorHandler->addException($response, $e);

            } catch (ProviderNotFoundException $e) {
                $this->errorHandler->addException($response, $e);
            }
        }

        return $this->createSupportedJsonResponse($response->assemble(), $request);
    }

    /**
     * @param Request $request
     * @param string  $context
     */
    public function uploadLinkAction(Request $request, $context)
    {
        $response        = new UploaderResponse();
        $uploaderManager = $this->container->get('glavweb_uploader.uploader_manager');
        $link            = $request->get('file_link');
        $requestId       = $request->get('_glavweb_uploader_request_id');

        // extract $uploadedFile, $media
        /** @var Media $media */
        $uploadedFile = null;
        $media        = null;

        try {
            extract($uploaderManager->upload($link, $context, $requestId), EXTR_OVERWRITE);

            if ($media) {
                $response['id']                 = $media->getId();
                $response['name']               = $media->getName();
                $response['description']        = $media->getDescription();
                $response['thumbnail_path']     = $media->getThumbnailPath();
                $response['content_path']       = $media->getContentPath();
                $response['content_type']       = $media->getContentType();
                $response['content_size']       = $media->getContentSize();
                $response['width']              = $media->getWidth();
                $response['height']             = $media->getHeight();
                $response['provider_reference'] = $media->getProviderReference();
            }

        } catch (UploadException $e) {
            $this->errorHandler->addException($response, $e);

        } catch (ProviderNotFoundException $e) {
            $this->errorHandler->addException($response, $e);
        }

        return $this->createSupportedJsonResponse($response->assemble(), $request);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function progressAction(Request $request)
    {
        $session = $this->container->get('session');

        $prefix = ini_get('session.upload_progress.prefix');
        $name   = ini_get('session.upload_progress.name');

        // assemble session key
        // ref: http://php.net/manual/en/session.upload-progress.php
        $key = sprintf('%s.%s', $prefix, $request->get($name));
        $value = $session->get($key);

        return new JsonResponse($value);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function cancelAction(Request $request)
    {
        $session = $this->container->get('session');

        $prefix = ini_get('session.upload_progress.prefix');
        $name   = ini_get('session.upload_progress.name');

        $key = sprintf('%s.%s', $prefix, $request->get($name));

        $progress = $session->get($key);
        $progress['cancel_upload'] = false;

        $session->set($key, $progress);

        return new JsonResponse(true);
    }

    /**
     * Delete file
     */
    public function deleteAction(Request $request, $context)
    {
        $em = $this->getDoctrine()->getManager();
        $id        = $request->get('id');
        $requestId = $request->get('request_id');

        /** @var \Glavweb\UploaderBundle\Entity\Media $media */
        $media = $em->find('GlavwebUploaderBundle:Media', $id);

        $success = false;
        $isValid = $media !== null;
        if ($isValid) {
            if ($media->getIsOrphan()) {
                $isValid = $media->getRequestId() == $requestId;
                if ($isValid) {
                    $em->remove($media);
                    $em->flush();
                    $success = true;
                }
            } else {
                $media->setRequestId($requestId);

                $mediaMarkRemove = new MediaMarkRemove();
                $mediaMarkRemove->setRequestId($requestId);
                $mediaMarkRemove->setMedia($media);

                $em->persist($mediaMarkRemove);
                $em->flush();
                $success = true;
            }
        }

        return new JsonResponse(array('success' => $success));
    }

    /**
     * Rename file
     */
    public function renameAction(Request $request, $context)
    {
        $em = $this->getDoctrine()->getManager();
        $id             = $request->get('id');
        $requestId      = $request->get('request_id');
        $name           = $request->get('name');
        $description    = $request->get('description');

        /** @var \Glavweb\UploaderBundle\Entity\Media $media */
        $media = $em->find('GlavwebUploaderBundle:Media', $id);

        $success = false;
        $isValid = $media && $requestId && ($name || $description);

        if ($isValid) {
            if ($media->getIsOrphan()) {
                $isValid = $media->getRequestId() == $requestId;
                if ($isValid) {
                    $media->setName($name);
                    $media->setDescription($description);

                    $em->flush();
                    $success = true;
                }
            } else {
                $media->setRequestId($requestId);

                $mediaMarkRename = new MediaMarkRename();
                $mediaMarkRename->setRequestId($requestId);
                $mediaMarkRename->setNewName($name);
                $mediaMarkRename->setNewDescription($description);
                $mediaMarkRename->setMedia($media);

                $em->persist($mediaMarkRename);
                $em->flush();
                $success = true;
            }
        }

        return new JsonResponse(array('success' => $success));
    }

    /**
     *  Flattens a given filebag to extract all files.
     *
     *  @param FileBag $bag The filebag to use
     *  @return array An array of files
     */
    protected function getFiles(FileBag $bag)
    {
        $files = array();
        $fileBag = $bag->all();
        $fileIterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($fileBag), \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($fileIterator as $file) {
            if (is_array($file) || null === $file) {
                continue;
            }

            $files[] = $file;
        }

        return $files;
    }

    /**
     *  This internal function handles the actual upload process
     *  and will most likely be called from the upload()
     *  function in the implemented Controller.
     *
     *  Note: The return value differs when
     *
     * @param File              $file     The file to upload
     * @param ResponseInterface $response A response object.
     * @param Request           $request  The request object.
     * @param string            $context  The context
     */
    protected function handleUpload(File $file, ResponseInterface $response, Request $request, $context)
    {
        /** @var \Glavweb\UploaderBundle\Manager\UploaderManager $uploaderManager */
        $requestId       = $request->get('_glavweb_uploader_request_id');
        $uploaderManager = $this->container->get('glavweb_uploader.uploader_manager');

        if (!($file instanceof FileInterface)) {
            $file = new FilesystemFile($file);
        }

        // validate
        $this->validate($file, $request, $context);

        // pre upload dispatch
        $this->dispatchPreUploadEvent($file, $response, $request, $context);

        // extract $uploadedFile, $media
        $uploadedFile = null;
        $media        = null;
        extract($uploaderManager->upload($file, $context, $requestId), EXTR_OVERWRITE);

        if ($media) {
            $response['id']          = $media->getId();
            $response['contentPath'] = $this->get('glavweb_uploader.media_helper')->getContentPath($media);

        }

        // post upload dispatch
        $this->dispatchPostEvents($uploadedFile, $media, $response, $request, $context);
    }

    /**
     *  This function is a helper function which dispatches pre upload event
     *
     * @param FileInterface     $uploadedFile
     * @param ResponseInterface $response
     * @param Request           $request
     * @param string            $context
     */
    protected function dispatchPreUploadEvent(FileInterface $uploadedFile, ResponseInterface $response, Request $request, $context)
    {
        $configContext = $this->getConfigByContext($context);
        $dispatcher    = $this->container->get('event_dispatcher');

        // dispatch pre upload event (both the specific and the general)
        $event = new PreUploadEvent($uploadedFile, $response, $request, $context, $configContext);
        $dispatcher->dispatch(UploadEvents::PRE_UPLOAD, $event);
        $dispatcher->dispatch(sprintf('%s.%s', UploadEvents::PRE_UPLOAD, $context), $event);
    }

    /**
     *  This function is a helper function which dispatches post upload
     *  and post persist events.
     *
     * @param FileInterface      $uploadedFile
     * @param MediaInterface $media
     * @param ResponseInterface  $response
     * @param Request            $request
     * @param $context
     */
    protected function dispatchPostEvents(FileInterface $uploadedFile, MediaInterface $media, ResponseInterface $response, Request $request, $context)
    {
        $configContext = $this->getConfigByContext($context);
        $dispatcher    = $this->container->get('event_dispatcher');

        // dispatch post upload event (both the specific and the general)
        $event = new PostUploadEvent($uploadedFile, $media, $response, $request, $context, $configContext);
        $dispatcher->dispatch(UploadEvents::POST_UPLOAD, $event);
        $dispatcher->dispatch(sprintf('%s.%s', UploadEvents::POST_UPLOAD, $context), $event);
    }

    /**
     * @param FileInterface $file
     * @param Request $request
     * @param $context
     */
    protected function validate(FileInterface $file, Request $request, $context)
    {
        $configContext = $this->getConfigByContext($context);
        $dispatcher    = $this->container->get('event_dispatcher');

        // dispatch validation event (both the specific and the general)
        $event = new ValidationEvent($file, $request, $configContext, $context);
        $dispatcher->dispatch(UploadEvents::VALIDATION, $event);
        $dispatcher->dispatch(sprintf('%s.%s', UploadEvents::VALIDATION, $context), $event);
    }

    /**
     * Creates and returns a JsonResponse with the given data.
     *
     * On top of that, if the client does not support the application/json type,
     * then the content type of the response will be set to text/plain instead.
     *
     * @param mixed $data
     * @param Request $request
     * @param int $status
     * @return JsonResponse
     */
    protected function createSupportedJsonResponse($data, Request $request, $status = null)
    {
        if ($status === null) {
            $status = isset($data['error']) ? 400 : 200;
        }

        $response = new JsonResponse($data, $status);
        $response->headers->set('Vary', 'Accept');

        if (!in_array('application/json', $request->getAcceptableContentTypes())) {
            $response->headers->set('Content-type', 'text/plain');
        }

        return $response;
    }

    /**
     * @param string $context
     * @return array
     */
    protected function getConfigByContext($context)
    {
        return $this->config['mappings'][$context];
    }
}