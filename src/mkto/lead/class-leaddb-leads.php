<?php
/**
 * Created by PhpStorm.
 * User: Eric Zeidan
 * Date: 17/11/2016
 * Time: 14:03
 */

namespace Mkto\lead\leads;

use Mkto\token;
use Mkto\logs;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class Leads
 *
 * @author Eric Zeidan
 * @package Mkto\lead\leads
 */
class Leads
{
    /**
     *
     * @var Singleton
     */
    private static $instance;

    private $token;
    private $endPoint;

    public $ids;//Array of objects containing lead ids
    public $id;//id of the lead to associate to or id of the program for leadsByProgram or winning lead id for mergeLeads
    public $cookie;//cookie to associate
    public $fields;//array of fields to return
    public $filterType; //field to filter off of, required
    public $filterValues; //one or more values for filter, required
    public $batchSize;
    public $nextPageToken;//token returned from previous call for paging
    public $leadIds; //array of one or more losing IDs
    public $input; //an array of lead records as objects
    public $lookupField; //field used for deduplication
    public $action; //operation type, createOnly, updateOnly, createOrUpdate, createDuplicate
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
     * @return Leads|Singleton
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
     * $describe = new Mkto\lead\leads\Leads();
     * print_r($describe->describeLead());
     * -------------------------------------------------------------------
     */
    public function describeLead(){
        $url = "https://" . $this->endPoint . "/rest/v1/leads/describe.json?access_token=" . $this->token;
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
     * $associate = new Mkto\lead\leads\Leads();
     * $associate->id = 1;
     * $associate->cookie = urlencode("mkto_trk=id:299-BYM-827&token:_mch-localhost-1435105067262-67189");
     * print_r($associate->associatedLead());
     * -------------------------------------------------------------------
     */
    public function associatedLead(){
        $url = "https://" . $this->endPoint . "/rest/v1/leads/" . $this->id . "/associate.json?access_token=" . $this->token . "&cookie=" . $this->cookie;
        $ch = curl_init($url);
        curl_setopt($ch,  CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('accept: application/json','Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
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
     * $delete = new Mkto\lead\leads\Leads();
     * $lead1 = new stdClass();
     * $lead1->id = 9;
     * $delete->ids = array($lead1);
     * print_r($delete->deleteLeads());
     * -------------------------------------------------------------------
     */
    public function deleteLeads(){
        $url = "https://" . $this->endPoint . "/rest/v1/leads.json?access_token=" . $this->token;
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
     * $lead = new Mkto\lead\leads\Leads();
     * $lead->id = 9;
     * print_r($lead->getLeadById());
     * -------------------------------------------------------------------
     */
    public function getLeadById(){
        $url = "https://" . $this->endPoint . "/rest/v1/lead/" . $this->id . ".json?access_token=" . $this->token;
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
     * $lead = new Mkto\lead\leads\Leads();
     * print_r($lead->getLeadPartitions());
     * -------------------------------------------------------------------
     */
    public function getLeadPartitions(){
        $url = "https://" . $this->endPoint . "/rest/v1/leads/partitions.json?access_token=" . $this->token;
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
     * $leads = new Mkto\lead\leads\Leads();
     * $leads->filterType = "email";
     * $leads->filterValues = array("k2klettern@gmail.com");
     * $leads->fields = array("email", "firstName", "lastName");
     * print_r($leads->getLeadsByFilterType());
     * -------------------------------------------------------------------
     */
    public function getLeadsByFilterType(){
        $url = "https://" . $this->endPoint . "/rest/v1/leads.json?access_token=" . $this->token
            . "&filterType=" . $this->filterType . "&filterValues=" . $this->csvString($this->filterValues);
        if (isset($this->batchSize)){
            $url = $url . "&batchSize=" . $this->batchSize;
        }
        if (isset($this->nextPageToken)){
            $url = $url . "&nextPageToken=" . $this->nextPageToken;
        }
        if(isset($this->fields)){
            $url = $url . "&fields=" . $this->csvString($this->fields);
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
     * $leads = new Mkto\lead\leads\Leads();
     * $leads->id = 1003;
     * print_r($leads->getLeadsByProgramId());
     * -------------------------------------------------------------------
     */
    public function getLeadsByProgramId(){
        $url = "https://" . $this->endPoint . "/rest/v1/leads/programs/" . $this->id . ".json?access_token=" . $this->token;
        if (isset($this->batchSize)){
            $url = $url . "&batchSize=" . $this->batchSize;
        }
        if (isset($this->nextPageToken)){
            $url = $url . "&nextPageToken=" . $this->nextPageToken;
        }
        if(isset($this->fields)){
            $url = $url . "&fields=" . $this->csvString($this->fields);
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
     * $delete = new Mkto\lead\leads\Leads();
     * $delete->id = 4;
     * $delete->leadIds = array(5, 9);
     * print_r($delete->mergeLeads());
     * -------------------------------------------------------------------
     */
    public function mergeLeads(){
        $url = "https://" . $this->endPoint . "/rest/v1/leads/" . $this->id ."/merge.json?access_token=" . $this->token . "&leadIds=" . $this->csvString($this->leadIds);
        $ch = curl_init($url);
        curl_setopt($ch,  CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('accept: application/json','Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_getinfo($ch);
        $response = curl_exec($ch);
        \Mkto\logs\logs::getInstance()->proccessLog($response, $url);
        return $response;
    }

    /**
     * @return array
     *
     * $request = new Mkto\lead\leads\Leads();
     * $request->action = "createOnly";
     * $request->lookupField = "email";
     * $lead1 = new stdClass();
     * $lead1->email = "eric@bsa.dev";
     * $lead1->firstName = "Eric";
     * $lead1->lastName = "Zeidan";
     * $request->input = array($lead1);
     * print_r($request->syncLeads());
     *
     */
    public function syncLeads(){
        $url = "https://" . $this->endPoint . "/rest/v1/leads.json?access_token=" . $this->token;
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
        $body = new \stdClass();
        $body->input = $this->ids;
        $json = json_encode($body);
        return $json;
    }

    private function bodyBuilderSync(){
        $body = new \stdClass();
        if (isset($this->action)){
            $body->action = $this->action;
        }
        if (isset($this->lookupField)){
            $body->lookupField = $this->lookupField;
        }
        if (isset($this->asyncProcessing)){
            $body->asyncProcessing = $this->asyncProcessing;
        }
        if (isset($this->partitionCode)){
            $body->partitionCode = $this->partitionCode;
        }
	if (isset($this->partitionName)){
            $body->partitionName = $this->partitionName;
        }

        $body->input = $this->input;
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
