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

use Glavweb\UploaderBundle\Exception\ClassNotUploadableException;
use Glavweb\UploaderBundle\Exception\Exception;
use Glavweb\UploaderBundle\Exception\ValueEmptyException;
use Glavweb\UploaderBundle\Mapping\Attribute\Uploadable;
use Glavweb\UploaderBundle\Mapping\Attribute\UploadableField;

/**
 * Class AttributeDriver.
 *
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
readonly class AttributeDriver
{
    /**
     * @throws ClassNotUploadableException
     * @throws Exception
     */
    public function loadDataForClass(\ReflectionClass $class): array
    {
        if (!$this->isUploadable($class)) {
            throw new ClassNotUploadableException();
        }

        $data = [];

        foreach ($class->getProperties() as $property) {
            $data[$property->getName()] = $this->getPropertyData($property);
        }

        return $data;
    }

    protected function isUploadable(\ReflectionClass $class): bool
    {
        $attributes = $class->getAttributes(Uploadable::class);

        return [] !== $attributes;
    }

    /**
     * @throws ClassNotUploadableException
     * @throws ValueEmptyException
     * @throws Exception
     */
    public function getDataByFieldName(\ReflectionClass $class, $fieldName): bool|array
    {
        if (!$this->isUploadable($class)) {
            throw new ClassNotUploadableException();
        }

        if (!$fieldName) {
            throw new ValueEmptyException();
        }

        try {
            $property = $class->getProperty($fieldName);
        } catch (\ReflectionException) {
            return false;
        }

        return $this->getPropertyData($property);
    }

    /**
     * @return array<string, string>
     *
     * @throws Exception
     */
    private function getPropertyData(\ReflectionProperty $property): array
    {
        $attributes = $property->getAttributes(UploadableField::class);

        if (\count($attributes) > 1) {
            throw new Exception(\sprintf('There are more than one uploadable field attribute on field "%s".', $property->getName()));
        }

        $uploadableField = $attributes[0]->newInstance();

        return ['mapping' => $uploadableField->getMapping()];
    }
}
