<?php

namespace Bca;

use Unirest\Request;
use Unirest\Request\Body;

/**
 * BCA REST API Library.
 *
 * @author     Pribumi Technology
 * @license    MIT
 * @copyright  (c) 2017, Pribumi Technology
 */
class BcaHttp
{
    public static $VERSION = '2.3.1';

    /**
     * Default Timezone.
     *
     * @var string
     */
    private static $timezone = 'Asia/Jakarta';

    /**
     * Default BCA Port.
     *
     * @var int
     */
    private static $port = 443;

    /**
     * Default BCA Host.
     *
     * @var string
     */
    private static $hostName = 'sandbox.bca.co.id';

    /**
     * Default BCA Host.
     *
     * @var string
     */
    private static $scheme = 'https';

    /**
     * Timeout curl.
     *
     * @var int
     */
    private static $timeOut = 60;

    /**
     * Default Curl Options.
     *
     * @var int
     */
    private static $curlOptions = array(
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSLVERSION => 6,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 60
    );

    /**
     * Default BCA Settings.
     *
     * @var array
     */
    protected $settings = array(
        'corp_id' => '',
        'client_id' => '',
        'client_secret' => '',
        'api_key' => '',
        'secret_key' => '',
        'curl_options' => array(),
        // Backward compatible
        'host' => 'sandbox.bca.co.id',
        'scheme' => 'https',
        'timeout' => 60,
        'port' => 443,
        'timezone' => 'Asia/Jakarta',
        // New Options
        'options' => array(
            'host' => 'sandbox.bca.co.id',
            'scheme' => 'https',
            'timeout' => 60,
            'port' => 443,
            'timezone' => 'Asia/Jakarta'
        )
    );

    /**
     * Default Constructor.
     *
     * @param string $corp_id nilai corp id
     * @param string $client_id nilai client key
     * @param string $client_secret nilai client secret
     * @param string $api_key niali oauth key
     * @param string $secret_key nilai oauth secret
     * @param array $options opsi ke server bca
     */
    public function __construct($corp_id, $client_id, $client_secret, $api_key, $secret_key, array $options = [])
    {
        // Required parameters.
        $this->settings['corp_id'] = $corp_id;
        $this->settings['client_id'] = $client_id;
        $this->settings['client_secret'] = $client_secret;
        $this->settings['api_key'] = $api_key;
        $this->settings['secret_key'] = $secret_key;
        $this->settings['host'] =
            preg_replace('/http[s]?\:\/\//', '', $this->settings['host'], 1);

        foreach ($options as $key => $value) {
            if (isset($this->settings[$key])) {
                $this->settings[$key] = $value;
            }
        }

        // Setup optional scheme, if scheme is empty
        if (isset($options['scheme'])) {
            $this->settings['scheme'] = $options['scheme'];
            $this->settings['options']['scheme'] = $options['scheme'];
        } else {
            $this->settings['scheme'] = self::getScheme();
            $this->settings['options']['scheme'] = self::getScheme();
        }

        // Setup optional host, if host is empty
        if (isset($options['host'])) {
            $this->settings['host'] = $options['host'];
            $this->settings['options']['host'] = $options['host'];
        } else {
            $this->settings['host'] = self::getHostName();
            $this->settings['options']['host'] = self::getHostName();
        }

        // Setup optional port, if port is empty
        if (isset($options['port'])) {
            $this->settings['port'] = $options['port'];
            $this->settings['options']['port'] = $options['port'];
        } else {
            $this->settings['port'] = self::getPort();
            $this->settings['options']['port'] = self::getPort();
        }

        // Setup optional timezone, if timezone is empty
        if (isset($options['timezone'])) {
            $this->settings['timezone'] = $options['timezone'];
            $this->settings['options']['timezone'] = $options['timezone'];
        } else {
            $this->settings['timezone'] = self::getHostName();
            $this->settings['options']['timezone'] = self::getHostName();
        }

        // Setup optional timeout, if timeout is empty
        if (isset($options['timeout'])) {
            $this->settings['timeout'] = $options['timeout'];
            $this->settings['options']['timeout'] = $options['timeout'];
        } else {
            $this->settings['timeout'] = self::getTimeOut();
            $this->settings['options']['timeout'] = self::getTimeOut();
        }

        // Set Default Curl Options.
        Request::curlOpts(self::$curlOptions);

        // Set custom curl options
        if (!empty($this->settings['curl_options'])) {
            $data = BcaHelper::mergeCurlOptions(self::$curlOptions, $this->settings['curl_options']);
            Request::curlOpts($data);
        }
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
     *
     * @return string
     */
    private function ddnDomain()
    {
        return $this->settings['scheme'] . '://' . $this->settings['host'] . ':' . $this->settings['port'] . '/';
    }

    /**
     * Generate authentifikasi ke server berupa OAUTH.
     *
     * @return \Unirest\Response
     */
    public function httpAuth()
    {
        $client_id = $this->settings['client_id'];
        $client_secret = $this->settings['client_secret'];

        $headerToken = base64_encode("$client_id:$client_secret");

        $headers = array('Accept' => 'application/json', 'Authorization' => "Basic $headerToken");

        $request_path = "api/oauth/token";
        $domain = $this->ddnDomain();
        $full_url = $domain . $request_path;

        $data = array('grant_type' => 'client_credentials');
        $body = Body::form($data);
        $response = Request::post($full_url, $headers, $body);

        return $response;
    }

    /**
     * Ambil informasi saldo berdasarkan nomor akun BCA.
     *
     * @param string $oauth_token nilai token yang telah didapatkan setelah login
     * @param array $sourceAccountId nomor akun yang akan dicek
     *
     * @throws BcaHttpException error
     * @return \Unirest\Response
     */
    public function getBalanceInfo($oauth_token, $sourceAccountId = [])
    {
        $corp_id = $this->settings['corp_id'];

        BcaHelper::validateArray($sourceAccountId);

        ksort($sourceAccountId);
        $arraySplit = implode(",", $sourceAccountId);
        $arraySplit = urlencode($arraySplit);

        $uriSign = "GET:/banking/v3/corporates/$corp_id/accounts/$arraySplit";
        $isoTime = BcaHelper::generateIsoTime(self::getTimeZone());
        $authSignature = BcaHelper::generateSign($uriSign, $oauth_token, $this->settings['secret_key'], $isoTime, null);

        $headers = array();
        $headers['Accept'] = 'application/json';
        $headers['Content-Type'] = 'application/json';
        $headers['Authorization'] = "Bearer $oauth_token";
        $headers['X-BCA-Key'] = $this->settings['api_key'];
        $headers['X-BCA-Timestamp'] = $isoTime;
        $headers['X-BCA-Signature'] = $authSignature;

        $request_path = "banking/v3/corporates/$corp_id/accounts/$arraySplit";
        $domain = $this->ddnDomain();
        $full_url = $domain . $request_path;

        $data = array('grant_type' => 'client_credentials');

        $body = Body::form($data);
        $response = Request::get($full_url, $headers, $body);

        return $response;
    }

    /**
     * Ambil Daftar transaksi pertanggal.
     *
     * @param string $oauth_token nilai token yang telah didapatkan setelah login
     * @param array $sourceAccount nomor akun yang akan dicek
     * @param string $startDate tanggal awal
     * @param string $endDate tanggal akhir
     * @param string $corp_id nilai CorporateID yang telah diberikan oleh pihak BCA
     *
     * @return \Unirest\Response
     */
    public function getAccountStatement($oauth_token, $sourceAccount, $startDate, $endDate)
    {
        $corp_id = $this->settings['corp_id'];
        $uriSign = "GET:/banking/v3/corporates/$corp_id/accounts/$sourceAccount/statements?EndDate=$endDate&StartDate=$startDate";
        $isoTime = BcaHelper::generateIsoTime(self::getTimeZone());
        $authSignature = BcaHelper::generateSign($uriSign, $oauth_token, $this->settings['secret_key'], $isoTime, null);
        $headers = array();
        $headers['Accept'] = 'application/json';
        $headers['Content-Type'] = 'application/json';
        $headers['Authorization'] = "Bearer $oauth_token";
        $headers['X-BCA-Key'] = $this->settings['api_key'];
        $headers['X-BCA-Timestamp'] = $isoTime;
        $headers['X-BCA-Signature'] = $authSignature;
        $request_path = "banking/v3/corporates/$corp_id/accounts/$sourceAccount/statements?EndDate=$endDate&StartDate=$startDate";
        $domain = $this->ddnDomain();
        $full_url = $domain . $request_path;

        $data = array('grant_type' => 'client_credentials');
        $body = Body::form($data);
        $response = Request::get($full_url, $headers, $body);
        return $response;
    }

    /**
     * Ambil informasi ATM berdasarkan lokasi GEO.
     *
     * @param string $oauth_token nilai token yang telah didapatkan setelah login
     * @param string $latitude Langitude GPS
     * @param string $longitude Longitude GPS
     * @param string $count Jumlah ATM BCA yang akan ditampilkan
     * @param string $radius Nilai radius dari lokasi GEO
     *
     * @throws BcaHttpException error
     * @return \Unirest\Response
     */
    public function getAtmLocation(
        $oauth_token,
        $latitude,
        $longitude,
        $count = '10',
        $radius = '20'
    )
    {
        $params = array();
        $params['SearchBy'] = 'Distance';
        $params['Latitude'] = $latitude;
        $params['Longitude'] = $longitude;
        $params['Count'] = $count;
        $params['Radius'] = $radius;
        ksort($params);

        $auth_query_string = BcaHelper::arrayImplode('=', '&', $params);

        $uriSign = "GET:/general/info-bca/atm?$auth_query_string";
        $isoTime = BcaHelper::generateIsoTime(self::getTimeZone());
        $authSignature = BcaHelper::generateSign($uriSign, $oauth_token, $this->settings['secret_key'], $isoTime, null);

        $headers = array();
        $headers['Accept'] = 'application/json';
        $headers['Content-Type'] = 'application/json';
        $headers['Authorization'] = "Bearer $oauth_token";
        $headers['X-BCA-Key'] = $this->settings['api_key'];
        $headers['X-BCA-Timestamp'] = $isoTime;
        $headers['X-BCA-Signature'] = $authSignature;

        $request_path = "general/info-bca/atm?SearchBy=Distance&Latitude=$latitude&Longitude=$longitude&Count=$count&Radius=$radius";
        $domain = $this->ddnDomain();
        $full_url = $domain . $request_path;

        $data = array('grant_type' => 'client_credentials');
        $body = Body::form($data);
        $response = Request::get($full_url, $headers, $body);

        return $response;
    }

    /**
     * Ambil KURS mata uang.
     *
     * @param string $oauth_token nilai token yang telah didapatkan setelah login
     * @param string $rateType type rate
     * @param string $currency Mata uang
     *
     * @throws BcaHttpException error
     * @return \Unirest\Response
     */
    public function getForexRate(
        $oauth_token,
        $rateType = 'e-rate',
        $currency = 'USD'
    )
    {
        $params = array();
        $params['RateType'] = strtolower($rateType);
        $params['Currency'] = strtoupper($currency);
        ksort($params);

        $auth_query_string = BcaHelper::arrayImplode('=', '&', $params);

        $uriSign = "GET:/general/rate/forex?$auth_query_string";
        $isoTime = BcaHelper::generateIsoTime(self::getTimeZone());
        $authSignature = BcaHelper::generateSign($uriSign, $oauth_token, $this->settings['secret_key'], $isoTime, null);

        $headers = array();
        $headers['Accept'] = 'application/json';
        $headers['Content-Type'] = 'application/json';
        $headers['Authorization'] = "Bearer $oauth_token";
        $headers['X-BCA-Key'] = $this->settings['api_key'];
        $headers['X-BCA-Timestamp'] = $isoTime;
        $headers['X-BCA-Signature'] = $authSignature;

        $request_path = "general/rate/forex?$auth_query_string";
        $domain = $this->ddnDomain();
        $full_url = $domain . $request_path;

        $data = array('grant_type' => 'client_credentials');
        $body = Body::form($data);
        $response = Request::get($full_url, $headers, $body);

        return $response;
    }

    /**
     * Transfer dana kepada akun lain dengan jumlah nominal tertentu.
     *
     * @param string $oauth_token nilai token yang telah didapatkan setelah login
     * @param int $amount nilai dana dalam RUPIAH yang akan ditransfer, Format: 13.2
     * @param string $beneficiaryAccountNumber BCA Account number to be credited (Destination)
     * @param string $referenceID Sender's transaction reference ID
     * @param string $remark1 Transfer remark for receiver
     * @param string $remark2 ransfer remark for receiver
     * @param string $sourceAccountNumber Source of Fund Account Number
     * @param string $transactionID Transcation ID unique per day (using UTC+07 Time Zone). Format: Number
     * @param string $currencyCode nilai MATA Uang [Optional]
     *
     * @return \Unirest\Response
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
        $currencyCode = 'idr'
    )
    {
        $uriSign = "POST:/banking/corporates/transfers";

        $isoTime = BcaHelper::generateIsoTime(self::getTimeZone());

        $headers = array();
        $headers['Accept'] = 'application/json';
        $headers['Content-Type'] = 'application/json';
        $headers['Authorization'] = "Bearer $oauth_token";
        $headers['X-BCA-Key'] = $this->settings['api_key'];
        $headers['X-BCA-Timestamp'] = $isoTime;

        $request_path = "banking/corporates/transfers";
        $domain = $this->ddnDomain();
        $full_url = $domain . $request_path;

        $bodyData = array();
        $bodyData['Amount'] = $amount;
        $bodyData['BeneficiaryAccountNumber'] = strtolower(str_replace(' ', '', $beneficiaryAccountNumber));
        $bodyData['CorporateID'] = strtolower(str_replace(' ', '', $this->settings['corp_id']));
        $bodyData['CurrencyCode'] = $currencyCode;
        $bodyData['ReferenceID'] = strtolower(str_replace(' ', '', $referenceID));
        $bodyData['Remark1'] = strtolower(str_replace(' ', '', $remark1));
        $bodyData['Remark2'] = strtolower(str_replace(' ', '', $remark2));
        $bodyData['SourceAccountNumber'] = strtolower(str_replace(' ', '', $sourceAccountNumber));
        $bodyData['TransactionDate'] = $isoTime;
        $bodyData['TransactionID'] = strtolower(str_replace(' ', '', $transactionID));

        // Harus disort agar mudah kalkulasi HMAC
        ksort($bodyData);

        $authSignature = BcaHelper::generateSign($uriSign, $oauth_token, $this->settings['secret_key'], $isoTime, $bodyData);

        $headers['X-BCA-Signature'] = $authSignature;

        // Supaya jgn strip "ReferenceID" "/" jadi "/\" karena HMAC akan menjadi tidak cocok
        $encoderData = json_encode($bodyData, JSON_UNESCAPED_SLASHES);

        $body = Body::form($encoderData);
        $response = Request::post($full_url, $headers, $body);

        return $response;
    }

    /**
     * Realtime deposit untuk produk BCA.
     *
     * @param string $oauth_token nilai token yang telah didapatkan setelah login
     *
     * @return \Unirest\Response
     */
    public function getDepositRate($oauth_token)
    {
        $uriSign = "GET:/general/rate/deposit";
        $isoTime = BcaHelper::generateIsoTime(self::getTimeZone());
        $authSignature = BcaHelper::generateSign($uriSign, $oauth_token, $this->settings['secret_key'], $isoTime, null);

        $headers = array();
        $headers['Accept'] = 'application/json';
        $headers['Content-Type'] = 'application/json';
        $headers['Authorization'] = "Bearer $oauth_token";
        $headers['X-BCA-Key'] = $this->settings['api_key'];
        $headers['X-BCA-Timestamp'] = $isoTime;
        $headers['X-BCA-Signature'] = $authSignature;

        $request_path = "general/rate/deposit";
        $domain = $this->ddnDomain();
        $full_url = $domain . $request_path;

        $data = array('grant_type' => 'client_credentials');

        $body = Body::form($data);
        $response = Request::get($full_url, $headers, $body);

        return $response;
    }

    /**
     * Set TimeZone.
     *
     * @param string $timeZone Time yang akan dipergunakan.
     *
     * @return string
     */
    public static function setTimeZone($timeZone)
    {
        self::$timezone = $timeZone;
        return self::$timezone;
    }

    /**
     * Get TimeZone.
     *
     * @return string
     */
    public static function getTimeZone()
    {
        return self::$timezone;
    }

    /**
     * Set nama domain BCA yang akan dipergunakan.
     *
     * @param string $hostName nama domain BCA yang akan dipergunakan.
     *
     * @return string
     */
    public static function setHostName($hostName)
    {
        self::$hostName = $hostName;
        return self::$hostName;
    }

    /**
     * Ambil nama domain BCA yang akan dipergunakan.
     *
     * @return string
     */
    public static function getHostName()
    {
        return self::$hostName;
    }

    /**
     * Ambil maximum execution time.
     *
     * @return string
     */
    public static function getTimeOut()
    {
        return self::$timeOut;
    }

    /**
     * Ambil nama domain BCA yang akan dipergunakan.
     *
     * @return string
     */
    public static function getCurlOptions()
    {
        return self::$curlOptions;
    }

    /**
     * Setup curl options.
     *
     * @param array $curlOpts
     * @return array
     */
    public static function setCurlOptions(array $curlOpts = [])
    {
        $data = BcaHelper::mergeCurlOptions(self::$curlOptions, $curlOpts);
        self::$curlOptions = $data;
        return self::$curlOptions;
    }

    /**
     * Set Ambil maximum execution time.
     *
     * @param int $timeOut timeout in milisecond.
     *
     * @return string
     */
    public static function setTimeOut($timeOut)
    {
        self::$timeOut = $timeOut;
        return self::$timeOut;
    }

    /**
     * Set BCA port
     *
     * @param int $port Port yang akan dipergunakan
     *
     * @return int
     */
    public static function setPort($port)
    {
        self::$port = $port;
        return self::$port;
    }

    /**
     * Get BCA port
     *
     * @return int
     */
    public static function getPort()
    {
        return self::$port;
    }

    /**
     * Set BCA Schema
     *
     * @param int $scheme Scheme yang akan dipergunakan
     *
     * @return string
     */
    public static function setScheme($scheme)
    {
        self::$scheme = $scheme;
        return self::$scheme;
    }

    /**
     * Get BCA Schema
     *
     * @return string
     */
    public static function getScheme()
    {
        return self::$scheme;
    }
}
