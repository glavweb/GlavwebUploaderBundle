<?php

namespace Glavweb\UploaderBundle\Driver;

use Doctrine\Common\Annotations\Reader as AnnotationReader;
use Glavweb\UploaderBundle\Exception\ClassNotUploadableException;
use Glavweb\UploaderBundle\Exception\ValueEmptyException;

/**
 * Annotation driver
 *
 */
class AnnotationDriver
{
    const UPLOADABLE_ANNOTATION         = 'Glavweb\UploaderBundle\Mapping\Annotation\Uploadable';
    const UPLOADABLE_FIELD_ANNOTATION   = 'Glavweb\UploaderBundle\Mapping\Annotation\UploadableField';

    protected $reader;

    public function __construct(AnnotationReader $reader)
    {
        $this->reader = $reader;
    }

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
                'mapping'         => $uploadableField->getMapping(),
                'nameAddFunction' => $uploadableField->getNameAddFunction(),
                'nameGetFunction' => $uploadableField->getNameGetFunction()
            );

//            $metadata->fields[$property->getName()] = $fieldData;
            $data[$property->getName()] = $fieldData;
        }

//        return $metadata;
        return $data;
    }

    protected function isUploadable(\ReflectionClass $class)
    {
        return $this->reader->getClassAnnotation($class, self::UPLOADABLE_ANNOTATION) !== null;
    }

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

        return array(
            'mapping'         => $uploadableField->getMapping(),
            'nameAddFunction' => $uploadableField->getNameAddFunction(),
            'nameGetFunction' => $uploadableField->getNameGetFunction()
        );
    }
}
