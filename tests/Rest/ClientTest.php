<?php

namespace Ut\DocRepo\Rest\Tests;

use Ut\DocRepo\Rest\Client;
use Ut\DocRepo\Model\File;
use Ut\DocRepo\Model\Metadatum;

class ClientTest extends \PHPUnit\Framework\TestCase
{
    protected $config;
    protected $client;
    protected $file_id;

    public function __construct()
    {
        $this->config = parse_ini_file(CONFIG_FILE);
        parent::__construct();
    }

    public function setUp()
    {
        $this->client = new Client(
            $this->config['url_prefix'],
            $this->config['user_agent'],
            $this->config['repo_name'],
            $this->config['repo_password']
        );
    }

    public function testConstructor()
    {
        $this->assertInternalType('object', $this->client);
    }

    public function testAdd()
    {
        $file = new File();
        $file->setName('test_file.txt');
        $file->setMimeType('text/plain');
        $file = $this->client->add($file);

        $this->assertInternalType('integer', $file->getId());
        $this->assertGreaterThan(0, $file->getId());
        $this->assertEquals($file->getName(), 'test_file.txt');
        $this->assertEquals($file->getMimeType(), 'text/plain');
        $this->assertNotNull($file->getCreateDate());
        $this->assertNotNull($file->getUpdateDate());

        return $file->getId();
    }

    /**
     * @depends testAdd
     */
    public function testAddMetadataAsObject($file_id)
    {
        $name = 'timestamp1';
        $value =  (string)time();
        $metadatum = new Metadatum($name, $value);
        $return_metadatum = $this->client->addMetadatum($file_id, $metadatum);

        $this->assertTrue(is_a($return_metadatum, 'Ut\DocRepo\Model\Metadatum'));
        $this->assertEquals($name, $return_metadatum->getName());
        $this->assertEquals($value, $return_metadatum->getValue());

        return $file_id;
    }

    /**
     * @depends testAddMetadataAsObject
     */
    public function testAddMetadataAsArray($file_id)
    {
        $name = 'timestamp2';
        $value =  (string)time();
        $metadatum = array($name => $value);
        $return_metadatum = $this->client->addMetadatum($file_id, $metadatum);

        $this->assertTrue(is_a($return_metadatum, 'Ut\DocRepo\Model\Metadatum'));
        $this->assertEquals($name, $return_metadatum->getName());
        $this->assertEquals($value, $return_metadatum->getValue());

        return $file_id;
    }

    /**
     * @depends testAddMetadataAsArray
     */
    public function testDeleteMetadata($file_id)
    {
        // Delete the metadatum called 'timestamp1'
        $this->client->deleteMetadatum($file_id, 'timestamp1');

        // Get the updated metadata for the file
        $metadata = $this->client->getMetadata($file_id);

        // The should only be one metadatum left, called 'timestamp2'
        $this->assertEquals(1, count($metadata));

        $metadatum = $metadata[0];

        $this->assertEquals('timestamp2', $metadatum->getName());

        return $file_id;
    }

    /**
     * @depends testDeleteMetadata
     */
    public function testSetFileContents($file_id)
    {
        $file = $this->client->get($file_id);
        $contents_url = $file->getFileContentsUrl();

        $test_file_handle = fopen(TEST_FILE, 'r');
        $test_file_contents = file_get_contents(TEST_FILE);

        $this->client->setFileContents($contents_url, $test_file_handle);

        $retrieved_contents = $this->client->getFileContents($contents_url);

        $this->assertSame($test_file_contents, $retrieved_contents);

        return $file_id;
    }

    /**
     * @depends testSetFileContents
     */
    public function testReplaceFileContents($file_id)
    {
        $file = $this->client->get($file_id);
        $contents_url = $file->getFileContentsUrl();

        $modified_file_handle = fopen(TEST_FILE_MODIFIED, 'r');
        $modified_file_contents = file_get_contents(TEST_FILE_MODIFIED);

        $this->client->setFileContents($contents_url, $modified_file_handle);

        $retrieved_contents = $this->client->getFileContents($contents_url);

        $this->assertSame($modified_file_contents, $retrieved_contents);

        return $file_id;
    }
    /**
     * @depends testReplaceFileContents
     */
    public function testCopyFile($file_id)
    {
        $file = $this->client->get($file_id);
        $copied_file = $this->client->copyFile($file_id);

        $this->assertNotEquals($file->getId(), $copied_file->getId());
        $this->assertEquals($file->getName(), $copied_file->getName());
        $this->assertEquals($file->getSize(), $copied_file->getSize());
        $this->assertEquals($file->getMimeType(), $copied_file->getMimeType());
        $this->assertEquals($this->client->getFileContents($file->getFileContentsUrl()), $this->client->getFileContents($copied_file->getFileContentsUrl()));

        $this->client->delete($copied_file->getId());

        return $file_id;
    }

    /**
     * @depends testCopyFile
     */
    public function testMetadataSearch($file_id)
    {
        $file = $this->client->get($file_id);
        $this->client->addMetadatum($file_id, new Metadatum('fileId', $file_id));

        $search_results = $this->client->metadataSearch("fileId=$file_id");
        $first_result = $search_results[0];
        $this->assertTrue(is_array($search_results));
        $this->assertEquals(1, count($search_results));
        $this->assertEquals($file->getId(), $first_result->getId());
        $this->assertEquals($file->getName(), $first_result->getName());
        $this->assertEquals($file->getSize(), $first_result->getSize());
        $this->assertEquals($file->getMimeType(), $first_result->getMimeType());

        return $file_id;
    }
    /**
     * @depends testMetadataSearch
     */
    public function testDelete($file_id)
    {
        $this->expectException('Ut\DocRepo\Exception\ResourceNotFound');
        $this->client->delete($file_id);
        $file = $this->client->get($file_id);
    }
}
