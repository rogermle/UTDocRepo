<?php
/**
 * PHP REST client class for interfacing with UT Austin Document Repository.
 * Based on the UT_DocManager class by Paul Grotevant at ITS Applications
 * and Geoff Boyd at UT Liberal Arts ITS.
 * Many thanks to Chris Pittman and Dory Weiss for their assistance.
 * Copyright (c) 2017 University of Texas at Austin
 */

namespace Ut\DocRepo\Rest;

use Ut\DocRepo\Exception\AuthorizationError;
use Ut\DocRepo\Exception\InternalError;
use Ut\DocRepo\Exception\InvalidRequest;
use Ut\DocRepo\Exception\InvalidSignature;
use Ut\DocRepo\Exception\Malware;
use Ut\DocRepo\Exception\ResourceNotFound;
use Ut\DocRepo\Exception;
use Ut\DocRepo\Model\File;
use Ut\DocRepo\Model\Metadatum;

/**
 * UT Austin/ITS Document Repository Client
 * @package Ut\Docrepo\Rest
 */
class Client
{
    /**
     * Repository URL prefix
     * @var string
     */
    private $url_prefix;
    /**
     * User agent string
     * @var string
     */
    private $user_agent;
    /**
     * Repository name
     * @var string
     */
    private $repo_name;
    /**
     * Repository password
     * @var string
     */
    private $repo_password;
    /**
     * cURL Handle
     * @var string
     */
    private $ch;
    /**
     * Document Repository API Version Number
     * @var string
     */
    private $api_version = '1.0';


    public function __construct($_url_prefix, $_user_agent, $_repo_name, $_repo_password)
    {
        $this->url_prefix = $_url_prefix;
        $this->user_agent = $_user_agent;
        $this->repo_name = $_repo_name;
        $this->repo_password = $_repo_password;

        // Initialize HTTP client
        $this->initHttpClient();
    }

    public function initHttpClient()
    {
        if (null == $this->ch) {
            $this->ch = curl_init();
        }
        curl_setopt($this->ch, CURLOPT_USERAGENT, $this->user_agent);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($this->ch, CURLOPT_POST, false);
        curl_setopt($this->ch, CURLOPT_URL, '');
        curl_setopt($this->ch, CURLOPT_BINARYTRANSFER, false);
        curl_setopt($this->ch, CURLOPT_PUT, false);
        curl_setopt($this->ch, CURLOPT_UPLOAD, false);
    }

    /**
     * Create a new file in the repository.
     *
     * @param File $file File to create.
     * @return File
     *
     */
    public function add(File $file)
    {
        $post_body_xml = $file->toSimpleXMLElement()->asXml();
        $http_verb = 'POST';
        $relative_uri = '/' . $this->api_version . '/files';

        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($this->ch, CURLOPT_URL, $this->url_prefix . $relative_uri);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post_body_xml);

        $response = $this->makeHttpRequest($http_verb, $relative_uri);
        $return_file = new File();

        return $return_file->fromSimpleXMLElement($response);
    }

    /**
     * Add a metadatum (name/value pair) to a file.
     *
     * @param int $file_id File ID to add metadatum to.
     * @param Metadatum|array $metadatum Metadatum object or array containing name/value pair.
     * @return Metadatum
     * @throws Exception
     */
    public function addMetadatum($file_id, $metadatum)
    {
        // Check whether the metadatum was passed as an array or an object
        if (is_array($metadatum) && count($metadatum) == 1) {
            foreach ($metadatum as $name => $value) {
                $metadatum = new Metadatum($name, $value);
            }
        } elseif (!is_a($metadatum, 'Ut\DocRepo\Model\Metadatum')) {
            throw new Exception('Metadatum argument must be passed as either an array with a name-value pair, or as a Ut\DocRepo\Model\Metadatum object.');
        }

        $post_body_xml = $metadatum->toSimpleXMLElement()->asXml();

        $http_verb = 'POST';
        $relative_uri = '/' . $this->api_version . '/files/' . $file_id . '/metadata';

        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($this->ch, CURLOPT_URL, $this->url_prefix . $relative_uri);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post_body_xml);

        $response = $this->makeHttpRequest($http_verb, $relative_uri);
        $return_metadatum = new Metadatum($response->name, $response->value);

        return $return_metadatum->fromSimpleXMLElement($response);
    }

    /**
     * Calculate HMAC signature for secure HTTP connection.
     *
     * @param string $signing_key
     * @param string $pattern
     * @return string
     */
    public function calculateHmacSignature($signing_key, $pattern)
    {
        return hash_hmac('sha1', $pattern, $signing_key);
    }

    /**
     * Creates HTTP request and executes cURL action. Returns either
     * a string (in the case of file contents) or a SimpleXML object.
     * The client's cURL handle is reset at the end of this request,
     * before it can be used for a subsequent request.
     *
     * @param string $http_verb HTTP verb of request action.
     * @param string $relative_uri Relative URI of request action.
     * @param string $date Date/time stamp of request action in RFC2616 format.
     * @param bool $xml_response Does the request expect an XML response?
     *
     * @throws InvalidSignature
     * @throws InternalError
     * @throws AuthorizationError
     * @throws ResourceNotFound
     * @throws InvalidRequest
     * @throws Malware
     * @throws Exception
     * @return mixed
     */

    private function makeHttpRequest($http_verb, $relative_uri, $date = null, $xml_response = true)
    {
        if (null == $date) {
            $date = gmdate('D, d M Y H:i:s T');
        }
        $pattern = "$http_verb\n$relative_uri\n$date";
        // print("$pattern \n");
        $headers = array(
            'X-DocRepository-Date: ' . $date,
            'Authorization: docrepository ' . $this->repo_name . ':' . $this->calculateHmacSignature(
                $this->repo_password,
                $pattern
            ),
        );
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);

        $response_string = curl_exec($this->ch);
        // print("Hello from Sunil");
        if ($xml_response) {
            $return_val = simplexml_load_string($response_string);

            // Check if root element is 'error'
            if ($return_val->getName() == 'error') {
                switch ($return_val->code) {
                    case ('InvalidSignature'):
                        throw new InvalidSignature($return_val->message);
                        break;
                    case ('InternalError'):
                        throw new InternalError($return_val->message);
                        break;
                    case ('AuthorizationError'):
                        throw new AuthorizationError($return_val->message);
                        break;
                    case ('ResourceNotFound'):
                        throw new ResourceNotFound($return_val->message);
                        break;
                    case ('InvalidRequest'):
                        throw new InvalidRequest($return_val->message);
                        break;
                    case ('Malware'):
                        throw new Malware($return_val->message);
                        break;
                    default:
                        throw new Exception($return_val->message);
                }
            }
        } else {
            $return_val = $response_string;
        }

        // Re-initialize HTTP client for next request
        $this->initHttpClient();

        return $return_val;
    }

    /**
     * Copies a file, including binary contents and metadata.
     *
     * @param $file_id File ID to be copied.
     * @return File
     */
    public function copyFile($file_id)
    {
        $http_verb = 'GET';
        $relative_uri = '/' . $this->api_version . '/files/' . $file_id . '/copy';

        curl_setopt($this->ch, CURLOPT_URL, $this->url_prefix . $relative_uri);

        $response = $this->makeHttpRequest($http_verb, $relative_uri);

        $return_file = new File();
        return $return_file->fromSimpleXMLElement($response);
    }

    /**
     * Deletes a file.
     *
     * @param $file_id File ID to be deleted.
     * @return void
     *
     * @throws Exception
     */
    public function delete($file_id)
    {
        $http_verb = 'DELETE';
        $relative_uri = '/' . $this->api_version . '/files/' . $file_id;

        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($this->ch, CURLOPT_URL, $this->url_prefix . $relative_uri);

        $response = $this->makeHttpRequest($http_verb, $relative_uri);
    }

    /**
     * Deletes a metadatum from a file.
     *
     * @param int $file_id File ID.
     * @param string $metadatum_name Name of metadatum to be deleted.
     * @return void
     */
    public function deleteMetadatum($file_id, $metadatum_name)
    {
        $http_verb = 'DELETE';
        $relative_uri = '/' . $this->api_version . '/files/' . $file_id . '/metadata/' . $metadatum_name;

        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($this->ch, CURLOPT_URL, $this->url_prefix . $relative_uri);

        $response = $this->makeHttpRequest($http_verb, $relative_uri);
    }

    /**
     * Retrieves a file (not including binary contents).
     *
     * @param string $file_id File ID to retrieve.
     * @return File
     *
     * @throws Exception
     */
    public function get($file_id)
    {
        $http_verb = 'GET';
        $relative_uri = '/' . $this->api_version . '/files/' . $file_id;

        curl_setopt($this->ch, CURLOPT_URL, $this->url_prefix . $relative_uri);

        $response = $this->makeHttpRequest($http_verb, $relative_uri);
        $return_file = new File();
        return $return_file->fromSimpleXMLElement($response);
    }

    /**
     * Retrieves a file's binary contents.
     *
     * @param string $url URL of binary contents.
     * @return binary
     */
    public function getFileContents($url)
    {
        $http_verb = 'GET';
        $relative_uri = substr($url, strlen($this->url_prefix));

        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_BINARYTRANSFER, true);

        $response = $this->makeHttpRequest($http_verb, $relative_uri, null, false);
        return $response;
    }

    /**
     * Retrieves a file's metadata.
     *
     * @param int $file_id File ID to retrieve metdata for.
     * @return array
     */
    public function getMetadata($file_id)
    {
        $http_verb = 'GET';
        $relative_uri = '/' . $this->api_version . '/files/' . $file_id . '/metadata';

        curl_setopt($this->ch, CURLOPT_URL, $this->url_prefix . $relative_uri);

        $response = $this->makeHttpRequest($http_verb, $relative_uri);

        $metadata = array();
        foreach ($response->metadatum as $metadatum_node) {
            $metadata[] = new Metadatum((string)$metadatum_node->name, (string)$metadatum_node->value);
        }
        return $metadata;
    }

    /**
     * Searches for files based on a metadata query.
     *
     * @param string $query Search query.
     * @return array
     */
    public function metadataSearch($query)
    {
        $http_verb = 'GET';
        $relative_uri = '/' . $this->api_version . '/search';

        curl_setopt($this->ch, CURLOPT_URL, $this->url_prefix . $relative_uri . '?query=' . urlencode($query));

        $response = $this->makeHttpRequest($http_verb, $relative_uri);

        $files = array();
        foreach ($response->files->children() as $file_node) {
            $file = new File();
            $files[] = $file->fromSimpleXMLElement($file_node);
        }
        return $files;
    }

    /**
     * Replaces a file's existing values with new file object.
     *
     * @param File $file File to replace.
     * @return File
     */
    public function modify(File $file)
    {
        $post_body_xml = $file->toSimpleXMLElement()->asXml();
        $http_verb = 'POST';
        $relative_uri = '/' . $this->api_version . '/files/' . $file->getId();

        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($this->ch, CURLOPT_URL, $this->url_prefix . $relative_uri);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post_body_xml);

        $response = $this->makeHttpRequest($http_verb, $relative_uri);
        $return_file = new File();
        return $return_file->fromSimpleXMLElement($response);
    }

    /**
     * Set binary contents of file.
     *
     * @param string $url URL for file's binary contents.
     * @param $in Input stream.
     * @return void
     */
    public function setFileContents($url, $in)
    {
        $http_verb = 'PUT';
        $relative_uri = substr($url, strlen($this->url_prefix));

        curl_setopt($this->ch, CURLOPT_PUT, true);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_INFILE, $in);
        curl_setopt($this->ch, CURLOPT_UPLOAD, true);

        $response = $this->makeHttpRequest($http_verb, $relative_uri);
    }

    /**
     * Destructor function
     *
     * @return void
     */
    public function __destruct()
    {
        curl_close($this->ch);
    }
}
