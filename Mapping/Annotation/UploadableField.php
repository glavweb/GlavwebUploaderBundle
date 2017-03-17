<?php

/*
 * This file is part of the Glavweb UploaderBundle package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\UploaderBundle\Mapping\Annotation;

/**
 * Class UploadableField
 *
 * @Annotation
 *
 * @package Glavweb\UploaderBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class UploadableField
{
    /**
     * @var string $mapping
     */
    protected $mapping;

     /**
     * Constructs a new instance of UploadableField.
     *
     * @param  array                     $options The options.
     * @throws \InvalidArgumentException
     */
    public function __construct(array $options)
    {
        if (isset($options['mapping'])) {
            $this->mapping = $options['mapping'];

        } else {
            throw new \InvalidArgumentException('The "mapping" attribute of UploadableField is required.');
        }
    }

    /**
     * Gets the mapping name.
     *
     * @return string The mapping name.
     */
    public function getMapping()
    {
        return $this->mapping;
    }

    /**
     * Sets the mapping name.
     *
     * @param string $mapping The mapping name.
     */
    public function setMapping($mapping)
    {
        $this->mapping = $mapping;
    }
}
