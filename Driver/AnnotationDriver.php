<?php

/*
 * This file is part of the Glavweb UploaderBundle package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\UploaderBundle\Driver;

use Doctrine\Common\Annotations\Reader as AnnotationReader;
use Glavweb\UploaderBundle\Exception\ClassNotUploadableException;
use Glavweb\UploaderBundle\Exception\ValueEmptyException;

/**
 * Class AnnotationDriver
 *
 * @package Glavweb\UploaderBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class AnnotationDriver
{
    const UPLOADABLE_ANNOTATION       = 'Glavweb\UploaderBundle\Mapping\Annotation\Uploadable';
    const UPLOADABLE_FIELD_ANNOTATION = 'Glavweb\UploaderBundle\Mapping\Annotation\UploadableField';

    /**
     * @var AnnotationReader
     */
    protected $reader;

    /**
     * @param AnnotationReader $reader
     */
    public function __construct(AnnotationReader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @param \ReflectionClass $class
     * @return array
     * @throws ClassNotUploadableException
     */
    public function loadDataForClass(\ReflectionClass $class)
    {
        if (!$this->isUploadable($class)) {
            throw new ClassNotUploadableException();
        }

        $data = array();

        foreach ($class->getProperties() as $property) {
            $uploadableField = $this->reader->getPropertyAnnotation($property, self::UPLOADABLE_FIELD_ANNOTATION);
            if ($uploadableField === null) {
                continue;
            }

            $fieldData = array(
                'mapping' => $uploadableField->getMapping()
            );

            $data[$property->getName()] = $fieldData;
        }

        return $data;
    }

    /**
     * @param \ReflectionClass $class
     * @return bool
     */
    protected function isUploadable(\ReflectionClass $class)
    {
        return $this->reader->getClassAnnotation($class, self::UPLOADABLE_ANNOTATION) !== null;
    }

    /**
     * @param \ReflectionClass $class
     * @param $fieldName
     * @return array|bool
     * @throws ClassNotUploadableException
     * @throws ValueEmptyException
     */
    public function getDataByFieldName(\ReflectionClass $class, $fieldName)
    {
        if (!$this->isUploadable($class)) {
            throw new ClassNotUploadableException();
        }

        if (!$fieldName) {
            throw new ValueEmptyException();
        }

        $property = $class->getProperty($fieldName);

        if (!$property) {
            return false;
        }

        $uploadableField = $this->reader->getPropertyAnnotation($property, self::UPLOADABLE_FIELD_ANNOTATION);

        if (!$uploadableField) {
            return false;
        }

        return [
            'mapping' => $uploadableField->getMapping()
        ];
    }
}
