<?php
/**
 * Copyright (c) 2017 University of Texas at Austin
 */

namespace Utexas\DocRepo\Model;

use Utexas\DocRepo\Exception;
use SimpleXMLElement;

/**
 * File Model Class
 * @package Ut\DocRepo\Model
 * @author Roger Le <roger.le@austin.utexas.edu>
 */
class File
{
    /**
     * Internal data array
     *
     * @var array
     */
    private $data = array(
        'id' => null,
        'name' => null,
        'file-url' => null,
        'created-at' => null,
        'mime-type' => null,
        'size' => null,
        'updated-at' => null
    );

    /**
     * Internal metadata array
     *
     * @var array
     */
    private $metadata = array();

    /**
     * Add a metadatum to the file's metadata. Metadatum can be passed in either
     * as a Ut\DocRepo\Model\Metadatum object or an array with one name-value pair.
     *
     * @param Metadatum $metadatum
     * @return File
     *
     * @throws Exception
     */
    public function addMetadatum($metadatum)
    {
        if (is_array($metadatum) && count($metadatum) == 1) {
            foreach ($metadatum as $name => $value) {
                $metadatum = new Metadatum($name, $value);
            }
        }

        if (!is_a($metadatum, 'Utexas\DocRepo\Model\Metadatum')) {
            throw new Exception(
                'Metadatum argument must be passed as either an array with a name-value pair,
             or as a Ut\DocRepo\Model\Metadatum object.'
            );
        }

        $this->metadata[] = $metadatum;
        return $this;
    }

    /**
     * Converts SimpleXMLElement object to native file object
     *
     * @param SimpleXMLElement $xml_element
     * @return File
     *
     * @throws Exception
     */
    public function fromSimpleXMLElement(SimpleXMLElement $xml_element)
    {
        $file = new self();
        foreach ($this->data as $name => $value) {
            if (isset($xml_element->{$name})) {
                if ($name == 'id') {
                    $file->data[$name] = (int)$xml_element->{$name};
                } else {
                    $file->data[$name] = (string)$xml_element->{$name};
                }
            }
        }
        foreach ($xml_element->metadata->children() as $metadata_child) {
            $file->addMetadatum(new Metadatum((string)$metadata_child->name, (string)$metadata_child->value));
        }
        return $file;
    }

    /**
     * Get file creation date
     *
     * @return string
     */
    public function getCreateDate()
    {
        return $this->data['created-at'];
    }

    /**
     * Get URL for file contents
     *
     * @return string
     */
    public function getFileContentsURL()
    {
        return $this->data['file-url'];
    }

    /**
     * Get filename before the extension
     *
     * @return string
     */
    public function getFileNameBeforeExtension()
    {
        $name = $this->getName();
        $last_dot = strripos($name, '.');
        return substr($name, 0, $last_dot);
    }

    /**
     * Get filename extension
     *
     * @return string
     */
    public function getFileNameExtension()
    {
        $name = $this->getName();
        $last_dot = strripos($name, '.');
        return substr($name, $last_dot + 1);
    }

    /**
     * Get file ID
     *
     * @return int
     */
    public function getId()
    {
        return (int)$this->data['id'];
    }

    /**
     * Get metadata array
     *
     * @return array
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Get file mime-type
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->data['mime-type'];
    }

    /**
     * Get filename
     *
     * @return string
     */
    public function getName()
    {
        return $this->data['name'];
    }

    /**
     * Get a metadatum by name
     *
     * @param string $name
     * @return string
     * @throws Exception
     */
    public function getNamedMetadatum($name)
    {
        foreach ($this->metadata as $metadatum) {
            if ($metadatum->getName() == $name) {
                return $metadatum;
            }
        }
        throw new Exception('Metadatum ' . $name . ' is not defined.');
    }

    /**
     * Get file size in kilobytes
     * @return string
     */
    public function getSize()
    {
        return $this->data['size'];
    }

    /**
     * Get timestamp for last file update (e.g. "Jan 15, 2010 4:58:15 PM")
     *
     * @return string
     */
    public function getUpdateDate()
    {
        return $this->data['updated-at'];
    }

    /**
     * Remove a metadatum by name. Throws an exception if there is no metadatum
     * with the specified name.
     *
     * @param string $metadatum_name
     * @return File
     * @throws Exception
     */
    public function removeMetadatum($metadatum_name)
    {
        $x = 0;
        foreach ($this->metadata as $metadatum) {
            if ($metadatum->getName() == $metadatum_name) {
                unset($this->metadata[$x]);
                return $this;
            }
            $x++;
        }
        throw new Exception('Metadatum ' . $metadatum_name . ' is not defined.');
    }

    /**
     * Set the file's metadata, using an array of either name-value pairs
     * or of metadatum objects. This method overwrites any previous metadata
     * on the file object.
     *
     * @param array $metadata An array of either name-value pairs or of Ut_DocRepo_Model_Metadatum objects.
     * @return File
     */
    public function setMetadata(array $metadata)
    {
        $metadata_as_objs = array();
        foreach ($metadata as $key => $value) {
            if (!is_a($value, 'Utexas\DocRepo\Model\Metadatum')) {
                $metadatum = new Metadatum((string)$key, (string)$value);
            } else {
                $metadatum = $value;
            }
            $metadata_as_objs[] = $metadatum;
        }
        $this->metadata = $metadata_as_objs;
        return $this;
    }

    /**
     * Set the file's mime-type
     *
     * @param string $mime_type
     * @return File
     */
    public function setMimeType($mime_type)
    {
        $this->data['mime-type'] = $mime_type;
        return $this;
    }

    /**
     * Set the filename.
     *
     * @param string $name
     * @return File
     */
    public function setName($name)
    {
        $this->data['name'] = $name;
        return $this;
    }

    /**
     * Converts the file object into a SimpleXMLElement object
     *
     * @return SimpleXMLElement
     */
    public function toSimpleXMLELement()
    {
        $xml_element = new SimpleXMLElement('<file/>');
        foreach ($this->data as $name => $value) {
            if ($value) {
                $xml_element->addChild($name, $value);
            }
        }

        $metadata = $xml_element->addChild('metadata');
        foreach ($this->getMetadata() as $metadatum) {
            $md_node = $metadata->addChild('metadatum');
            $md_node->addChild('name', $metadatum->getName());
            $md_node->addChild('value', $metadatum->getValue());
        }
        return $xml_element;
    }
}
