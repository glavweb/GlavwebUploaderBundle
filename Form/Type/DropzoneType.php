<?php

namespace Glavweb\UploaderBundle\Form\Type;

use Glavweb\UploaderBundle\Driver\AnnotationDriver;
use Glavweb\UploaderBundle\Exception\MappingNotSetException;
use Glavweb\UploaderBundle\Exception\NotFoundPropertiesInAnnotationException;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Glavweb\UploaderBundle\Helper\MediaHelper;
use Symfony\Component\Translation\DataCollectorTranslator;

/**
 * Class DropzoneType
 * @package Glavweb\UploaderBundle\Form\Type
 */
class DropzoneType extends AbstractType
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    protected $router;

    /**
     * @var \Glavweb\UploaderBundle\Helper\MediaHelper
     */
    protected $mediaHelper;

    /**
     * @var \Glavweb\UploaderBundle\Driver\AnnotationDriver;
     */
    protected $driverAnnotation;

    /**
     * @var DataCollectorTranslator
     */
    protected $translator;


    /**
     * @param Router $router
     * @param MediaHelper $mediaHelper
     * @param array $config
     * @param AnnotationDriver $driverAnnotation
     */
    public function __construct(Router $router, MediaHelper $mediaHelper, array $config, AnnotationDriver $driverAnnotation, DataCollectorTranslator $translator)
    {
        $this->router           = $router;
        $this->mediaHelper      = $mediaHelper;
        $this->config           = $config;
        $this->driverAnnotation = $driverAnnotation;
        $this->translator       = $translator;
    }

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     * @throws MappingNotSetException
     * @throws NotFoundPropertiesInAnnotationException
     * @throws \Glavweb\UploaderBundle\Exception\ClassNotUploadableException
     * @throws \Glavweb\UploaderBundle\Exception\ValueEmptyException
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $entity = $form->getParent()->getData();
        $fieldName = $form->getConfig()->getName();

        if (!isset($options['requestId'])) {
            $options['requestId'] = uniqid();
        }

        $dataPropertyAnnotation = $this->driverAnnotation->getDataByFieldName(new \ReflectionClass($entity), $fieldName);

        if (!$dataPropertyAnnotation) {
            throw new NotFoundPropertiesInAnnotationException();
        }

        $files = $entity->$dataPropertyAnnotation['nameGetFunction']();
        $context = $dataPropertyAnnotation['mapping'];

        if (!$context) {
            throw new MappingNotSetException();
        }

        $config = $this->getConfigByContext($context);

        $router    = $this->router;
        $uploadDir = $this->mediaHelper->getUploadDirectoryUrl($context);
        $urls      = array(
            'upload' => $router->generate('glavweb_uploader_upload', array('context' => $context)),
            'rename' => $router->generate('glavweb_uploader_rename', array('context' => $context)),
            'delete' => $router->generate('glavweb_uploader_delete', array('context' => $context)),
        );

        $view->vars['requestId']        = $options['requestId'];
        $view->vars['views']            = $options['views' ];
        $view->vars['type']             = $context;
        $view->vars['files']            = $files;
        // $view->vars['previewImg']    = $options['previewImg'];
        $view->vars['previewShow']      = array_merge($options['previewShowDefault'],$options['previewShow']);

        // Dropzone
        $view->vars['dropzoneOptions'] = array_merge($options['dropzoneOptionsDefault'], array(
            'url'               => $urls['upload'],
            'uploadDir'         => $uploadDir,
            'previewTemplate'   => '#js-gwu-template_' . $options['requestId'],
            'previewsContainer' => '#js-gwu-previews_' . $options['requestId'],
            'form'              => '.js-gwu-from_' . $options['requestId'],
            'link'              => '.js-gwu-link_' . $options['requestId'],
            'maxFilesize'       => $config['max_size'],
            'clickable'         => '.js-gwu-clickable_' . $options['requestId'],
        ),$options['dropzoneOptions']);

        // Uploader
        $view->vars['uploaderOptions'] = array_merge($options['uploaderOptionsDefault'], array(
            'urls'              => $urls,
            'requestId'         => $options['requestId'],
            'dropzoneContainer' => '#js-gwu-dropzone_' . $options['requestId'],
            'previewShow'       => $view->vars['previewShow'],
            'uploadDir'         => $uploadDir,
            'countFiles'        => $files->count(),
            'maxFiles'          => $view->vars['dropzoneOptions']['maxFiles'],
            'type'              => $context,
            'clickable'         => '.js-gwu-clickable_' . $options['requestId'],
        ), $options['uploaderOptions']);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $translator = $this->translator;

        $resolver->setDefaults(array(
            'previewImg'         => null,
            'requestId'          => null,
            'useLink'            => true,
            'useForm'            => true,
            'showMark'           => true,
            'showUploadButton'   => true,
            'showLabel'          => true,
            'thumbnailOptions'   => array(
                'width'     => 200,
                'height'    => 200,
            ),
            'views'              => array(
//                'form' => 'path/to/view',
//                'link' => 'path/to/view',
//                'preview' => 'path/to/view',
            ),
            'previewShow'        => array(),
            'previewShowDefault' => array(
                'isDetails'  => true,
                'isSize'     => true,
                'isFilename' => true,
                'isProgress' => true,
                'isError'    => true,
                'isShowMark' => true
            ),
            'uploaderOptions'    => array(),
            'uploaderOptionsDefault' => array(
                'type'             => null,
                'uploaderClass'    => '',
                'formViewType'     => 'form',
                'previewViewType'  => 'image',
                'preloader'        => '.js-gwu-preloader',
                'upoloaderError'   => '.js-gwu-error',
                'previewContainer' => '.js-gwu-preview',
                'rename'           => '.js-gwu-rename',
                'filename'         => '.js-gwu-filename',
                'description'      => '.js-gwu-description',
                'form'             => '.js-gwu-form',
                'link'             => '.js-gwu-link',
                'popup'            => '.js-gwu-popup',
                'isPopup'          => true,
                'isName'           => true,
                'isDescription'    => false,
                'isSort'           => false,
                'isShowErrorPopup' => false,
                'isThumbnail'      => true,
                'isUploadButton'   => true,
                'thumbnailOptions' => array(),
                'countFiles'       => 0
            ),
            'dropzoneOptions'    => array(),
            'dropzoneOptionsDefault' => array(
                'url'                          => null,
                'previewTemplate'              => null,
                'previewsContainer'            => null,
                'clickable'                    => null,
                'maxFilesize'                  => 2,
                'maxFiles'                     => 20,
                'thumbnailWidth'               => 350,
                'thumbnailHeight'              => 350,
                'parallelUploads'              => 20,
                'autoQueue'                    => true,
                'autoProcessQueue'             => true,
                'acceptedFiles'                => '.png, .jpg',
                'dictDefaultMessage'           => $translator->trans('dropzone.files_uploaded'),
                'dictFallbackMessage'          => $translator->trans('dropzone.browser_not_support_drag_n_drop'),
                'dictFileTooBig'               => $translator->trans('dropzone.file_size_too_large'),
                'dictInvalidFileType'          => $translator->trans('dropzone.wrong_format'),
                'dictResponseError'            => $translator->trans('dropzone.disable_adblocker'),
                'dictCancelUpload'             => $translator->trans( 'dropzone.cancel_upload'),
                'dictCancelUploadConfirmation' => $translator->trans('dropzone.cancel_upload_confirmation'),
                'dictRemoveFile'               => $translator->trans('dropzone.remove_file'),
                'dictRemoveFileConfirmation'   => null,
                'dictMaxFilesExceeded'         => $translator->trans('dropzone.max_files_exceeded')
            ),
        ));

    }

    /**
     * @return null|string|\Symfony\Component\Form\FormTypeInterface
     */
    public function getParent()
    {
        return 'form';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'glavweb_uploader_dropzone';
    }

    /**
     * @param  $files
     * @return array
     */
    protected function prepareFiles($files)
    {
//        if (isset($files[0]) && $files[0] instanceof File) {
//            return array_map(function($file) {
//                return array(
//                    'id'   => $file->getId(),
//                    'path' => $this->mediaHelper->getWebPath($file),
//                    'name' => $file->getClientName()
//                );
//            }, $files);
//
//        }

        return $files;
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