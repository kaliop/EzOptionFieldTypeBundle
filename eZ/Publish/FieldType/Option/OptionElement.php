<?php

namespace Kaliop\EzOptionFieldTypeBundle\eZ\Publish\FieldType\Option;

/**
 * Class OptionElement
 *
 * @package Kaliop\EzOptionFieldTypeBundle\eZ\Publish\FieldType\Option
 */
class OptionElement
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $additionalPrice;

    /**
     * @var string
     */
    public $text;

    /**
     * OptionElement constructor.
     *
     * @param string   $text
     * @param int|null $additionalPrice : [optional] no additional price if null
     * @param int      $id              : [optional] if -1, the id is set automatically on add to Option\Value
     */
    public function __construct($text, $additionalPrice = null, $id = -1)
    {
        $this->text = $text;
        $this->additionalPrice = $additionalPrice;
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->text;
    }
}
