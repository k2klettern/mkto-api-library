<?php
/**
 * Created by PhpStorm.
 * User: Eric Zeidan
 * Date: 17/11/2016
 * Time: 12:50
 */

namespace Mkto\lead\campaigns;

use Mkto\token;
use Mkto\logs;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class Campaign
 *
 * @author Eric Zeidan
 * @package Mkto\lead\campaings
 */
class Campaign
{
    /**
     *
     * @var Singleton
     */
    private static $instance;

    private $token;
    private $endPoint;

    public $id;//id of campaign to retrieve
    public $ids; //array of list Ids to retrieve
    public $names;//array of names to to retrieve
    public $programName;//array of program names to retrieve lists from
    public $workspaceName; //array of Workspace names to retrieve lists from
    public $batchSize; //max 300, default 300
    public $nextPageToken; //token retrieved from previous call for paging
    public $leads;//array of stdClass objects with one member, id, required
    public $tokens;//array of stdClass objects with two members, name and value
    public $runAt;//dateTime to run campaign
    public $cloneToProgramName;//if set will clone program with name
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
     * @return Campaign|Singleton
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
     * $campaigns = new Mkto\lead\campaigns\Campaign();
     * print_r($campaigns->multipleCampaigns());
     * -------------------------------------------------------------------
     */
    public function multipleCampaigns(){
        $url = "https://" . $this->endPoint . "/rest/v1/campaigns.json?access_token=" . $this->token;
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
     * @return array
     *
     * EXAMPLE OF USE
     * -------------------------------------------------------------------
     * $campaign = new Mkto\lead\campaigns\Campaign();
     * $campaign->id = 1003;
     * print_r($campaign->getCampaignById());
     * -------------------------------------------------------------------
     */
    public function getCampaignById(){
        $url = "https://" . $this->endPoint . "/rest/v1/campaigns/" . $this->id . ".json?access_token=" . $this->token;
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
     *   $request = new Mkto\lead\campaigns\Campaign();
     *   $request->id = 1001;
     *   $token1 = new stdClass();
     *   $token1->name = "token";
     *   $token1->value = "Hello World!";
     *   $mytokens = new stdClass();
     *   $mytokens->tokens = array($token1);
     *   $request->tokens = $mytokens;
     *   $leads1 = new stdClass();
     *   $leads1->id = 1;
     *   $myLeads = new stdClass();
     *   $myLeads->leads = array($leads1);
     *   $request->leads = $myLeads;
     *   print_r($request->requestCampaign());
     * -------------------------------------------------------------------
     */
    public function requestCampaign(){
        $url = "https://" . $this->endPoint . "/rest/v1/campaigns/" . $this->id . "/trigger.json?access_token=" . $this->token;
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
     * EXAMPLE OF USE
     * -------------------------------------------------------------------
     * $request = new Mkto\lead\campaigns\Campaign();
     * $request->id = 1001;
     * $token1 = new stdClass();
     * $token1->name = "{{my.token}}";
     * $token1->value = "Hello World!";
     * $request->tokens = array($token1);
     * print_r($request->scheduleCampaign());
     * -------------------------------------------------------------------
     */
    public function scheduleCampaign(){
        $url = "https://" . $this->endPoint . "/rest/v1/campaigns/" . $this->id . "/schedule.json?access_token=" . $this->token;
        $requestBody = $this->bodyBuilderSchedule();
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
        $input = new \stdClass();
        $body->input = $this->leads;
        if (isset($this->tokens)){
            $body->input = (object) array_merge((array) $this->leads, (array) $this->tokens);
        }

        $json = json_encode($body);
        return $json;
    }

    /**
     * @return mixed|string|void
     */
    private function bodyBuilderSchedule(){
        $body = new \stdClass();
        $body->input = new \stdClass();
        if (isset($this->runAt)){
            $body->input->runAt = $this->runAt;
        }
        if (isset($this->cloneToProgramName)){
            $body->input->cloneToProgramName = $this->cloneToProgramName;
        }
        if (isset($this->tokens)){
            $body->input->tokens = $this->tokens;
        }
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


