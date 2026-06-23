<?php

/*
 * This file is part of the Glavweb UploaderBundle package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\UploaderBundle\Mapping\Attribute;

/**
 * Class UploadableField.
 *
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class UploadableField
{
    /**
     * @var string
     */
    protected $mapping;

    /**
     * Constructs a new instance of UploadableField.
     *
     * @param array<string, mixed> $options the options
     *
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
     * @return string the mapping name
     */
    public function getMapping()
    {
        return $this->mapping;
    }

    /**
     * Sets the mapping name.
     *
     * @param string $mapping the mapping name
     */
    public function setMapping(string $mapping): void
    {
        $this->mapping = $mapping;
    }
}
