<?php

namespace Kaliop\EzOptionFieldTypeBundle\eZ\Publish\FieldType\Option;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\FieldType\Value as CoreValue;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue as PersistenceValue;
use Kaliop\EzOptionFieldTypeBundle\eZ\Publish\FieldType\Option\Value as OptionValue;
/**
 * Class Type
 *
 * @package Kaliop\EzOptionFieldTypeBundle\eZ\Publish\FieldType\Option
 */
class Type extends FieldType
{
    /**
     * Return the field type identifier as string
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return 'ezoption';
    }

    /**
     * @inheritdoc
     */
    public function validateValidatorConfiguration($validatorConfiguration)
    {
        $validationErrors = array();

        /** @noinspection ForeachSourceInspection */
        foreach ($validatorConfiguration as $validatorIdentifier => $constraints) {
            // Report unknown validators
            $validationErrors[] = new ValidationError("Validator '$validatorIdentifier' is unknown");
            continue;

            // TODO : Create validator
        }

        return $validationErrors;
    }

    /**
     * @inheritdoc
     */
    public function validate(FieldDefinition $fieldDefinition, SPIValue $fieldValue)
    {
        $errors = array();

        if ($this->isEmptyValue($fieldValue)) {
            return $errors;
        }

        // TODO : Create validator

        return $errors;
    }

    /**
     * Implements the core of {@see isEmptyValue()}.
     *
     * @param mixed $value
     *
     * @return boolean
     */
    public function isEmptyValue(SPIValue $value)
    {
        return $value->name === null || trim($value->name) === '';
    }

    /**
     * @inheritdoc
     *
     * @return OptionValue
     */
    public function fromHash($hash)
    {
        if ($hash === null) {
            return $this->getEmptyValue();
        }

        $options = array_map(function($value) {
            return new OptionElement($value['text'], isset($value['additional_price']) ? $value['additional_price'] : null, isset($value['id']) ? $value['id'] : -1);
        }, $hash['options']);

        return new OptionValue($hash['name'], $options);
    }

    /**
     * @inheritdoc
     *
     * @return OptionValue
     */
    public function getEmptyValue()
    {
        return new OptionValue;
    }

    /**
     * @inheritdoc
     *
     * @param Value $value
     */
    public function toPersistenceValue(SPIValue $value)
    {
        if ($value === null) {
            return new PersistenceValue(
                array(
                    'data'         => null,
                    'externalData' => null,
                    'sortKey'      => null,
                )
            );
        }

        return new PersistenceValue(
            array(
                'data'         => $this->toHash($value),
                'sortKey'      => $this->getSortInfo($value),
            )
        );
    }

    /**
     * @inheritdoc
     *
     * @param OptionValue $value
     */
    public function toHash(SPIValue $value)
    {
        if ($this->isEmptyValue($value)) {
            return null;
        }

        $options = array();
        foreach ($value->getOptions() as $option) {
            $options[] = array(
                'text' => $option->text,
                'additional_price' => $option->additionalPrice,
                'id' => $option->id
            );
        }

        return array(
            'name'   => $value->getName(),
            'options' => $options,
        );
    }

    /**
     * @inheritdoc
     *
     * @param OptionValue $value
     */
    protected function getSortInfo(CoreValue $value)
    {
        return $this->getName($value);
    }

    /**
     * @inheritdoc
     *
     * @param OptionValue $value
     */
    public function getName(SPIValue $value)
    {
        return $value->getName();
    }

    /**
     * @inheritdoc
     *
     * @return OptionValue
     */
    public function fromPersistenceValue(PersistenceValue $fieldValue)
    {
        return $this->fromHash($fieldValue->data);
    }

    /**
     * @inheritdoc
     *
     * @param string|OptionValue $inputValue
     *
     * @return OptionValue
     */
    protected function createValueFromInput($inputValue)
    {
        if (is_string($inputValue)) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $inputValue = new OptionValue($inputValue );
        }

        return $inputValue;
    }

    /**
     * @inheritdoc
     *
     * @param OptionValue $value
     */
    protected function checkValueStructure(CoreValue $value)
    {
        // Check that question is a string
        if (!is_string($value->getName())) {
            throw new InvalidArgumentType(
                '$value->name',
                'string',
                $value->getName()
            );
        }

        // Check that responses is an array
        if (!is_array($value->getOptions())) {
            throw new InvalidArgumentType(
                '$value->options',
                'array',
                $value->getOptions()
            );
        }

        // Check options type
        /** @var OptionElement[] $badIdOptions */
        $badOptions = array_filter($value->getOptions(), function($option) {
            return !($option instanceof OptionElement);
        });
        /** @noinspection LoopWhichDoesNotLoopInspection */
        foreach ($badOptions as $key => $option) {
            throw new InvalidArgumentType(
                '$value->options[' . $key . ']',
                'OptionElement',
                $option
            );
        }

        // Check text option type
        /** @var OptionElement[] $badIdOptions */
        $badTextOptions = array_filter($value->getOptions(), function(/** @var OptionElement $option */$option) {
            return !is_string($option->text);
        });
        /** @noinspection LoopWhichDoesNotLoopInspection */
        foreach ($badTextOptions as $key => $option) {
            throw new InvalidArgumentType(
                '$value->options[' . $key . ']->text',
                'string',
                $option->text
            );
        }

        // Check id option type
        /** @var OptionElement[] $badIdOptions */
        $badIdOptions = array_filter($value->getOptions(), function(/** @var OptionElement $option */$option) {
            return !is_int($option->id);
        });
        /** @noinspection LoopWhichDoesNotLoopInspection */
        foreach ($badIdOptions as $key => $option) {
            throw new InvalidArgumentType(
                '$value->options[' . $key . ']->id',
                'int',
                $option->id
            );
        }

        // Check additional price option type
        /** @var OptionElement[] $badIdOptions */
        $badPriceOptions = array_filter($value->getOptions(), function(/** @var OptionElement $option */$option) {
            return $option->additionalPrice !== null && !is_int($option->additionalPrice);
        });
        /** @noinspection LoopWhichDoesNotLoopInspection */
        foreach ($badPriceOptions as $key => $option) {
            throw new InvalidArgumentType(
                '$value->options[' . $key . ']->additionalPrice',
                'int',
                $option->additionalPrice
            );
        }

    }
}
