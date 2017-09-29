<?php

class BcaHttpException extends Exception
{
}

class BcaHttpInstance
{
    private static $instance      = null;
    private static $corp_id       = '';
    private static $client_id     = '';
    private static $client_secret = '';
    private static $api_key       = '';
    private static $secret_key    = '';

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
            self::$client_secret,
            self::$api_key,
            self::$secret_key
        );
        return self::$instance;
    }
}

/**
 * BCA REST API Library.
 *
 * @author     Pribumi Technology
 * @license    MIT
 * @copyright  (c) 2017, Pribumi Technology
 */
class BcaHttp
{
    public static $VERSION = '1.0.0';

    private $logger = null;

    private $settings = array(
        'corp_id'       => '',
        'client_id'     => '',
        'client_secret' => '',
        'api_key'       => '',
        'secret_key'    => '',
        'scheme'        => 'https',
        'port'          => 443,
        'host'          => 'sandbox.bca.co.id',
        'timezone'      => 'Asia/Jakarta',
        'timeout'       => 30,
        'debug'         => true,
        'development'   => true
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

    public function __construct($corp_id, $client_id, $client_secret, $api_key, $secret_key, $options = array())
    {
        if (!isset($options['host'])) {
            $options['host'] = 'sandbox.bca.co.id';
        }

        if (!isset($options['host'])) {
            $options['port'] = 443;
        }

        if (!isset($options['timezone'])) {
            $options['timezone'] = 'Asia/Jakarta';
        }

        foreach ($options as $key => $value) {
            if (isset($this->settings[$key])) {
                $this->settings[$key] = $value;
            }
        }

        $this->settings['corp_id']       = $corp_id;
        $this->settings['client_id']     = $client_id;
        $this->settings['client_secret'] = $client_secret;
        $this->settings['api_key']       = $api_key;
        $this->settings['secret_key']    = $secret_key;

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
     * Ambil Nilai settings.
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
     * @return string
     */
    private function ddn_domain()
    {
        return $this->settings['scheme'] . '://' . $this->settings['host'] . ':' . $this->settings['port'] . '/';
    }

    /**
     * Generate authentifikasi ke server berupa OAUTH
     *
     * @param array $data
     *
     * @return object
     */
    public function httpAuth()
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

        $data = array('grant_type' => 'client_credentials');
        $body = \Unirest\Request\Body::form($data);
        \Unirest\Request::verifyPeer(false);
        $response = \Unirest\Request::post($full_url, $headers, $body);

        return $response;
    }

    /**
     * Ambil informasi saldo berdasarkan nomor akun BCA.
     *
     * @param string $oauth_token nilai token yang telah didapatkan setelah login
     * @param array $sourceAccountId nomor akun yang akan dicek
     * @param string $corp_id nilai CorporateID yang telah diberikan oleh pihak BCA
     * @param string $bodyToHash array Body yang akan dikirimkan ke Server BCA
     *
     * @return object
     */
    public function getBalanceInfo($oauth_token, $sourceAccountId = [], $corp_id = '', $timeZone = '')
    {
        if ($corp_id == '') {
            $corp_id = $this->settings['corp_id'];
        }

        if ($timeZone == '') {
            $timeZone = $this->settings['timezone'];
        }

        $apikey = $this->settings['api_key'];
        $secret = $this->settings['secret_key'];

        $this->validateOauthKey($apikey);
        $this->validateOauthSecret($secret);

        if (!empty($sourceAccountId)) {
            $arraySplit = implode(",", $sourceAccountId);
        } else {
            throw new BcaHttpException('AccountNumber tidak boleh kosong.');
        }

        $uriSign       = "GET:/banking/v2/corporates/$corp_id/accounts/$arraySplit";
        $isoTime       = self::generateIsoTime($timeZone);
        $emptyArray    = array();
        $authSignature = self::generateSign($uriSign, $oauth_token, $secret, $isoTime, null);

        $headers                    = array();
        $headers['Accept']          = 'application/json';
        $headers['Content-Type']    = 'application/json';
        $headers['Authorization']   = "Bearer $oauth_token";
        $headers['X-BCA-Key']       = $apikey;
        $headers['X-BCA-Timestamp'] = $isoTime;
        $headers['X-BCA-Signature'] = $authSignature;

        $request_path = "banking/v2/corporates/$corp_id/accounts/$arraySplit";
        $domain       = $this->ddn_domain();
        $full_url     = $domain . $request_path;

        \Unirest\Request::verifyPeer(false);
        $data     = array('grant_type' => 'client_credentials');
        $body     = \Unirest\Request\Body::form($data);
        $response = \Unirest\Request::get($full_url, $headers, $body);

        return $response;
    }

    /**
     * Transfer dana kepada akun lain dengan jumlah nominal tertentu.
     *
     * @param string $oauth_token nilai token yang telah didapatkan setelah login
     * @param int $amount nilai dana dalam RUPIAH yang akan ditransfer, Format: 13.2
     * @param string $beneficiaryAccountNumber  BCA Account number to be credited (Destination)
     * @param string $referenceID Sender's transaction reference ID
     * @param string $remark1 Transfer remark for receiver
     * @param string $remark2 ransfer remark for receiver
     * @param string $sourceAccountNumber Source of Fund Account Number
     * @param string $transactionID Transcation ID unique per day (using UTC+07 Time Zone). Format: Number
     * @param string $corp_id nilai CorporateID yang telah diberikan oleh pihak BCA [Optional]
     *
     * @return object
     */
    public function fundTransfers(
        $oauth_token,
        $amount,
        $sourceAccountNumber,
        $beneficiaryAccountNumber,
        $referenceID,
        $remark1,
        $remark2,
        $transactionID,
        $corp_id = '',
        $timeZone = ''
    ) {
        if ($corp_id == '') {
            $corp_id = $this->settings['corp_id'];
        }

        if ($timeZone == '') {
            $timeZone = $this->settings['timezone'];
        }

        $apikey = $this->settings['api_key'];
        $secret = $this->settings['secret_key'];

        $this->validateOauthKey($apikey);
        $this->validateOauthSecret($secret);

        $uriSign    = "POST:/banking/corporates/transfers";
        $isoTime    = self::generateIsoTime($timeZone);
        $emptyArray = array();

        $headers                    = array();
        $headers['Accept']          = 'application/json';
        $headers['Content-Type']    = 'application/json';
        $headers['Authorization']   = "Bearer $oauth_token";
        $headers['X-BCA-Key']       = $apikey;
        $headers['X-BCA-Timestamp'] = $isoTime;

        $request_path = "banking/corporates/transfers";
        $domain       = $this->ddn_domain();
        $full_url     = $domain . $request_path;

        $bodyData                             = array();
        $bodyData['Amount']                   = $amount;
        $bodyData['BeneficiaryAccountNumber'] = $beneficiaryAccountNumber;
        $bodyData['CorporateID']              = $corp_id;
        $bodyData['CurrencyCode']             = 'IDR';
        $bodyData['ReferenceID']              = $referenceID;
        $bodyData['Remark1']                  = strtolower(str_replace(' ', '', $remark1));
        $bodyData['Remark2']                  = strtolower(str_replace(' ', '', $remark2));
        $bodyData['SourceAccountNumber']      = $sourceAccountNumber;
        $bodyData['TransactionDate']          = $isoTime;
        $bodyData['TransactionID']            = $transactionID;

        // Harus disort agar mudah kalkulasi HMAC
        ksort($bodyData);

        // Supaya jgn strip "ReferenceID" "/" jadi "/\" karena HMAC akan menjadi tidak cocok
        $encoderData = json_encode($bodyData, JSON_UNESCAPED_SLASHES);

        $authSignature = self::generateSign($uriSign, $oauth_token, $secret, $isoTime, $bodyData);

        $headers['X-BCA-Signature'] = $authSignature;

        \Unirest\Request::verifyPeer(false);
        $data     = array('grant_type' => 'client_credentials');
        $body     = \Unirest\Request\Body::form($encoderData);
        $response = \Unirest\Request::post($full_url, $headers, $body);

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
        if (!empty($bodyToHash) || $bodyToHash !== null) {
            ksort($bodyToHash);
            $encoderData = json_encode($bodyToHash, JSON_UNESCAPED_SLASHES);
            $hash        = hash("sha256", $encoderData);
        } else {
            $empty = "";
            $hash  = hash("sha256", "");
        }

        $stringToSign   = $url . ":" . $auth_token . ":" . $hash . ":" . $isoTime;
        $auth_signature = hash_hmac('sha256', $stringToSign, $secret_key, false);

        return $auth_signature;
    }

    /**
     * Generate ISO8601 Time.
     *
     * @param string $timeZone Time yang akan dipergunakan
     *
     * @return string
     */
    public static function setTimeZone($timeZone)
    {
        $this->settings['timezone'] = $timeZone;
    }

    /**
     * Generate ISO8601 Time.
     *
     * @param string $timeZone Time yang akan dipergunakan
     *
     * @return string
     */
    public static function getTimeZone($timeZone)
    {
        return $this->settings['timezone'];
    }

    /**
     * Generate ISO8601 Time.
     *
     * @param string $timeZone Time yang akan dipergunakan
     *
     * @return string
     */
    public static function generateIsoTime($timeZone = "Asia/Jakarta")
    {
        $date = \Carbon\Carbon::now($timeZone);
        date_default_timezone_set($timeZone);
        $fmt     = $date->format('Y-m-d\TH:i:s');
        $ISO8601 = sprintf("$fmt.%s%s", substr(microtime(), 2, 3), date('P'));

        return $ISO8601;
    }

    /**
     * Validasi jika clientkey telah di-definsikan.
     *
     * @param string clientkey
     *
     * @return string
     */
    private function validateClientKey($id)
    {
        if (!preg_match('/\A[-a-zA-Z0-9_=@,.;]+\z/', $id)) {
            throw new BcaHttpException('Invalid ClientKey' . $id);
        }
    }

    /**
     * Validasi jika clientsecret telah di-definsikan.
     *
     * @param string clientkey
     *
     * @return string
     */
    private function validateClientSecret($id)
    {
        if (!preg_match('/\A[-a-zA-Z0-9_=@,.;]+\z/', $id)) {
            throw new BcaHttpException('Invalid ClientSecret' . $id);
        }
    }

    /**
     * Validasi jika clientsecret telah di-definsikan.
     *
     * @param string clientkey
     *
     * @return string
     */
    private function validateOauthKey($id)
    {
        if (!preg_match('/\A[-a-zA-Z0-9_=@,.;]+\z/', $id)) {
            throw new BcaHttpException('Invalid ApiKey' . $id);
        }
    }

    /**
     * Validasi jika clientsecret telah di-definsikan.
     *
     * @param string clientkey
     *
     * @return string
     */
    private function validateOauthSecret($id)
    {
        if (!preg_match('/\A[-a-zA-Z0-9_=@,.;]+\z/', $id)) {
            throw new BcaHttpException('Invalid OauthSecret' . $id);
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
