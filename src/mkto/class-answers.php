<?php
/**
 * Created by PhpStorm.
 * User: Eric Zeidan
 * Date: 13/12/2016
 * Time: 15:18
 */

namespace WPD\MarketoAPI\answers;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class Answers
{
    /**
     *
     * @var Singleton
     */
    private static $instance;

    public $result;
    public $functionname;

    public function __construct()
    {
    }
    /**
     * @return Logs|Singleton
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    /**
     * @param $response
     * @param null $type can be INFO,WARNING,ERROR,SUCCESS
     */
    public function proccessAnswer( $response )
    {
        if (curl_error($response)) {
            $this->result[] = "danger";
            $this->result[] = preg_replace('/;+/', ' ',$response->get_error_message());
            $return = $this->printMessage();
            echo $return;
            return;
        } elseif(is_array($response) || is_object($response)) {
            $body = json_decode(wp_remote_retrieve_body($response));
            if($body->success) {
                $this->result[] = "success";
                $this->result[] = $body->result;
            } elseif ($body->errors) {
                $this->result[] = "danger";
                $this->result[] = $body->errors;
            } else {
                $this->result[] = "info";
                $this->result[] = $body->warnings;
            }
            $return = $this->printMessage();
            echo $return;
            return;
        } else {
            $this->result[] = "alert";
            $this->result = (!is_array($response) || !is_object($response)) ? "; " . preg_replace('/;+/', ' ',$response) : "; " . preg_replace('/;+/', ' ',print_r($response, true));
            $return = $this->printMessage();
            echo $return;
            return;
        }
    }

    public function printMessage() {
        $msg = "<div class=\"alert alert-" . $this->result[0] . "\" role=\"alert\">";
        $msg .= "<span class=\"glyphicon glyphicon-exclamation-sign\" aria-hidden=\"true\"></span>";
        $msg .= "<span class=\"sr-only\">Notice:</span>";
        $msg .= json_encode($this->result[1]);
        $msg .= "</div>";

        return $msg;
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