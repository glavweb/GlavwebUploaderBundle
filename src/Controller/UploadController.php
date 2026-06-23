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
use Glavweb\UploaderBundle\ErrorHandler\StandardErrorHandler;
use Glavweb\UploaderBundle\Event\PostUploadEvent;
use Glavweb\UploaderBundle\Event\PreUploadEvent;
use Glavweb\UploaderBundle\Event\ValidationEvent;
use Glavweb\UploaderBundle\Exception\CropImageException;
use Glavweb\UploaderBundle\Exception\ProviderNotFoundException;
use Glavweb\UploaderBundle\Exception\RequestIdNotFoundException;
use Glavweb\UploaderBundle\File\FileInterface;
use Glavweb\UploaderBundle\File\FilesystemFile;
use Glavweb\UploaderBundle\Manager\UploaderManager;
use Glavweb\UploaderBundle\Model\MediaInterface;
use Glavweb\UploaderBundle\Response\Response as UploaderResponse;
use Glavweb\UploaderBundle\Response\ResponseInterface;
use Glavweb\UploaderBundle\UploadEvents;
use Glavweb\UploaderBundle\Util\MediaStructure;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Class UploadController.
 *
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class UploadController extends AbstractController
{
    protected ?ErrorHandlerInterface $errorHandler = null;

    /**
     * Upload file.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws RequestIdNotFoundException
     */
    #[Route('/upload/{context}', name: 'glavweb_uploader_upload', methods: ['POST'])]
    public function uploadAction(
        Request $request,
        string $context,
        UploaderManager $uploaderManager,
        MediaStructure $mediaStructure,
        EventDispatcherInterface $eventDispatcher,
        StandardErrorHandler $standardErrorHandler,
    ): JsonResponse {
        $response = new UploaderResponse();
        $files = $this->getFiles($request->files);

        /** @var UploadedFile $file */
        foreach ($files as $file) {
            try {
                $this->doUpload($file, $response, $request, $context, $uploaderManager, $mediaStructure, $eventDispatcher);
            } catch (UploadException|ProviderNotFoundException $e) {
                ($this->errorHandler ?? $standardErrorHandler)->addException($response, $e);
            }
        }

        return $this->createSupportedJsonResponse($response->assemble(), $request);
    }

    /**
     *  Flattens a given filebag to extract all files.
     *
     * @param FileBag $bag The filebag to use
     *
     * @return array An array of files
     */
    protected function getFiles(FileBag $bag): array
    {
        $files = [];
        $fileBag = $bag->all();

        $fileIterator = new \RecursiveIteratorIterator(
            new \RecursiveArrayIterator($fileBag),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($fileIterator as $file) {
            if (\is_array($file)) {
                continue;
            }

            if (null === $file) {
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
     * @param ResponseInterface $response a response object
     * @param Request           $request  the request object
     * @param string            $context  The context
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ProviderNotFoundException
     * @throws RequestIdNotFoundException
     * @throws \Exception
     */
    protected function doUpload(
        File $file,
        ResponseInterface $response,
        Request $request,
        string $context,
        UploaderManager $uploaderManager,
        MediaStructure $mediaStructure,
        EventDispatcherInterface $eventDispatcher,
    ): void {
        $config = $this->getParameter('glavweb_uploader.config');

        if (isset($config['chunk_upload']) && $uploaderManager->isChunkUpload($request)) {
            $concatenatedFile = $uploaderManager->handleChunkUpload($request, $file);

            if ($concatenatedFile instanceof File) {
                $originalFileName = $file instanceof UploadedFile ? $file->getClientOriginalName() : null;

                $file = new FilesystemFile($concatenatedFile, $originalFileName);
            } else {
                return;
            }
        }

        $payload = $request->getPayload();
        $requestId = $payload->get('request_id');
        $thumbnailFilter = $payload->get('thumbnail_filter');

        if (!$requestId) {
            throw new RequestIdNotFoundException('Request ID not found.');
        }

        if (!$file instanceof FileInterface) {
            $file = new FilesystemFile($file);
        }

        // validate
        $this->validate($uploaderManager, $file, $request, $context, $eventDispatcher);

        // pre upload dispatch
        $this->dispatchPreUploadEvent($uploaderManager, $file, $response, $request, $context, $eventDispatcher);

        $uploadResult = $uploaderManager->upload($file, $context, $requestId);
        $uploadedFile = $uploadResult['uploadedFile'];
        $media = $uploadResult['media'];

        if ($media) {
            $mediaStructure = $mediaStructure->getMediaStructure($media, $thumbnailFilter, true);
            foreach ($mediaStructure as $key => $value) {
                $response[$key] = $value;
            }
        }

        // post upload dispatch
        $this->dispatchPostEvents($uploaderManager, $uploadedFile, $media, $response, $request, $context, $eventDispatcher);
    }

    protected function validate(
        UploaderManager $uploaderManager,
        FileInterface $file,
        Request $request,
        string $context,
        EventDispatcherInterface $eventDispatcher,
    ): void {
        $configContext = $uploaderManager->getContextConfig($context);

        // dispatch validation event (both the specific and the general)
        $event = new ValidationEvent($file, $request, $configContext, $context);
        $eventDispatcher->dispatch($event, UploadEvents::VALIDATION);
        $eventDispatcher->dispatch($event, \sprintf('%s.%s', UploadEvents::VALIDATION, $context));
    }

    /**
     *  This function is a helper function which dispatches pre upload event.
     */
    protected function dispatchPreUploadEvent(
        UploaderManager $uploaderManager,
        FileInterface $uploadedFile,
        ResponseInterface $response,
        Request $request,
        string $context,
        EventDispatcherInterface $eventDispatcher,
    ): void {
        $configContext = $uploaderManager->getContextConfig($context);

        // dispatch pre upload event (both the specific and the general)
        $event = new PreUploadEvent($uploadedFile, $response, $request, $context, $configContext);
        $eventDispatcher->dispatch($event, UploadEvents::PRE_UPLOAD);
        $eventDispatcher->dispatch($event, \sprintf('%s.%s', UploadEvents::PRE_UPLOAD, $context));
    }

    /**
     *  This function is a helper function which dispatches post upload
     *  and post persist events.
     */
    protected function dispatchPostEvents(
        UploaderManager $uploaderManager,
        FileInterface $uploadedFile,
        MediaInterface $media,
        ResponseInterface $response,
        Request $request,
        string $context,
        EventDispatcherInterface $eventDispatcher,
    ): void {
        $configContext = $uploaderManager->getContextConfig($context);

        // dispatch post upload event (both the specific and the general)
        $event = new PostUploadEvent($uploadedFile, $media, $response, $request, $context, $configContext);
        $eventDispatcher->dispatch($event, UploadEvents::POST_UPLOAD);
        $eventDispatcher->dispatch($event, \sprintf('%s.%s', UploadEvents::POST_UPLOAD, $context));
    }

    /**
     * Creates and returns a JsonResponse with the given data.
     *
     * On top of that, if the client does not support the application/json type,
     * then the content type of the response will be set to text/plain instead.
     */
    protected function createSupportedJsonResponse(mixed $data, Request $request, ?int $status = null): JsonResponse
    {
        if (null === $status) {
            $status = isset($data['error']) ? 400 : 200;
        }

        $response = new JsonResponse($data, $status);
        $response->headers->set('Vary', 'Accept');

        if (!\in_array('application/json', $request->getAcceptableContentTypes())) {
            $response->headers->set('Content-type', 'text/plain');
        }

        return $response;
    }

    public function setErrorHandler(ErrorHandlerInterface $errorHandler): void
    {
        $this->errorHandler = $errorHandler;
    }

    /**
     * Upload file by URL.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws RequestIdNotFoundException|ProviderNotFoundException
     */
    #[Route('/uploadlink/{context}', name: 'glavweb_uploader_uploadlink', methods: ['POST'])]
    public function uploadLinkAction(
        Request $request,
        string $context,
        UploaderManager $uploaderManager,
        MediaStructure $mediaStructure,
        StandardErrorHandler $standardErrorHandler,
    ): JsonResponse {
        $response = new UploaderResponse();
        $link = $request->getPayload()->get('file_link');

        try {
            $this->doUploadByLink($link, $response, $request, $context, $uploaderManager, $mediaStructure);
        } catch (UploadException $uploadException) {
            ($this->errorHandler ?? $standardErrorHandler)->addException($response, $uploadException);
        }

        return $this->createSupportedJsonResponse($response->assemble(), $request);
    }

    /**
     * @throws RequestIdNotFoundException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface|ProviderNotFoundException
     */
    private function doUploadByLink(
        string $link,
        ResponseInterface $response,
        Request $request,
        string $context,
        UploaderManager $uploaderManager,
        MediaStructure $mediaStructure,
    ): void {
        $requestId = $request->getPayload()->get('request_id');
        $thumbnailFilter = $request->getPayload()->get('thumbnail_filter');

        if (!$requestId) {
            throw new RequestIdNotFoundException('Request ID not found.');
        }

        /** @var Media $media */
        $uploadResult = $uploaderManager->upload($link, $context, $requestId);
        $media = $uploadResult['media'];

        if ($media) {
            $mediaStructure = $mediaStructure->getMediaStructure($media, $thumbnailFilter, true);
            foreach ($mediaStructure as $key => $value) {
                $response[$key] = $value;
            }
        }
    }

    /**
     * Delete file by secured id.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/delete', name: 'glavweb_uploader_delete', methods: ['POST'])]
    public function deleteAction(Request $request, UploaderManager $uploaderManager): JsonResponse
    {
        $success = false;

        $payload = $request->getPayload();
        $id = $payload->get('id');
        $requestId = $payload->get('request_id');

        $modelManager = $uploaderManager->getModelManager();
        $media = $modelManager->findOneBySecuredId($id);

        if ($media instanceof MediaInterface && $requestId) {
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
     * Edit file.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface|CropImageException
     */
    #[Route('/edit', name: 'glavweb_uploader_edit', methods: ['POST'])]
    public function editAction(Request $request, UploaderManager $uploaderManager, MediaStructure $mediaStructure): JsonResponse
    {
        $success = false;

        $payload = $request->getPayload();
        $id = $payload->get('id');
        $requestId = $payload->get('request_id');
        $name = $payload->get('name');
        $description = $payload->get('description');
        $cropData = $payload->get('crop_data');
        $softEdit = $payload->get('soft_edit', false);

        if ($cropData) {
            $cropData = json_decode($cropData, true);
        }

        $modelManager = $uploaderManager->getModelManager();
        $media = $modelManager->findOneBySecuredId($id);

        if ($media instanceof MediaInterface && $requestId && ($name || $description)) {
            if ($softEdit) {
                $success = $modelManager->markEdit($media, $requestId, $name, $description);
            } elseif ($media->getIsOrphan()) {
                if ($media->getRequestId() == $requestId) {
                    $success = $modelManager->editMedia($media, $name, $description);
                }
            } else {
                $success = $modelManager->editMedia($media, $name, $description);
            }
        }

        if ($media instanceof MediaInterface) {
            if ($cropData) {
                $uploaderManager->cropImage($media, $cropData);
            }

            $mediaStructureData = $mediaStructure->getMediaStructure($media, null, true);
        }

        return new JsonResponse([
            'success' => $success,
            'media' => $mediaStructureData ?? null,
        ], $success ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST);
    }
}
