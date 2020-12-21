<?php

namespace Kaliop\EzOptionFieldTypeBundle\eZ\Publish\FieldType\Option;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use Symfony\Component\Config\Definition\Exception\DuplicateKeyException;

/**
 * Class Value for ezOption field type
 *
 * @package Kaliop\EzOptionFieldTypeBundle\eZ\Publish\FieldType\Option
 */
class Value extends BaseValue
{

    /**
     * Text of options
     *
     * @var string $name
     */
    protected $name;

    /**
     * All options
     *
     * @var OptionElement[] $options
     */
    protected $options = array();

    /**
     * Value constructor
     *
     * @see Value::setName
     * @see Value::addOptions
     *
     * @param string                         $name
     * @param array|OptionElement[]|string[] $options it's possible to combine OptionElement and string in the same array
     *
     * @throws \Symfony\Component\Config\Definition\Exception\DuplicateKeyException
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     */
    public function __construct($name = '', array $options = array())
    {
        parent::__construct();
        $this->setName($name);
        $this->addOptions($options);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return OptionElement[]
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Transform options from object to array
     *
     * @return array
     */
    public function getOptionsToArray()
    {
        $optionsArray = $this->getOptions();

        array_walk($optionsArray, function(&$value) {
            $value = array(
                'id' => $value->id,
                'text' => $value->text,
                'additional_price' => $value->additionalPrice
            );
        });

        return $optionsArray;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Add many options to the option list
     *
     * @see Value::addOption
     *
     * @param array|OptionElement[]|string[] $options it's possible to combine OptionElement and string in the same array
     *
     * @throws \Symfony\Component\Config\Definition\Exception\DuplicateKeyException
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     */
    public function addOptions(array $options)
    {
        foreach ($options as $option) {
            $this->addOption($option);
        }
    }

    /**
     * Add an option to the option list
     *
     * @param OptionElement|string $value
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     * @throws \Symfony\Component\Config\Definition\Exception\DuplicateKeyException
     */
    public function addOption($value)
    {
        $optionElement = $value;

        // create option element if value is text
        if (is_string($value)) {
            $optionElement = new OptionElement($value);
        }

        if (!( $optionElement instanceof OptionElement )) {
            throw new InvalidArgumentType(
                '$value',
                'OptionElement|string',
                $value
            );
        }

        // check duplicate id
        if ($this->options !== null && $optionElement->id !== -1) {
            $option = array_filter($this->options, function ($option) use ($optionElement) {
                return $option->id === $optionElement->id;
            });
            if (count($option) !== 0) {
                throw new DuplicateKeyException('An option already exist with the id : ' . $optionElement->id);
            }
        }

        // update id if equal -1 (auto id)
        else { // $optionElement->id === -1
            if (count($this->options) === 0) {
                $optionElement->id = 0;
            }
            else {
                $maxArrayKey = max(array_keys($this->options));
                $optionElement->id = ++$maxArrayKey;
            }
        }

        // add to options
        $this->options[$optionElement->id] = $optionElement;
    }

    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}
