<?php

namespace Utexas\DocRepo\Model\Tests;

use Utexas\DocRepo\Model\Metadatum;

class MetadatumTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return Metadatum
     */
    public function testConstructorReturnsInstance()
    {
        $metadatum = new Metadatum('foo', 'bar');
        $this->assertTrue(is_a($metadatum, 'Utexas\DocRepo\Model\Metadatum'));
        return $metadatum;
    }

    /**
     * @depends testConstructorReturnsInstance
     * @param $metadatum
     * @return Metadatum
     */
    public function testGetNameReturnsName($metadatum)
    {
        $this->assertEquals($metadatum->getName(), 'foo');
        return $metadatum;
    }

    /**
     * @depends testGetNameReturnsName
     * @param $metadatum
     * @return Metadatum
     */
    public function testGetValueReturnsValue($metadatum)
    {
        $this->assertEquals($metadatum->getValue(), 'bar');
        return $metadatum;
    }

    /**
     * @depends testGetValueReturnsValue
     * @param $metadatum
     * @return Metadatum
     */
    public function testSetNameChangesName($metadatum)
    {
        $metadatum->setName('fu');
        $this->assertEquals($metadatum->getName(), 'fu');
        return $metadatum;
    }

    /**
     * @depends testSetNameChangesName
     * @param $metadatum
     * @return Metadatum
     */
    public function testSetValueChangesValue($metadatum)
    {
        $metadatum->setValue('bear');
        $this->assertEquals($metadatum->getValue(), 'bear');
        return $metadatum;
    }

    /**
     * @depends testSetValueChangesValue
     * @param $metadatum
     * @return SimpleXMLElement
     */
    public function testToSimpleXmlElementReturnsSimpleXmlElement($metadatum)
    {
        $xml_element = $metadatum->toSimpleXmlELement();
        $this->assertTrue(is_a($xml_element, 'SimpleXMLElement'));
        return $xml_element;
    }

    /**
     * @depends testToSimpleXmlElementReturnsSimpleXmlElement
     * @return void
     */
    public function testSimpleXmlElementValuesMatchOriginalValues($xml_element)
    {
        $this->assertEquals($xml_element->getName(), 'metadatum');
        $this->assertEquals((string)$xml_element->name, 'fu');
        $this->assertEquals((string)$xml_element->value, 'bear');
    }
}
