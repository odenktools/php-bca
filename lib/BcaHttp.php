<?php

use Carbon\Carbon;
use Exception;
use Unirest;

class BcaHttpException extends Exception
{
}

class BcaHttpInstance
{
    private static $instance      = null;
    private static $corp_id       = '';
    private static $client_id     = '';
    private static $client_secret = '';

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public static function get_bca()
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        self::$instance = new BcaHttp(
            self::$corp_id,
            self::$client_id,
            self::$client_secret
        );
        return self::$instance;
    }
}

class BcaHttp
{
    public static $VERSION = '1.0.0';

    private $logger = null;

    private $settings = array(
        'corp_id'       => '',
        'client_id'     => '',
        'client_secret' => '',
        'scheme'        => 'https',
        'port'          => 443,
        'host'          => 'sandbox.bca.co.id',
        'timeout'       => 30,
        'debug'         => true,
        'development'   => true,
        'curl_options'  => array(),
    );

    /**
     * Curl handler
     * @var object
     */
    private $ch = null;

    /**
     * @var string
     */
    protected $error;

    /**
     * Check if the current PHP setup is sufficient to run this class.
     *
     * @throws BcaHttpException if any required dependencies are missing
     */
    private function check_compatibility()
    {
        if (!extension_loaded('curl') || !extension_loaded('json')) {
            throw new BcaHttpException('CURL tidak terinstall pada PHP anda.');
        }

        if (!in_array('sha256', hash_algos())) {
            throw new BcaHttpException('SHA256 tidak support pada versi PHP anda, Silahkan upgrade versi PHP anda.');
        }
    }

    public function __construct($corp_id, $client_id, $client_secret, $options = array())
    {
        if (!isset($options['host'])) {
            $options['host'] = 'sandbox.bca.co.id';
        }

        if (!isset($options['host'])) {
            $options['port'] = 443;
        }

        foreach ($options as $key => $value) {
            // only set if valid setting/option
            if (isset($this->settings[$key])) {
                $this->settings[$key] = $value;
            }
        }

        $this->settings['corp_id']       = $corp_id;
        $this->settings['client_id']     = $client_id;
        $this->settings['client_secret'] = $client_secret;

        if (!array_key_exists('host', $this->settings)) {
            if (array_key_exists('host', $options)) {
                $this->settings['host'] = $options['host'];
            } else {
                $this->settings['host'] = 'sandbox.bca.co.id';
            }
        }

        $this->settings['host'] =
            preg_replace('/http[s]?\:\/\//', '', $this->settings['host'], 1);
    }

    /**
     * Fetch the settings.
     *
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Build the ddn domain.
     * output = 'https://sandbox.bca.co.id:443'
     * scheme = http(s)
     * host = sandbox.bca.co.id
     * port = 80 ? 443
     * prefix = '/api/v1'
     * @return string
     */
    private function ddn_domain()
    {
        return $this->settings['scheme'] . '://' . $this->settings['host'] . ':' . $this->settings['port'];
    }

    /**
     * Generate authentifikasi ke server berupa OAUTH
     *
     * @param bool $data
     *
     * @return array
     */
    public function httpAuth($data = [])
    {
        $corp_id       = $this->settings['corp_id'];
        $client_id     = $this->settings['client_id'];
        $client_secret = $this->settings['client_secret'];

        $this->validateClientKey($client_id);
        $this->validateClientSecret($client_secret);

        $result      = array('token' => null, 'code' => 401);
        $headerToken = base64_encode("$client_id:$client_secret");
        $headers     = array('Accept' => 'application/json', 'Authorization' => "Basic $headerToken");

        $request_path = "api/oauth/token";
        $domain       = $this->ddn_domain();
        $full_url     = $domain . $request_path;

        $body = \Unirest\Request\Body::form($data);
        Unirest\Request::verifyPeer(false);
        $response = Unirest\Request::post($full_url, $headers, $body);
        if ($response->code === 200) {
            $result['body']         = $response->body;
            $result['access_token'] = $response->body->access_token;
            $result['code']         = 200;
        }

        return $result;
    }

    public function getBalanceInfo($oauth_token, $sourceAccountId = [], $corp_id = '', $gmt = "+07:00")
    {
        if ($corp_id == '') {
            $corp_id = $this->settings['corp_id'];
        }

        $client_id     = $this->settings['client_id'];
        $client_secret = $this->settings['client_secret'];

        $this->validateClientKey($client_id);
        $this->validateClientSecret($client_secret);

        if (!empty($sourceAccountId)) {
            $arraySplit = implode(",", $sourceAccountId);
        } else {
            throw new BcaHttpException('AccountNumber tidak boleh kosong.');
        }

        $uriSign       = "GET:/banking/v2/corporates/$corp_id/accounts/$arraySplit";
        $isoTime       = self::generateIsoTime();
        $emptyArray    = array();
        $authSignature = $this->generateSign($uriSign, $oauth_token, $client_secret, $isoTime, $emptyArray);

        $headers                    = array();
        $headers['Accept']          = 'application/json';
        $headers['Content-Type']    = 'application/json';
        $headers['Authorization']   = "Bearer $oauth_token";
        $headers['X-BCA-Key']       = $client_id;
        $headers['X-BCA-Timestamp'] = $isoTime;
        $headers['X-BCA-Signature'] = $authSignature;
        $data                       = array('grant_type' => 'client_credentials');

        $request_path = "banking/v2/corporates/$corp_id/accounts/$arraySplit";
        $domain       = $this->ddn_domain();
        $full_url     = $domain . $request_path;

        \Unirest\Request::verifyPeer(false);
        $body     = \Unirest\Request\Body::form($data);
        $response = \Unirest\Request::get($full_url, $headers, $body);

        return $response;
    }

    /**
     * Generate Signature.
     *
     * @param string $url Url yang akan disign
     * @param string $auth_token string nilai token dari login
     * @param string $secret_key string secretkey yang telah diberikan oleh BCA
     * @param string $isoTime string Waktu ISO8601
     * @param string $bodyToHash array Body yang akan dikirimkan ke Server BCA
     *
     * @return string
     */
    public static function generateSign($url, $auth_token, $secret_key, $isoTime, $bodyToHash = [])
    {
        $hash = null;
        if (!empty($bodyToHash)) {
            ksort($bodyToHash);
            $encoderData = json_encode($bodyToHash, JSON_UNESCAPED_SLASHES);
            $hash        = hash("sha256", $encoderData);
        } else {
            $empty = "";
            $hash  = hash("sha256", $empty);
        }

        $stringToSign   = $url . ":" . $auth_token . ":" . $hash . ":" . $isoTime;
        $auth_signature = hash_hmac('sha256', $stringToSign, $secret_key, false);

        return $auth_signature;
    }

    public static function generateIsoTime($timeZone = "Asia/Jakarta")
    {
        $date = \Carbon\Carbon::now($timeZone);
        date_default_timezone_set($timeZone);
        $fmt     = $date->format('Y-m-d\TH:i:s');
        $ISO8601 = sprintf("$fmt.%s%s", substr(microtime(), 2, 3), date('P'));

        return $ISO8601;
    }

    private function validateClientKey($id)
    {
        if (!preg_match('/\A[-a-zA-Z0-9_=@,.;]+\z/', $id)) {
            throw new BcaHttpException('Invalid ClientKey' . $id);
        }
    }
    private function validateClientSecret($id)
    {
        if (!preg_match('/\A[-a-zA-Z0-9_=@,.;]+\z/', $id)) {
            throw new BcaHttpException('Invalid ClientSecret' . $id);
        }
    }

    public static function array_implode($glue, $separator, $array)
    {
        if (!is_array($array)) {
            return $array;
        }
        $string = array();
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $val = implode(',', $val);
            }
            $string[] = "{$key}{$glue}{$val}";
        }

        return implode($separator, $string);
    }
}
