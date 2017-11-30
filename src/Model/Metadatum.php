<?php
/**
 * Copyright (c) 2017 University of Texas at Austin
 */

namespace Ut\DocRepo\Model;

use SimpleXMLElement;

/**
 * Class Metadatum
 * @package Ut\DocRepo\Model
 */
class Metadatum
{
    /**
     * Internal data array
     *
     * @var array
     */
    private $data = array(
        'name' => null,
        'value' => null
    );

    /**
     * Constructor method.
     *
     * @param string $name
     * @param string $value
     * @return Metadatum
     */
    public function __construct($name, $value)
    {
        $this->data['name'] = (string)$name;
        $this->data['value'] = (string)$value;
        return $this;
    }

    /**
     * Converts a SimpleXMLElement object into a native object
     *
     * @param SimpleXMLElement $xml_element
     * @return Metadatum
     */
    public function fromSimpleXMLElement(SimpleXMLElement $xml_element)
    {
        $name = (string)$xml_element->name;
        $value = (string)$xml_element->value;
        $metadatum = new self($name, $value);
        return $metadatum;
    }

    /**
     * Get metadatum name
     *
     * @return string
     */
    public function getName()
    {
        return $this->data['name'];
    }

    /**
     * Get metadatum value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->data['value'];
    }

    /**
     * Set metadatum name
     *
     * @param string $name
     * @return Metadatum
     */
    public function setName($name)
    {
        $this->data['name'] = $name;
        return $this;
    }

    /**
     * Set metadatum value
     *
     * @param string $value
     * @return Metadatum
     */
    public function setValue($value)
    {
        $this->data['value'] = $value;
        return $this;
    }

    /**
     * Converts the metadatum object into a SimpleXMLElement object
     *
     * @return SimpleXMLElement
     */
    public function toSimpleXMLElement()
    {
        $xml_element = new SimpleXMLElement('<metadatum/>');
        foreach ($this->data as $name => $value) {
            if ($value) {
                $xml_element->addChild($name, $value);
            }
        }
        return $xml_element;
    }
}
