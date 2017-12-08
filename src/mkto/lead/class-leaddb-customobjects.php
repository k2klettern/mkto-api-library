<?php
/**
 * Created by PhpStorm.
 * User: Eric Zeidan
 * Date: 17/11/2016
 * Time: 13:38
 */

namespace Mkto\lead\customobjects;

use Mkto\token;
use Mkto\logs;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class Customobjects
 *
 * @author Eric Zeidan
 * @package Mkto\lead\customobjects
 */
class Customobjects
{
    /**
     *
     * @var Singleton
     */
    private static $instance;

    private $token;
    private $endPoint;
    private $passcsv;

    public $names;//array of custom object names to list
    public $name;//name of Custom Object type to delete
    public $input;//array of objects with fields to dedupeby
    public $idfields; //array of marketGUIDs
    public $dedupeBy; //dedupe field, dedupeFields or idField
    public $filterType;//filter field, one of describe SearchableFields
    public $filterValues;//array of filtered values
    public $fields;//optional array of fields to retrieve
    public $nextPageToken;//token for paging
    public $batchSize;//max 300, default 300
    public $action;//action to take, createOnly, updateOnly, createOrUpdate, default createOrUpdate
    public $filename;
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
     * @return Customobjects|Singleton
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
     * $list = new Mkto\lead\customobjects\Customobjects();
     * print_r($list->listOfCustomObjects());
     * -------------------------------------------------------------------
     */
    public function listOfCustomObjects(){
        $url = "https://" . $this->endPoint . "/rest/v1/customobjects.json?access_token=" . $this->token;
        if (isset($this->names)){
            $url .= "&names=" . $this->csvString($this->names);
        }
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
     * $import = new Mkto\lead\bulkleads\Customobjects();
     * $import->object = 'car_c';
     * $import->id = 10;
     * print_r($import->getImportCustomObjectsStatus());
     * -------------------------------------------------------------------
     */
    public function getImportCustomObjectsStatus(){
        $url = "https://" . $this->endPoint . "/bulk/v1/customobjects/" . $this->object . "/import/" . $this->id . "/status.json?access_token=" . $this->token;
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
     * $import = new Mkto\lead\bulkleads\Customobjects();
     * $import->object = 'car_c';
     * $import->id = 10;
     * print_r($import->getImportCustomObjectsFailures());
     * -------------------------------------------------------------------
     */
    public function getImportCustomObjectsFailures(){
        $url = "https://" . $this->endPoint . "/bulk/v1/customobjects/" . $this->object . "/import/" . $this->id . "/failures.json?access_token=" . $this->token;
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
     * $import = new Mkto\lead\bulkleads\Customobjects();
     * $import->object = 'car_c';
     * $import->id = 10;
     * print_r($import->getImportCustomObjectsWarnings());
     * -------------------------------------------------------------------
     */
    public function getImportCustomObjectsWarnings(){
        $url = "https://" . $this->endPoint . "/bulk/v1/customobjects/" . $this->object . "/import/" . $this->id . "/warnings.json?access_token=" . $this->token;
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
     * $delete = new Mkto\lead\customobjects\Customobjects();
     * $pet1 = new stdClass();
     * $pet1->ownerId = 1;
     * $pet1->name = "Fido";
     * $delete->input = [$pet1];
     * $delete->dedupeBy = "dedupeFields";
     * print_r($delete->deleteCustomObjects());
     * -------------------------------------------------------------------
     */
    public function deleteCustomObjects(){
        $url = "https://" . $this->endPoint . "/rest/v1/customobjects/" . $this->name . "/delete.json?access_token=" . $this->token;
        $requestBody = $this->bodyBuilder();
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
     * EXAMPLE OF USE
     * -------------------------------------------------------------------
     * $describe = new Mkto\lead\customobjects\Customobjects();
     * $describe->name = "pet";
     * print_r($describe->describeCustomObjects());
     * -------------------------------------------------------------------
     */
    public function describeCustomObjects(){
        $url = "https://" . $this->endPoint . "/rest/v1/customobjects/" . $this->name . "/describe.json?access_token=" . $this->token;
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
     * $pets = new Mkto\lead\customobjects\Customobjects();
     * $pets->name = "pet_c";
     * $pets->filterType = "idField";
     * $pets->filterValues = array("dff23271-f996-47d7-984f-f2676861b5fa");
     * print_r($pets->getCustomObjects());
     * -------------------------------------------------------------------
     */
    public function getCustomObjects(){
        $url = "https://" . $this->endPoint . "/rest/v1/customobjects/" . $this->name . ".json?access_token=" . $this->token . "&filterType=" . $this->filterType
            . "&filterValues=" . $this->csvString($this->filterValues);
        if (isset($this->fields)){
            $url .= "&fields=" . $this->csvString($this->fields);
        }
        if (isset($this->nextPageToken)){
            $url .= "&nextPageToken=" . $this->nextPageToken;
        }
        if (isset($this->batchSize)){
            $url .= "&batchSize=" . $this->batchSize;
        }
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
     * $upsert = new Mkto\lead\customobjects\Customobjects();
     * $upsert->name = "pet";
     * $pet1 = new stdClass();
     * $pet1->name = "Fido";
     * $pet1->ownerId = 1;
     * $upsert->input = [$pet1];
     * $upsert->dedupeBy = "dedupeFields";
     * print_r($upsert->syncCustomObject());
     * -------------------------------------------------------------------
     */
    public function syncCustomObject(){
        $url = "https://" . $this->endPoint . "/rest/v1/customobjects/" . $this->name . ".json?access_token=" . $this->token;
        $requestBody = $this->bodyBuilderSync();
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
     * @return mixed
     *
     * EXAMPLE OF USE
     * --------------------------------------------------------------------------------
     * $bulk = new Mkto\lead\customobjects\Customobjects();
     * $bulk->name = "car_c";
     * $bulk->filename = get_home_url() . "/wp-content/uploads/2017/03/customobject.csv"; // URL to csv
     * $result = $bulk->bulkCsvCustomObject();
     * print_r($result);
     * ----------------------------------------------------------------------------------
     */
    public function bulkCsvCustomObject() {

        $uploaded = $this->filename;
        $file = MKTO_BASE_PATH . "/" . $uploaded;
        $filewithdir = str_replace($file['path'], "", $uploaded); // pending create path und dir constants
        $realpath = realpath($file['dir'] . $filewithdir);
        $url = "https://" . $this->endPoint . "/bulk/v1/customobjects/" . $this->name . "/import.json?format=csv&access_token=" . $this->token;
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
     * @return mixed|string|void
     */
    private function bodyBuilder(){
        $requestBody = new \stdClass();
        //set dedupeby parameter in json body
        $requestBody->dedupeBy = $this->dedupeBy;
        $requestBody->input = array();
        $i = 0;
        //if dedupeby is dedupefields copy input to json
        if ($this->dedupeBy === "dedupeFields"){
            $requestBody->input = $this->input;
        }//else use marketoGUID
        else if ($this->dedupeBy === "idField"){
            foreach($this->input as $id){
                $obj = new \stdClass();
                $obj->marketoGUID = $id;
                $requestBody->input[$i] = $obj;
                $i++;
            }
        }
        $json = json_encode($requestBody);
        return $json;
    }

    /**
     * @return mixed|string|void
     */
    private function bodyBuilderSync(){
        $requestBody = new \stdClass();
        if (isset($this->action)){
            $requestBody->action = $this->action;
        }
        if (isset($this->dedupeBy)){
            $requestBody->dedupeBy = $this->dedupeBy;
        }
        $requestBody->input = $this->input;
        $json = json_encode($requestBody);
        return $json;
    }

    /**
     * @param $fields
     * @return string
     */
    private static function csvString($fields){
        $csvString = "";
        $i = 0;
        foreach($fields as $field){
            if ($i > 0){
                $csvString = $csvString . "," . $field;
            }elseif ($i === 0){
                $csvString = $field;
            }
        }
        return $csvString;
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

