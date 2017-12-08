<?php
/**
 * Created by PhpStorm.
 * User: Eric Zeidan
 * Date: 18/11/2016
 * Time: 12:29
 */

namespace Mkto\lead\usage;

use Mkto\token;
use Mkto\logs;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class Usage
 *
 * @author Eric Zeidan
 * @package Mkto\lead\usage
 */
class Usage
{
    /**
     *
     * @var Singleton
     */
    private static $instance;
    public $id;
    public $listId;
    public $leadIds;//id of the list to retrieve
    public $fields;//id of list to add to
    public $batchSize;//array of lead ids to add to list
    public $nextPageToken;//one or more fields to return
    public $ids; //max 300 default 300
    public $functionname;//token returned from previous call for paging
    private $token;//optional list of lead Ids to retrieve
    private $endPoint;

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
     * @return Usage|Singleton
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
     * $errors = new Mkto\lead\usage\Usage();
     * print_r($errors->getDailyErrors());
     * -------------------------------------------------------------------
     */
    public function getDailyErrors(){
        $url = "https://" . $this->endPoint . "/rest/v1/stats/errors.json?access_token=" . $this->token;
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
     * $usage = new Mkto\lead\usage\Usage();
     * print_r($usage->getDailyUsage());
     * -------------------------------------------------------------------
     */
    public function getDailyUsage(){
        $url = "https://" . $this->endPoint . "/rest/v1/stats/usage.json?access_token=" . $this->token;
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
     * $errors = new Mkto\lead\usage\Usage();
     * print_r($errors->getWeeklyErrors());
     * -------------------------------------------------------------------
     */
    public function getWeeklyErrors(){
        $url = "https://" . $this->endPoint . "/rest/v1/stats/errors.json?access_token=" . $this->token;
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
     * $usage = new Mkto\lead\usage\Usage();
     * print_r($usage->getWeeklyUsage());
     * -------------------------------------------------------------------
     */
    public function getWeeklyUsage(){
        $url = "https://" . $this->endPoint . "/rest/v1/stats/usage/last7days.json?access_token=" . $this->token;
        $ch = curl_init($url);
        curl_setopt($ch,  CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('accept: application/json',));
        $response = curl_exec($ch);
        \Mkto\logs\logs::getInstance()->proccessLog($response, $url);
        return $response;
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
