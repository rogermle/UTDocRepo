<?php

namespace Utexas\DocRepo\Model\Tests;

use Utexas\DocRepo\Exception;
use Utexas\DocRepo\Model\File;

use Utexas\DocRepo\Model\Metadatum;

class FileTest extends \PHPUnit\Framework\TestCase
{
    protected $file;
    protected $xml_string = '<?xml version="1.0" encoding="UTF-8"?><file><id>7778</id><created-at>Feb 12, 2010 2:26:17 PM</created-at><updated-at>Feb 12, 2010 2:26:17 PM</updated-at><name>Gallery.html</name><mime-type>text/html</mime-type><size>27078</size><file-url>http://docrep.its.utexas.edu/docrepo-test/api/1.0/fileContents/7778</file-url><metadata /></file>';

    protected function setUp()
    {
        $xml_element = simplexml_load_string($this->xml_string);
        $file = new File();
        $this->file = $file->fromSimpleXMLElement($xml_element);
    }

    public function testFromSimpleXmlElement()
    {
        $this->assertTrue(is_a($this->file, 'Utexas\DocRepo\Model\File'));
    }

    public function testGetName()
    {
        $this->assertEquals($this->file->getName(), 'Gallery.html');
    }

    public function testGetId()
    {
        $this->assertEquals($this->file->getId(), '7778');
    }

    public function testGetSize()
    {
        $this->assertEquals($this->file->getSize(), '27078');
    }

    public function testGetMimeType()
    {
        $this->assertEquals($this->file->getMimeType(), 'text/html');
    }

    public function testGetCreateDate()
    {
        $this->assertEquals($this->file->getCreateDate(), 'Feb 12, 2010 2:26:17 PM');
    }

    public function testGetUpdateDate()
    {
        $this->assertEquals($this->file->getUpdateDate(), 'Feb 12, 2010 2:26:17 PM');
    }

    public function testGetFileContentsUrl()
    {
        $this->assertEquals(
            $this->file->getFileContentsUrl(),
            'http://docrep.its.utexas.edu/docrepo-test/api/1.0/fileContents/7778'
        );
    }

    public function testGetFileNameBeforeExtension()
    {
        $this->assertEquals($this->file->getFileNameBeforeExtension(), 'Gallery');
    }

    public function testGetFileNameExtension()
    {
        $this->assertEquals($this->file->getFileNameExtension(), 'html');
    }

    public function testSetName()
    {
        $this->file->setName('NewName.txt');
        $this->assertEquals($this->file->getName(), 'NewName.txt');
    }

    public function testSetMimeType()
    {
        $this->file->setMimeType('text/plain');
        $this->assertEquals($this->file->getMimeType(), 'text/plain');
    }

    public function testGetMetadata()
    {
        $metadata = $this->file->getMetadata();
        $this->assertEquals(array(), $metadata);
    }

    public function testAddMetadatum()
    {
        $metadatum = new Metadatum('foo', 'bar');
        $this->file->addMetadatum($metadatum);
        $this->assertTrue($this->file->getMetadata() == array($metadatum));
    }

    public function testSetMetadataAsArrayOfMetadatumObjects()
    {
        $metadata = array(
            new Metadatum('foo', 'bar'),
            new Metadatum('baz', 'bat'),
        );
        $this->file->setMetadata($metadata);
        $retrieved_metadata = $this->file->getMetadata();
        $this->assertTrue(count($retrieved_metadata) == 2);

        $m1 = $retrieved_metadata[0];
        $this->assertTrue($m1->getName() == 'foo');
        $this->assertTrue($m1->getValue() == 'bar');

        $m2 = $retrieved_metadata[1];
        $this->assertTrue($m2->getName() == 'baz');
        $this->assertTrue($m2->getValue() == 'bat');
    }

    public function testSetMetadataAsArrayOfNameValuePairs()
    {
        $metadata = array(
            'foo' => 'bar',
            'baz' => 'bat',
        );
        $this->file->setMetadata($metadata);
        $retrieved_metadata = $this->file->getMetadata();
        $this->assertTrue(count($retrieved_metadata) == 2);

        $m1 = $retrieved_metadata[0];
        $this->assertTrue($m1->getName() == 'foo');
        $this->assertTrue($m1->getValue() == 'bar');

        $m2 = $retrieved_metadata[1];
        $this->assertTrue($m2->getName() == 'baz');
        $this->assertTrue($m2->getValue() == 'bat');
    }

    public function testGetNamedMetadatum()
    {
        $metadatum = new Metadatum('foo', 'bar');
        $this->file->addMetadatum($metadatum);
        $retrieved_metadatum = $this->file->getNamedMetadatum('foo');
        $this->assertEquals($retrieved_metadatum, $metadatum);
    }

    /**
     * @expectedException Utexas\DocRepo\Exception
     * @expectedExceptionMessage Metadatum bar is not defined.
     */

    public function testGetBadMetadatum()
    {
        $metadatum = new Metadatum('foo', 'bar');
        $this->file->addMetadatum($metadatum);
        $retrieved_metadatum = $this->file->getNamedMetadatum('bar');
        $this->expectException('Utexas\DocRepo\Exception');
    }

    public function testRemoveMetadatum()
    {
        $metadatum = new Metadatum('foo', 'bar');
        $this->file->addMetadatum($metadatum);
        $this->assertTrue($this->file->getMetadata() == array($metadatum));
        $this->file->removeMetadatum('foo');
        $this->assertEquals(array(), $this->file->getMetadata());
    }

    /**
     * @expectedException Utexas\DocRepo\Exception
     */

    public function testRemoveBadMetadatum()
    {
        $this->file->removeMetadatum('badInput');
    }

    public function testToSimpleXmlElementReturnsSimpleXmlElement()
    {
        $xml_element = $this->file->toSimpleXmlELement();
        $this->assertTrue(is_a($xml_element, 'SimpleXMLElement'));
        return $xml_element;
    }

    /**
     * @depends testToSimpleXmlElementReturnsSimpleXmlElement
     */
    public function testSimpleXmlElementValuesMatchOriginalValues($xml_element)
    {
        $this->assertEquals($xml_element->getName(), 'file');
        $this->assertEquals((string)$xml_element->name, 'Gallery.html');
    }

    protected function tearDown()
    {
        unset($this->file);
    }
}
