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
     * @param Router $router
     * @param MediaHelper $mediaHelper
     * @param array $config
     * @param AnnotationDriver $driverAnnotation
     */
    public function __construct(Router $router, MediaHelper $mediaHelper, array $config, AnnotationDriver $driverAnnotation)
    {
        $this->router           = $router;
        $this->mediaHelper      = $mediaHelper;
        $this->config           = $config;
        $this->driverAnnotation = $driverAnnotation;
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
        $maxFiles = $config['max_files'];

        $view->vars['uploadedFilesTpl'] = $options['uploadedFilesTpl'];
        $view->vars['uploadItemTpl']    = $options['uploadItemTpl'];
        $view->vars['viewFormTpl']      = $options['viewFormTpl'];
        $view->vars['viewLinkTpl']      = $options['viewLinkTpl'];
        $view->vars['type']             = $context;
        $view->vars['previewImg']       = $options['previewImg'];
        $view->vars['useLink']          = $options['useLink'];
        $view->vars['useForm']          = $options['useForm'];
        $view->vars['showMark']         = $options['showMark'];
        $view->vars['thumbnailWidth']   = $options['thumbnailWidth' ];
        $view->vars['thumbnailHeight']  = $options['thumbnailHeight'];
        $view->vars['showUploadButton'] = $options['showUploadButton'];
        $view->vars['uploaderClass']    = $options['uploaderClass'];
        $view->vars['isShowErrorPopup'] = $options['isShowErrorPopup'];
        $view->vars['files']            = $files;
        $view->vars['countFiles']       = $files->count();
        $view->vars['showLabel']        = $options['showLabel'];
        $view->vars['requestId']        = $options['requestId'];
        $view->vars['uploadDir']        = $this->mediaHelper->getUploadDirectoryUrl($context);
        $view->vars['deleteUrl']        = $this->router->generate('glavweb_uploader_delete', array('context' => $context));
        $view->vars['renameUrl']        = $this->router->generate('glavweb_uploader_rename', array('context' => $context));
        $view->vars['maxFiles']         = $maxFiles;
        $view->vars['maxSize']          = $config['max_size'];
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'previewImg'         => null,
            'requestId'          => null,
            'useLink'            => true,
            'useForm'            => true,
            'showMark'           => true,
            'showUploadButton'   => true,
            'uploadedFilesTpl'   => 'GlavwebUploaderBundle:Form:base_upload_item_tpl.html.twig',
            'uploadItemTpl'      => 'GlavwebUploaderBundle:Form:base_uploaded_files.html.twig',
            'viewFormTpl'        => 'GlavwebUploaderBundle:Form:view-form.html.twig',
            'viewLinkTpl'        => 'GlavwebUploaderBundle:Form:view-link.html.twig',
            'showLabel'          => true,
            'thumbnailWidth'     => 200,
            'thumbnailHeight'    => 150,
            'uploaderClass'      => '',
            'isShowErrorPopup'   => true,
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