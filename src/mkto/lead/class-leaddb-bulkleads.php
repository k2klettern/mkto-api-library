<?php
/**
 * Created by PhpStorm.
 * User: Eric Zeidan
 * Date: 17/11/2016
 * Time: 12:26
 */

namespace Mkto\lead\bulkleads;

use Mkto\token;
use Mkto\logs;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class Bulkleads
 *
 * @author Eric Zeidan
 * @package Mkto\lead\bulkleads
 */
class Bulkleads
{
    /**
     *
     * @var Singleton
     */
    private static $instance;

    private $token;
    private $endPoint;

    public $id;//id of batch returned by import leads call
    public $file; //name of the file to import, required
    public $format;//file format, csv, tsv or ssv, required
    public $listId;//optional id of list to import to
    public $lookupField; //field to dedupe on, defaults to email
    public $functionname;

    public function __construct()
    {
        global $token;
        if(!isset($token)) {
            $this->token = \Mkto\token\GetAccessToken::getInstance()->getToken();
        } else {
            $this->token = $token;
        }
    }

    /**
     * @return Bulkleads|Singleton
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @return array
     *
     * EXAMPLE OF USE
     * -------------------------------------------------------------------
     * $status = new Mkto\lead\bulkleads\Bulkleads();
     * $status->id = 10;
     * print_r($status->getImportFailures());
     * -------------------------------------------------------------------
     */
    public function getImportFailures(){
        $url = "https://" . $this->endPoint . "/bulk/v1/leads/batch/" . $this->id . "/failures.json?access_token=" . $this->token;
        $ch = curl_init($url);
        curl_setopt($ch,  CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('accept: application/json',));
        $response = curl_exec($ch);
        \Mkto\logs\logs::getInstance()->proccessLog($response, $url);
        return $response;
    }

    /**
     * @return array
     *
     * EXAMPLE OF USE
     * -------------------------------------------------------------------
     * $import = new Mkto\lead\bulkleads\Bulkleads();
     * $import->id = 10;
     * print_r($import->getImportLeadsStatus());
     * -------------------------------------------------------------------
     */
    public function getImportLeadsStatus(){
        $url = "https://" . $this->endPoint . "/bulk/v1/leads/batch/" . $this->id . ".json?access_token=" . $this->token;
        $ch = curl_init($url);
        curl_setopt($ch,  CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('accept: application/json',));
        $response = curl_exec($ch);
        \Mkto\logs\logs::getInstance()->proccessLog($response, $url);
        return $response;
    }

    /**
     * @return mixed
     *
     * EXAMPLE OF USE
     * -------------------------------------------------------------------
     * $status = new Mkto\lead\bulkleads\Bulkleads();
     * $status->id = 1335;
     * print_r($status->getImportWarnings());
     * -------------------------------------------------------------------
     */
    public function getImportWarnings(){
        $url = "https://" . $this->endPoint . "/bulk/v1/leads/batch/" . $this->id . "/warnings.json?access_token=" . $this->token;
        $ch = curl_init($url);
        curl_setopt($ch,  CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('accept: application/json',));
        $response = curl_exec($ch);
        \Mkto\logs\logs::getInstance()->proccessLog($response, $url);
        return $response;
    }

    /**
     * @return array
     *
     * EXAMPLE OF USE
     * -------------------------------------------------------------------
     * $import = new Mkto\lead\bulkleads\Bulkleads();
     * $import->format = "csv";
     * $import->file = "path to file mktoseedlist.csv";
     * print_r($import->importLeads());
     * -------------------------------------------------------------------
     */
    public function importLeads(){
        $uploaded = $this->file;
        $file = MKTO_BASE_PATH . "/" . $uploaded;
        $filewithdir = str_replace($file['baseurl'], "", $uploaded); //peding url und path
        $realpath = realpath($file['basedir'] . $filewithdir);
        $url = "https://" . $this->endPoint . "/bulk/v1/leads.json?access_token=" . $this->token . "&format=" . $this->format;
        if (isset($this->listId)){
            $url .= "&listId=" . $this->listId;
        }
        if(isset($this->lookupField)){
            $url .= "&lookupField=" . $this->lookupField;
        }
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => array('file' => new \CURLFile($realpath, 'text/csv')),
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: multipart/form-data;",
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        \Mkto\logs\logs::getInstance()->proccessLog($response, $url);
        return $response;
    }

    /**
     * @return array
     *
     * EXAMPLE OF USE
     * -------------------------------------------------------------------
     * $export = new Mkto\lead\bulkleads\Bulkleads();
     * $export->fields = array('field1', 'field2', 'field3');
     * $export->format = "csv";
     * print_r($export->requestBulkJob());
     * -------------------------------------------------------------------
     */
    public function requestBulkJob() {
        $url = "https://" . $this->endPoint . "/bulk/v1/leads/export/create.json?access_token=" . $this->token;
	    $requestBody = $this->bodyBuilderRequest();
        $ch = curl_init($url);
        curl_setopt($ch,  CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('accept: application/json','Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
        curl_getinfo($ch);
        $response = curl_exec($ch);
        \Mkto\logs\logs::getInstance()->proccessLog($response, $url);
        return $response;
    }

    /**
     * @return array
     *
     *
     * $enqueue = new Mkto\lead\bulkleads\Bulkleads();
     * $enqueue->id = 1002;
     * print_r($enqueue->enqueueBulkJob());
     *
     */
    public function enqueueBulkJob() {
        $url = "https://" . $this->endPoint . "/bulk/v1/leads/export/" . $this->id . "/enqueue.json?access_token=" . $this->token;
        $requestBody = $this->bodyBuilderRequest();
        $ch = curl_init($url);
        curl_setopt($ch,  CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('accept: application/json','Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
        curl_getinfo($ch);
        $response = curl_exec($ch);
        \Mkto\logs\logs::getInstance()->proccessLog($response, $url);
        return $response;
    }


    /**
     * @return array
     *
     * $status = new Mkto\lead\bulkleads\Bulkleads();
     * $status->id = 1335;
     * print_r($status->statusBulkJob());
     *
     */
    public function statusBulkJob(){
        $url = "https://" . $this->endPoint . "/bulk/v1/leads/export/" . $this->id . "/status.json?access_token=" . $this->token;
        $ch = curl_init($url);
        curl_setopt($ch,  CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('accept: application/json',));
        $response = curl_exec($ch);
        \Mkto\logs\logs::getInstance()->proccessLog($response, $url);
        return $response;
    }

    /**
     * @return array
     *
     * $file = new Mkto\lead\bulkleads\Bulkleads();
     * $file->id = 1335;
     * print_r($status->downloadBulkJob());
     *
     */
    public function downloadBulkJob($filename = null){
        if(!$filename) {
            $filename = "file_" . $this->id . "_" . date('YMD_his') . ".csv";
        }
        $directory = MKTO_UPLOAD_DIR;
        $output_filename = $directory['path'] ."/". $filename;
        $filenamewd = MKTO_BASE_PATH . $directory['subdir'] ."/" . $filename;
        $url = "https://" . $this->endPoint . "/bulk/v1/leads/export/" . $this->id . "/file.json?access_token=" . $this->token;
        $response = file_get_contents($url);

        if($fp = fopen($output_filename, 'wa+')) {
        fwrite($fp, $response);
        fclose($fp);
	} else {
		return false;
	}
        if(!$response) {
            return false;
        } else {
            return $filenamewd;
        }
    }

    /**
     * @return mixed|string|void
     */
    private function bodyBuilderRequest(){
        $body = new \stdClass();
        if (isset($this->fields)) {
            $body->fields = $this->fields;
        }
        if (isset($this->format)) {
            $body->format = $this->format;
        }
        if (isset($this->columnHeaderNames)) {
            $body->columnHeaderNames = $this->columnHeaderNames;
        }
        if (isset($this->filter)){
            $body->filter = $this->filter;
        }
        $json = json_encode($body);
        return $json;
    }
    /**
     * @return array
     */
    private function bodyBuilder(){
        $local_file = $this->file; //path to a local file on your server
        $boundary = substr(str_shuffle(strtolower(sha1(rand() . time() . MKTO_SALT_STRING))),0, 24);
        $headers  = array(
            'accept' => 'application/json', 'content-type' => 'multipart/form-data; boundary=' . $boundary . '; charset=UTF-8'
        );
        $payload = '';
        // Upload the file
        if ( $local_file ) {
            $payload .= '--' . $boundary;
            $payload .= "\r\n";
            $payload .= 'Content-Disposition: form-data; name="' . 'content' .
                '"; filename="' . basename( $local_file ) . '"' . "\r\n";
            //        $payload .= 'Content-Type: image/jpeg' . "\r\n";
            $payload .= "\r\n";
            $payload .= file_get_contents( $local_file );
            $payload .= "\r\n";
        }
        $payload .= '--' . $boundary . '--';
        $args = array(
            'method' => 'POST',
            'timeout' => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => $headers,
            'body' => $payload,
            'cookies' => array()
        );
        return $args;
    }

    /**
     * @return string|void
     *
     * @param functionname need to be set
     *
     * See token for example of use
     */
    public function get_reflection() {
        $reflector = new \ReflectionClass($this);
        // to get the Class DocBlock
        $texto = __($reflector->getDocComment());
        // to get the Method DocBlock
        $texto .= __($reflector->getMethod($this->functionname)->getDocComment());
        return $texto;
    }
}

