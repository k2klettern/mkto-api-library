<?php
/**
 * Created by PhpStorm.
 * User: Eric Zeidan
 * Date: 17/11/2016
 * Time: 12:03
 */

namespace Mkto\lead;

use Mkto\token;
use Mkto\logs;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class Activities
 *
 * @author Eric Zeidan
 * @package Mkto\lead
 */
class Activities
{
    /**
     *
     * @var Singleton
     */
    private static $instance;

    private $token;
    private $endPoint;

    public $activityTypeIds; //array of integer IDs of activity types, required
    public $nextPageToken;//paging token to specify beginning date for activities, required
    public $batchSize;//max 300, default 300
    public $listId;//integer id of a static list, if specified will only retrieve from leads in the list
    public $fields;//array of field names to retrieve changes for, required
    public $sinceDatetime;//earliest time to retrieve data from
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
     * @return Activities|Singleton
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
     * $types = new Mkto\lead\activities\Activities();
     * print_r($types->getActivityTypes());
     * -------------------------------------------------------------------
     */
    public function getActivityTypes(){
        $url = "https://" . $this->endPoint . "/rest/v1/activities/types.json?access_token=" . $this->token;
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
     * $activities = new Mkto\lead\activities\Activities();
     * $activities->nextPageToken = "WQV2VQVPPCKHC6AQYVK7JDSA3I3LCWXH3Y6IIZ7YSGQLXHCPVE5Q====";
     * $activities->activityTypeIds = [1,2,3];
     * print_r($activities->getLeadActivities());
     * -------------------------------------------------------------------
     */
    public function getLeadActivities(){
        $url = "https://" . $this->endPoint . "/rest/v1/activities.json?access_token=" . $this->token . "&activityTypeIds=" . $this->csvString($this->activityTypeIds)
            . "&nextPageToken=" . $this->nextPageToken;
        if (isset($this->batchSize)){
            $url .= "&batchSize=" . $this->batchSize;
        }
        if (isset($this->listId)){
            $url .= "&listId=" . $this->listId;
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
     * $changes = new Mkto\lead\activities\Activities();
     * $changes->nextPageToken = "WQV2VQVPPCKHC6AQYVK7JDSA3I3LCWXH3Y6IIZ7YSGQLXHCPVE5Q====";
     * $changes->fields = ["email", "firstName", "lastName"];
     * print_r($changes->getLeadChanges());
     * -------------------------------------------------------------------
     */
    public function getLeadChanges(){
        $url = "https://" . $this->endPoint . "/rest/v1/activities/leadchanges.json?access_token=" . $this->token . "&fields=" . $this->csvString($this->fields)
            . "&nextPageToken=" . $this->nextPageToken;
        if (isset($this->batchSize)){
            $url .= "&batchSize=" . "$this->batchSize";
        }
        if (isset($this->listId)){
            $url .= "&listId=" . $this->listId;
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
     * $addcusact = new Mkto\lead\activities\Activities();
     * $addcusact->leadID = 1001;
     * $addcusact->activityDate = "2017-09-26T06:56:35+07:00",
     * $addcusact->activityTypeId = 1001;
     * $addcusact->primaryAttributeValue = "Game Giveaway";
     * $attr1 = new stdClass();
     * $attr1->name = "URL";
     * $attr1->apiName = "url";
     * $attr1->value = "http://www.nvidia.com/game-giveaway";
     * $addcusact->attributes = [$attr1];
     * print_r($addcusact->addCustomActivities());
     * -------------------------------------------------------------------
     */
    public function addCustomActivities() {
        $url = "https://" . $this->endPoint . "/rest/v1/activities/external.json?access_token=" . $this->token;
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
     * $pagingToken = new Mkto\lead\activities\Activities();
     * $pagingToken->sinceDatetime = "2015-01-01T00:00:00z";
     * print_r($pagingToken->getPagingToken());
     * -------------------------------------------------------------------
     */
    public function getPagingToken(){
        $url = "https://" . $this->endPoint. "/rest/v1/activities/pagingtoken.json?access_token=" . $this->token . "&sinceDatetime=" . $this->sinceDatetime;
        		$ch = curl_init($url);		curl_setopt($ch,  CURLOPT_RETURNTRANSFER, 1);		curl_setopt($ch, CURLOPT_HTTPHEADER, array('accept: application/json',));		$response = curl_exec($ch);
        \Mkto\logs\logs::getInstance()->proccessLog($response, $url);
        return $response;
    }

    /**
     * @return mixed|string|void
     */
    private function bodyBuilder(){
        $requestBody = new \stdClass();
        $input->leadId = $this->leadId;
        $input->activityDate = $this->activityDate;
        if(isset($this->apiName))
            $input->apiName = $this->apiName;
        $input->activityTypeId = $this->activityTypeId;
        $input->primaryAttributeValue = $this->primaryAttributeValue;
	foreach($this->attributes as $attribute) {
		foreach($attribute as $key => $attrs) {
			$attr[$key]['name'] = $attribute[$key]->name;
			$attr[$key]['value'] = $attribute[$key]->value;
		}
	}
        $input->attributes = $attr;
        $input->id = $this->id;
        if(isset($this->status))
            $input->status = $this->status;
        $requestBody->input = [$input];
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
            $i++;
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
