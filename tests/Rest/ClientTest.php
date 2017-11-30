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
}
