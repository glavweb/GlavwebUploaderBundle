<?php

/*
 * This file is part of the Glavweb UploaderBundle package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\UploaderBundle\Controller;

use Glavweb\UploaderBundle\Entity\Media;
use Glavweb\UploaderBundle\ErrorHandler\ErrorHandlerInterface;
use Glavweb\UploaderBundle\Event\PostUploadEvent;
use Glavweb\UploaderBundle\Event\PreUploadEvent;
use Glavweb\UploaderBundle\Event\ValidationEvent;
use Glavweb\UploaderBundle\Exception\ProviderNotFoundException;
use Glavweb\UploaderBundle\Exception\RequestIdNotFoundException;
use Glavweb\UploaderBundle\File\FileInterface;
use Glavweb\UploaderBundle\File\FilesystemFile;
use Glavweb\UploaderBundle\Model\MediaInterface;
use Glavweb\UploaderBundle\Response\Response as UploaderResponse;
use Glavweb\UploaderBundle\Response\ResponseInterface;
use Glavweb\UploaderBundle\UploadEvents;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UploadController
 *
 * @package Glavweb\UploaderBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
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
     * @Route("/uploader/upload/{context}", name="glavweb_uploader_upload", methods={"POST"})
     *
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
                $this->doUpload($file, $response, $request, $context);

            } catch (UploadException $e) {
                $this->getErrorHandler()->addException($response, $e);

            } catch (ProviderNotFoundException $e) {
                $this->getErrorHandler()->addException($response, $e);
            }
        }

        return $this->createSupportedJsonResponse($response->assemble(), $request);
    }

    /**
     * @Route("/uploader/uploadlink/{context}", name="glavweb_uploader_uploadlink", methods={"POST"})
     *
     * @param Request $request
     * @param string $context
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function uploadLinkAction(Request $request, $context)
    {
        $response = new UploaderResponse();
        $link     = $request->get('file_link');

        try {
            $this->doUploadByLink($link, $response, $request, $context);

        } catch (UploadException $e) {
            $this->errorHandler->addException($response, $e);

        } catch (ProviderNotFoundException $e) {
            $this->errorHandler->addException($response, $e);
        }

        return $this->createSupportedJsonResponse($response->assemble(), $request);
    }

    /**
     * @Route("/uploader/progress", name="glavweb_uploader_progress")
     *
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
     * @Route("/uploader/cancel", name="glavweb_uploader_cancel")
     *
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
     *
     * @Route("/uploader/delete", name="glavweb_uploader_delete", methods={"POST"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAction(Request $request)
    {
        $success = false;

        $id        = $request->get('id');
        $requestId = $request->get('request_id');

        $modelManager = $this->get('glavweb_uploader.uploader_manager')->getModelManager();
        $media = $modelManager->findOneBySecuredId($id);

        if ($media && $requestId) {
            if ($media->getIsOrphan()) {
                if ($media->getRequestId() == $requestId) {
                    $success = $modelManager->removeMedia($media);
                }

            } else {
                $success = $modelManager->markRemove($media, $requestId);
            }
        }

        return new JsonResponse(['success' => $success], $success ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST);
    }

    /**
     * Rename file
     *
     * @Route("/uploader/edit", name="glavweb_uploader_edit", methods={"POST"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function editAction(Request $request)
    {
        $success = false;

        $id          = $request->get('id');
        $requestId   = $request->get('request_id');
        $name        = $request->get('name');
        $description = $request->get('description');

        $modelManager = $this->get('glavweb_uploader.uploader_manager')->getModelManager();
        $media = $modelManager->findOneBySecuredId($id);

        if ($media && $requestId && ($name || $description)) {
            if ($media->getIsOrphan()) {
                if ($media->getRequestId() == $requestId) {
                    $success = $modelManager->editMedia($media, $name, $description);
                }

            } else {
                $success = $modelManager->markEdit($media, $requestId, $name, $description);
            }
        }

        return new JsonResponse(['success' => $success], $success ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST);
    }

    /**
     *  Flattens a given filebag to extract all files.
     *
     *  @param FileBag $bag The filebag to use
     *  @return array An array of files
     */
    protected function getFiles(FileBag $bag)
    {
        $files   = [];
        $fileBag = $bag->all();

        $fileIterator = new \RecursiveIteratorIterator(
            new \RecursiveArrayIterator($fileBag),
            \RecursiveIteratorIterator::SELF_FIRST
        );

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
     * @throws ProviderNotFoundException
     * @throws RequestIdNotFoundException
     * @throws \Glavweb\UploaderBundle\Exception\Exception
     */
    protected function doUpload(File $file, ResponseInterface $response, Request $request, $context)
    {
        $uploaderManager = $this->container->get('glavweb_uploader.uploader_manager');
        $mediaStructure  = $this->get('glavweb_uploader.util.media_structure');

        $requestId       = $request->get('request_id');
        $thumbnailFilter = $request->get('thumbnail_filter');

        if (!$requestId) {
            throw new RequestIdNotFoundException('Request ID not found.');
        }

        if (!$file instanceof FileInterface) {
            $file = new FilesystemFile($file);
        }

        // validate
        $this->validate($file, $request, $context);

        // pre upload dispatch
        $this->dispatchPreUploadEvent($file, $response, $request, $context);

        $uploadResult = $uploaderManager->upload($file, $context, $requestId);
        $uploadedFile = $uploadResult['uploadedFile'];
        $media        = $uploadResult['media'];

        if ($media) {
            $mediaStructure = $mediaStructure->getMediaStructure($media, $thumbnailFilter, true);
            foreach ($mediaStructure as $key => $value) {
                $response[$key] = $value;
            }
        }

        // post upload dispatch
        $this->dispatchPostEvents($uploadedFile, $media, $response, $request, $context);
    }

    /**
     * @param string            $link
     * @param ResponseInterface $response
     * @param Request           $request
     * @param string            $context
     * @throws ProviderNotFoundException
     * @throws RequestIdNotFoundException
     * @throws \Glavweb\UploaderBundle\Exception\Exception
     */
    private function doUploadByLink($link, ResponseInterface $response, Request $request, $context)
    {
        $mediaStructure  = $this->get('glavweb_uploader.util.media_structure');
        $uploaderManager = $this->get('glavweb_uploader.uploader_manager');

        $requestId              = $request->get('request_id');
        $thumbnailFilter = $request->get('thumbnail_filter');

        if (!$requestId) {
            throw new RequestIdNotFoundException('Request ID not found.');
        }

        /** @var Media $media */
        $uploadResult = $uploaderManager->upload($link, $context, $requestId);;
        $media        = $uploadResult['media'];

        if ($media) {
            $mediaStructure = $mediaStructure->getMediaStructure($media, $thumbnailFilter, true);
            foreach ($mediaStructure as $key => $value) {
                $response[$key] = $value;
            }
        }
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
        $config = $this->getConfig();

        return $config['mappings'][$context];
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        if (!$this->config) {
            $this->config = $this->getParameter('glavweb_uploader.config');
        }

        return $this->config;
    }

    /**
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @return ErrorHandlerInterface
     */
    public function getErrorHandler()
    {
        if (!$this->errorHandler) {
            $this->errorHandler = $this->get('glavweb_uploader.error_handler.standard');
        }

        return $this->errorHandler;
    }

    /**
     * @param ErrorHandlerInterface $errorHandler
     */
    public function setErrorHandler($errorHandler)
    {
        $this->errorHandler = $errorHandler;
    }
}