<?php
/**
 * Created by PhpStorm.
 * User: Eric Zeidan
 * Date: 14/11/2016
 * Time: 18:06
 */

namespace Mkto\logs;


defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class Logs
 *
 * @author Eric Zeidan
 * @package WPD\MarketoAPI\assets\emails
 */
class Logs
{
    /**
     *
     * @var Singleton
     */
    private static $instance;

    private $dir;
    private $filename = 'logs-mkto.txt';
    private $file;
    private $fh;
    public $log;
    public $functionname;

    public function __construct()
    {

        $this->dir = MKTO_BASE_PATH;
        $this->file = $this->dir. "/" .$this->filename;

        if (!file_exists( $this->file))
        {
	    chmod($this->dir['basedir'], 0777);	
            $this->fh = fopen( $this->file, "c+");
            if($this->fh==false)
                echo "unable to create file";
            else
                chmod($this->file, 0777);
                file_put_contents($this->file, "DATE/TIME;TYPE;INSTANCE;RESPONSE\r\n", FILE_APPEND | LOCK_EX);
		chmod($this->dir['basedir'], 0755);
        }
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
    public function proccessLog( $response, $url = null, $type = null ){
        if (curl_error($response)) {
            $logentry = date('Y:m:d h:i:s');
            $logentry .= "; ERROR ";
            $logentry .= "; " . $this->endpoint;
            $logentry .= "; " .  preg_replace('/;+/', ' ', curl_error($response));
            $this->writeLog($logentry);
        } elseif(is_array($response) || is_object($response)) {
            $logentry = date('Y:m:d h:i:s');
                $body = json_decode(wp_remote_retrieve_body($response));
                if($body->success) {
                    $logentry .= " ; SUCCESS ";
                } elseif ($body->errors) {
                    $logentry .= " ; ERROR ";
                } else {
                    $logentry .= " ; INFO ";
                }
            $logentry .= "; " . $this->endpoint;
            $logentry .= " ; BODY: " .  preg_replace('/;+/', ' ', serialize($response));
	        $logentry .= "HEADERS: " .  preg_replace('/;+/', ' ', serialize($response));
            $logentry .= "HEADER: " .  preg_replace('/;+/', ' ', serialize($response, 'url'));
            $logentry .=  "CODE: " .  preg_replace('/;+/', ' ', serialize($response));
            if(isset($url)) $logentry .= "URL REQUEST: " . $url;
            $this->writeLog($logentry);
        } else {
            $logentry = date('Y:m:d h:i:s');
            $logentry .= isset($type) ? "; " . $type : "; INFO";
            $logentry .= "; " . $this->endpoint;
            $logentry .= (!is_array($response) || !is_object($response)) ? "; " . preg_replace('/;+/', ' ',$response) : "; " . preg_replace('/;+/', ' ',print_r($response, true));
            $this->writeLog($logentry);
        }
    }

    /**
     * @param $data
     */
    private function writeLog( $data )  {

        if ( is_array( $data ) || is_object( $data ) ) {
            file_put_contents($this->file,  print_r( $data, true ) . "\r\n", FILE_APPEND | LOCK_EX);
        } else {
            file_put_contents($this->file, $data . "\r\n", FILE_APPEND | LOCK_EX);
        }
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
