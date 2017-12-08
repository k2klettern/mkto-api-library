<?php
namespace Mkto\token;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class GetAccessToken
 */

class GetAccessToken{
    /**
     *
     * @var Singleton
     */
    private static $instance;

    public $token;
    private $endPoint;
    private $clientId;
    private $clientSecret;

    public $functionname;


    public function __construct(){

        require_once "settings.php";

        $this->endPoint = MKTO_ENDPOINT;
        $this->clientId = MKTO_CLIENTID;
        $this->clientSecret = MKTO_CLIENTSECRET;

    }

    /**
     * @return GetAccessToken|Singleton
     */
    public static function getInstance()
    {
        if ( is_null( self::$instance ) )
        {
            self::$instance = new self();
        }
        return self::$instance;
    }


    /**
     *
     * @return mixed
     *
     * Get the Marketo Token and saves in an option to be used during one hour
     */
    public function getToken(){
        global $token;

        $ch = curl_init($this->endPoint . "/identity/oauth/token?grant_type=client_credentials&client_id=" . $this->clientId . "&client_secret=" . $this->clientSecret);
        curl_setopt($ch,  CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('accept: application/json',));
        $response = json_decode(curl_exec($ch));
        curl_close($ch);
        $token = $response->access_token;
        return $token;
    }

    /**
     * @return string|void
     *
     * @param functionname need to be set
     *
     * Example of use
     * ===================================================
     * $reflec = new WPD\MarketoAPI\token\GetAccessToken;
     * $reflec->functionname = "getToken";
     * $result = $reflec->get_reflection();
     * ===================================================
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