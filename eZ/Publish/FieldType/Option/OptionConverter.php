<?php

namespace Kaliop\EzOptionFieldTypeBundle\eZ\Publish\FieldType\Option;

use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;

/**
 * Class LegacyConverter
 *
 * @package Kaliop\EzOptionFieldTypeBundle\eZ\Publish\FieldType\Option
 */
class OptionConverter implements Converter
{
    public static function create()
    {
        return new self;
    }

    /**
     * @inheritdoc
     *
     * @param Value $value
     */
    public function toStorageValue(FieldValue $value, StorageFieldValue $storageFieldValue)
    {
        $storageFieldValue->dataText = $this->serializeData($value);
        $storageFieldValue->sortKeyString = $value->sortKey;
    }

    /**
     * Serialize value to store on legacy storage
     *
     * @param FieldValue $value
     *
     * @return string
     */
    private function serializeData(FieldValue $value)
    {
        $data = $value->data;

        $xml = new \SimpleXMLElement('<ezoption/>');
        $xmlName = $xml->addChild('name');
        $this->appendCData($xmlName, isset($data['name']) ? $data['name'] : '');

        $xmlOptions = $xml->addChild('options');

        if (isset($data['options'])) {
        /** @var OptionElement[] $options */
            $options = $data['options'];
            foreach ($options as $option) {
                /** @noinspection DisconnectedForeachInstructionInspection */
                $xmlOption = $xmlOptions->addChild('option');
                $this->appendCData($xmlOption, $option['text']);
                $xmlOption->addAttribute('additional_price', $option['additional_price']);
                $xmlOption->addAttribute('id', $option['id']);
            }
        }

        return $xml->asXML();
    }

    /**
     * Add cdata to a xml element
     *
     * @param \SimpleXMLElement $simpleXMLElement
     * @param string            $value
     */
    private function appendCData(\SimpleXMLElement $simpleXMLElement, $value)
    {
        $dom = dom_import_simplexml($simpleXMLElement);
        $cdata = $dom->ownerDocument->createCDATASection($value);
        $dom->appendChild($cdata);
    }

    /**
     * @inheritdoc
     *
     * @param Value $value
     */
    public function toFieldValue(StorageFieldValue $value, FieldValue $fieldValue)
    {
        $fieldValue->data = $this->unserializeData($value);
        $fieldValue->sortKey = $value->sortKeyString;
    }

    /**
     * Parse a storage field value to an array
     *
     * @param StorageFieldValue $value
     *
     * @return array
     */
    private function unserializeData(StorageFieldValue $value)
    {
        $data = array();

        $xml = simplexml_load_string($value->dataText);

        $data['name'] = $this->transformSimpleXmlElementToData($xml->name);
        $data['options'] = [];

        /** @var \SimpleXMLElement $options */
        $options = $xml->options;
        if (isset( $options->option )) {
            foreach ($options->option as $option) {
                $data['options'][] = $this->transformSimpleXmlElementToData($option, 'text');
            }
        }

        return $data;
    }

    /**
     * @param \SimpleXMLElement $xmlElement
     * @param string            $valueName
     *
     * @return array|string
     */
    private function transformSimpleXmlElementToData($xmlElement, $valueName = 'value')
    {
        $data = array();

        $data[$valueName] = self::autoNumericCast((string) $xmlElement);

        foreach ($xmlElement->attributes() as $attrName => $attrValue) {
            if ($attrName === $valueName) {
                $attrName .= '_2';
            }
            $data[$attrName] = self::autoNumericCast((string) $attrValue);
        }

        if (count($data) === 1) {
            return $data[$valueName];
        }

        return $data;
    }

    /**
     * Return a value cast in int or float if value is numeric
     * Note : Only numeric value can be cast
     *
     * @param mixed $value
     *
     * @return mixed
     */
    private static function autoNumericCast($value)
    {
        if (is_numeric($value)) {
            if (ctype_digit($value)) {
                return (int) $value;
            }
            else {
                return (float) $value;
            }
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function getIndexColumn()
    {
        return 'sort_key_string';
    }

    /**
     * @inheritdoc
     */
    public function toStorageFieldDefinition(FieldDefinition $fieldDef, StorageFieldDefinition $storageDef)
    {
        // TODO: Implement toStorageFieldDefinition() method.
    }

    /**
     * @inheritdoc
     */
    public function toFieldDefinition(StorageFieldDefinition $storageDef, FieldDefinition $fieldDef)
    {
        // TODO: Implement toFieldDefinition() method.
    }
}
