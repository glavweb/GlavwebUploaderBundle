<?php

namespace Glavweb\UploaderBundle\Form\Type;

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
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    protected $router;

    /**
     * @var \Glavweb\UploaderBundle\Helper\MediaHelper
     */
    protected $mediaHelper;

    /**
     * @param Router     $router
     * @param MediaHelper $mediaHelper
     */
    public function __construct(Router $router, MediaHelper $mediaHelper)
    {
        $this->router     = $router;
        $this->mediaHelper = $mediaHelper;
    }

    /**
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (!isset($options['files'])) {
            $options['files'] = array();
        }

        if (!isset($options['requestId'])) {
            $options['requestId'] = uniqid();
        }

        $files = $this->prepareFiles($options['files']);

        $view->vars['type']      = $options['type'];
        $view->vars['useLink']   = $options['useLink'];
        $view->vars['useForm']   = $options['useForm'];
        $view->vars['files']     = $files;
        $view->vars['requestId'] = $options['requestId'];
        $view->vars['uploadDir'] = $this->mediaHelper->getUploadDirectoryUrl($options['type']);
        $view->vars['deleteUrl'] = $this->router->generate('glavweb_uploader_delete', array('context' => $options['type']));
        $view->vars['renameUrl'] = $this->router->generate('glavweb_uploader_rename', array('context' => $options['type']));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'files'     => array(),
            'requestId' => null,
            'useLink'   => true,
            'useForm'   => true,
            'type'      => null
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
}