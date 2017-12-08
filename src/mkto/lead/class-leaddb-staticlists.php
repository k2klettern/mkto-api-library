<?php
/**
 * Created by PhpStorm.
 * User: Eric Zeidan
 * Date: 18/11/2016
 * Time: 11:50
 */

namespace Mkto\lead\staticlist;

use Mkto\token;
use Mkto\logs;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class Staticlist
 *
 * @author Eric Zeidan
 * @package Mkto\lead\staticlist
 */
class Staticlist
{
    /**
     *
     * @var Singleton
     */
    private static $instance;

    private $token;
    private $endPoint;

    public $id;//id of the list to retrieve
    public $listId;//id of list to add to
    public $leadIds;//array of lead ids to add to list
    public $fields;//one or more fields to return
    public $batchSize; //max 300 default 300
    public $nextPageToken;//token returned from previous call for paging
    public $ids;//optional list of lead Ids to retrieve
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
     * @return Staticlist|Singleton
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
     * $lists = new Mkto\lead\staticlist\Staticlist();
     * $lists->ids = [1,2,3,4,5,6,7,8, 1001, 1007];
     * print_r($lists->getLists());
     * -------------------------------------------------------------------
     */
    public function getLists(){
        $url = "https://" . $this->endPoint . "/rest/v1/lists.json?access_token=" . $this->token;
        if (isset($this->ids)){
            $url .= "&id=" . $this->csvString($this->ids);
        }
        if (isset($this->programName)){
            $url .= "&programName=" . $this->csvString($this->programName);
        }
        if (isset($this->workspaceName)){
            $url .= "&workspaceName=" . $this->csvString($this->workspaceName);
        }
        if (isset($this->names)){
            $url .= "&name=" . $this->csvString($this->names);
        }
        if (isset($this->batchSize)){
            $url .= "&batchSize=" . $this->csvString($this->batchSize);
        }
        if (isset($this->nextPageToken)){
            $url .= "&nextPageToken=" . $this->csvString($this->batchSize);
        }
        $ch = curl_init($url);
        curl_setopt($ch,  CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('accept: application/json',));
        $response = curl_exec($ch);
        \Mkto\logs\logs::getInstance()->proccessLog($response, $url);
        return $response;
    }

    /**
     * @return array|\WP_Error
     *
     * EXAMPLE OF USE
     * -------------------------------------------------------------------
     * $removeFromList = new Mkto\lead\staticlist\Staticlist();
     * $removeFromList->listId = 1001;
     * $removeFromList->leadIds = [1,2,3,4];
     * print_r($removeFromList->addToList());
     * -------------------------------------------------------------------
     */
    public function addToList(){
        $url = "http://" . $this->endPoint . "/rest/v1/lists/" . $this->listId . "/leads.json?access_token=" . $this->token;
        $requestBody = $this->bodyBuilder();
        $ch = curl_init($url);
        print_r($requestBody);
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
     * @return array|\WP_Error
     *
     * EXAMPLE OF USE
     * -------------------------------------------------------------------
     * $leads = new Mkto\lead\staticlist\Staticlist();
     * $leads->listId = 1001;
     * print_r($leads->getLeadsByListId());
     * -------------------------------------------------------------------
     */
    public function getLeadsByListId(){
        $url = "https://" . $this->endPoint . "/rest/v1/list/" . $this->listId . "/leads.json?access_token=" . $this->token;
        if (isset($this->fields)){
            $url = $url . "&fields=" . $this->csvString($this->fields);
        }
        if (isset($this->batchSize)){
            $url = $url . "&batchSize=" . $this->batchSize;
        }
        if (isset($this->nextPageToken)){
            $url = $url . "&nextPageToken=" . $this->fields;
        }
        $ch = curl_init($url);
        curl_setopt($ch,  CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('accept: application/json',));
        $response = curl_exec($ch);
        \Mkto\logs\logs::getInstance()->proccessLog($response, $url);
        return $response;
    }

    /**
     * @return array|\WP_Error
     *
     * EXAMPLE OF USE
     * -------------------------------------------------------------------
     * $list = new Mkto\lead\staticlist\Staticlist();
     * $list->id = 1001;
     * print_r($list->getListById());
     * -------------------------------------------------------------------
     */
    public function getListById(){
        $url = "https://" . $this->endPoint . "/rest/v1/lists/" . $this->id . ".json?access_token=" . $this->token;
        $ch = curl_init($url);
        curl_setopt($ch,  CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('accept: application/json',));
        $response = curl_exec($ch);
        \Mkto\logs\logs::getInstance()->proccessLog($response, $url);
        return $response;
    }

    /**
     * @return array|\WP_Error
     *
     * EXAMPLE OF USE
     * -------------------------------------------------------------------
     * $members = new Mkto\lead\staticlist\Staticlist();
     * $members->listId = 1001;
     * $members->ids = [1,2,3,4];
     * print_r($members->memberOfList());
     * -------------------------------------------------------------------
     */
    public function memberOfList(){
        $url = "https://" . $this->endPoint . "/rest/v1/lists/" . $this->listId . "/leads/ismember.json?access_token=" . $this->token;
        if (isset($this->ids)){
            $url .= "&id=" . $this->csvString($this->ids);
        }
        $ch = curl_init($url);
        curl_setopt($ch,  CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('accept: application/json',));
        $response = curl_exec($ch);
        \Mkto\logs\logs::getInstance()->proccessLog($response, $url);
        return $response;
    }

    /**
     * @return array|\WP_Error
     *
     * EXAMPLE OF USE
     * -------------------------------------------------------------------
     * $removeFromList = new Mkto\lead\staticlist\Staticlist();
     * $removeFromList->listId = 1001;
     * $removeFromList->leadIds = [1,2,3,4];
     * print_r($removeFromList->removeFromList());
     * -------------------------------------------------------------------
     */
    public function removeFromList(){
        $url = "https://" . $this->endPoint . "/rest/v1/lists/" . $this->listId . "/leads.json?access_token=" . $this->token;
        $requestBody = $this->bodyBuilderRemove();
        $ch = curl_init($url);
        print_r($requestBody);
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
        $array = array();
        foreach($this->leadIds as $lead){
            $member = new \stdClass();
            $member->id = $lead;
            array_push($array, $member);
        }
        $body = new \stdClass();
        $body->input = $array;
        $json = json_encode($body);
        return $json;
    }

    /**
     * @return mixed|string|void
     */
    private function bodyBuilderRemove(){
        $array = array();
        foreach($this->leadIds as $lead){
            $member = new \stdClass();
            $member->id = $lead;
            array_push($array, $member);
        }
        $body = new \stdClass();
        $body->input = $array;
        $json = json_encode($body);
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