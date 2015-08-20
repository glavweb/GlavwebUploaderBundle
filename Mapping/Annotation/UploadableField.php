<?php

namespace Glavweb\UploaderBundle\Mapping\Annotation;

/**
 * UploadableField.
 *
 * @Annotation
 *
 */
class UploadableField
{
    /**
     * @var string $mapping
     */
    protected $mapping;

    /**
     * @var string $nameAddFunction
     */
    protected $nameAddFunction;

    /**
     * @var string $nameGetFunction
     */
    protected $nameGetFunction;

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

        if (isset($options['nameAddFunction'])) {
            $this->nameAddFunction = $options['nameAddFunction'];
        } else {
            throw new \InvalidArgumentException('The "nameAddFunction" attribute of UploadableField is required.');
        }

        if (isset($options['nameGetFunction'])) {
            $this->nameGetFunction = $options['nameGetFunction'];
        } else {
            throw new \InvalidArgumentException('The "nameGetFunction" attribute of UploadableField is required.');
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
     * @param $mapping The mapping name.
     */
    public function setMapping($mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * @return string
     */
    public function getNameAddFunction()
    {
        return $this->nameAddFunction;
    }

    /**
     * @param string $nameAddFunction
     */
    public function setNameAddFunction($nameAddFunction)
    {
        $this->nameAddFunction = $nameAddFunction;
    }

    /**
     * @return string
     */
    public function getNameGetFunction()
    {
        return $this->nameGetFunction;
    }

    /**
     * @param string $nameGetFunction
     */
    public function setNameGetFunction($nameGetFunction)
    {
        $this->nameGetFunction = $nameGetFunction;
    }
}
