<?php

namespace tbn\ApiGeneratorBundle\Services;

/**
 *
 * @author Thomas BEAUJEAN
 *
 * ref: tbn.api_generator.service.converter_service
 *
 */
class ConverterService
{
    protected $converterMapping;

    /**
     * Constructor
     *
     * @param array $converterMapping
     */
    public function __construct($converterMapping)
    {
        $this->converterMapping = $converterMapping;
    }

    /**
     *
     * @param string  $fieldType
     * @param unknown $originalValue
     *
     * @return unknown
     */
    public function revert($fieldType, $originalValue)
    {
        $convertedValue = $originalValue;

        if ($originalValue !== null) {
            if (key_exists($fieldType, $this->converterMapping)) {
                $converterClass = $this->converterMapping[$fieldType];
                $converter = new $converterClass();
                $convertedValue = $converter->revert($originalValue);
            }
        }

        return $convertedValue;
    }
}
