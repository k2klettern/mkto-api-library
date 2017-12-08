<?php
/**
 * Created by PhpStorm.
 * User: Eric Zeidan
 * Date: 17/11/2016
 * Time: 13:14
 */

namespace Mkto\lead\companies;

use Mkto\token;
use Mkto\logs;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class Companies
 *
 * @author Eric Zeidan
 * @package Mkto\lead\companies
 */
class Companies
{
    /**
     *
     * @var Singleton
     */
    private static $instance;

    private $token;
    private $endPoint;

    public $filterType;//filter field, one of describe SearchableFields
    public $filterValues;//array of filtered values
    public $fields;//optional array of fields to retrieve
    public $nextPageToken;//token for paging
    public $batchSize;//max 300, default 300
    public $externalCompanyIds;//array of external opportunity ids
    public $idfields; //array of marketo IDs
    public $dedupeBy; //dedupe field, dedupeFields or idField
    public $input;//array of company objects, required
    public $action;//action to take, createOnly, updateOnly, createOrUpdate, default createOrUpdate
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
     * @return Companies|Singleton
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
     * $companies = new Mkto\lead\companies\Companies();
     * $companies->filterType = "externalCompanyId";
     * $companies->filterValues = ["Company 1"];
     * print_r($companies->getCompanies());
     * -------------------------------------------------------------------
     */
    public function getCompanies(){
        $url = "https://" . $this->endPoint . "/rest/v1/companies.json?access_token=" . $this->token . "&filterType=" . $this->filterType
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
     * $delete = new Mkto\lead\companies\Companies();
     * $delete->dedupeBy = "dedupeFields";
     * $delete->externalCompanyIds = ["Company 1"];
     * print_r($delete->deleteCompanies());
     * -------------------------------------------------------------------
     */
    public function deleteCompanies(){
        $url = "https://" . $this->endPoint . "/rest/v1/opportunities/delete.json?access_token=" . $this->token;
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
     * $roles = new Mkto\lead\companies\Companies();
     * $roles->filterType = "externalopportunityid";
     * $roles->filterValues = ["Opportunity Test 1"];
     * print_r($roles->describeCompanies());
     * -------------------------------------------------------------------
     */
    public function describeCompanies(){
        $url = "https://" . $this->endPoint . "/rest/v1/opportunities/roles.json?access_token=" . $this->token . "&filterType=" . $this->filterType
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
     * $upsert = new Mkto\lead\companies\Companies();
     * $company1 = new stdClass();
     * $company1->externalCompanyId = "Company 1";
     * $upsert->input = [$company1];
     * $upsert->dedupeBy = "dedupeFields";
     * print_r($upsert->syncCompanies());
     * -------------------------------------------------------------------
     */
    public function syncCompanies(){
        $url = "https://" . $this->endPoint . "/rest/v1/companies.json?access_token=" . $this->token;
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
     * @return mixed|string|void
     */
    private function bodyBuilder(){
        $requestBody = new \stdClass();
        //set dedupeby parameter in json body
        $requestBody->dedupeBy = $this->dedupeBy;
        $requestBody->input = array();
        $i = 0;
        //if dedupeby is dedupefields, use externalopportunityid
        if ($this->dedupeBy === "dedupeFields"){
            foreach($this->input as $id){
                $obj = new \stdClass();
                $obj->externalCompanyId = $id;
                $requestBody->input[$i] = $obj;
                $i++;
            }
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

