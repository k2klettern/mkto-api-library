<?php
/**
 * Created by PhpStorm.
 * User: Eric Zeidan
 * Date: 17/11/2016
 * Time: 16:17
 */

namespace Mkto\lead\opportunities;

use Mkto\token;
use Mkto\logs;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class Opportunities
 *
 * @author Eric Zeidan
 * @package Mkto\lead\opportunities
 */
class Opportunities
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
    public $externalopportunityids;//array of external opportunity ids
    public $idfields; //array of marketo IDs
    public $dedupeBy; //dedupe field, dedupeFields or idField or dedupefields(externalopportunityid), or idField(marketoGUID) for sync
    public $name;//name of Custom Object type to delete
    public $input;//array of objects with fields to dedupeby or array of opportunity objects, required for Sync
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
     * @return Opportunities|Singleton
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
     * $oppties = new Mkto\lead\opportunities\Opportunities();
     * $oppties->filterType = "externalopportunityid";
     * $oppties->filterValues = ["Opportunity Test 1"];
     * print_r($oppties->getOpportunities());
     * -------------------------------------------------------------------
     */
    public function getOpportunities(){
        $url = "https://" . $this->endPoint . "/rest/v1/opportunities.json?access_token=" . $this->token . "&filterType=" . $this->filterType
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
     * $delete = new Mkto\lead\opportunities\Opportunities();
     * $delete->dedupeBy = "dedupeFields";
     * $delete->input = ["Opportunity 1", "Opportunity 2", "Opportunity 3"];
     * print_r($delete->deleteOpportunities());
     * -------------------------------------------------------------------
     */
    public function deleteOpportunities(){
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
     * $oppties = new Mkto\lead\opportunities\Opportunities();
     * $oppties->filterType = "kapturall";
     * $oppties->filterValues = ["Opportunity Test 1"];
     * print_r($oppties->describeOpportunity());
     * -------------------------------------------------------------------
     */
    public function describeOpportunity(){
        $url = "https://" . $this->endPoint . "/rest/v1/customobjects/" . $this->name . "/delete.json?access_token=" . $this->token;
        $requestBody = $this->bodyBuilderDescribe();
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
     * $describe = new Mkto\lead\opportunities\Opportunities();
     * print_r($describe->describeOpportunityRole());
     * -------------------------------------------------------------------
     */
    public function describeOpportunityRole(){
        $url = "https://". $this->endPoint . "/rest/v1/opportunities/roles/describe.json?access_token=" . $this->token;
        		$ch = curl_init($url);		curl_setopt($ch,  CURLOPT_RETURNTRANSFER, 1);		curl_setopt($ch, CURLOPT_HTTPHEADER, array('accept: application/json',));		$response = curl_exec($ch);
        \Mkto\logs\logs::getInstance()->proccessLog($response, $url);
        return $response;
    }

    /**
     * @return array
     *
     * EXAMPLE OF USE
     * -------------------------------------------------------------------
     * $roles = new Mkto\lead\opportunities\Opportunities();
     * $roles->filterType = "externalopportunityid";
     * $roles->filterValues = ["Opportunity Test 1"];
     * print_r($roles->getOpportunityRoles());
     * -------------------------------------------------------------------
     */
    public function getOpportunityRoles(){
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
     * $upsert = new Mkto\lead\opportunities\Opportunities();
     * $oppty1 = new stdClass();
     * $oppty1->externalopportunityid = "Opportunity 1";
     * $upsert->input = [$oppty1];
     * $upsert->dedupeBy = "dedupeFields";
     * print_r($upsert->syncOpportunities());
     * -------------------------------------------------------------------
     */
    public function syncOpportunities(){
        $url = "https://" . $this->endPoint . "/rest/v1/opportunities.json?access_token=" . $this->token;
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
     * @return array
     *
     * EXAMPLE OF USE
     * -------------------------------------------------------------------
     * $upsert = new Mkto\lead\opportunities\Opportunities();
     * $role1 = new stdClass();
     * $role1->externalopportunityid = "Opportunity 1";
     * $role1->role = "Captain";
     * $role1->leadId = 1;
     * $upsert->input = [$role1];
     * $upsert->dedupeBy = "dedupeFields";
     * print_r($upsert->syncOpportunityRoles());
     * -------------------------------------------------------------------
     */
    public function syncOpportunityRoles(){
        $url = "https://" . $this->endPoint . "/rest/v1/opportunities.json?access_token=" . $this->token;
        $requestBody = $this->bodyBuilderSyncRole();
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
                $obj->externalopportunityid = $id;
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
    private function bodyBuilderDescribe(){
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
     * @return mixed|string|void
     */
    private function bodyBuilderSyncRole(){
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
