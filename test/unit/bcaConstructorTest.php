<?php

if (!class_exists('PHPUnit_Framework_TestCase') && class_exists('\PHPUnit\Framework\TestCase'))
    class_alias('\PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase');

class bcaConstructorTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
    }

    public static function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    public function testSha256()
    {
        $hash = in_array('sha256', hash_algos());
        $this->assertTrue($hash);
    }

    public function testArrayImplode()
    {
        $params = array();
        $params['SearchBy'] = 'Distance';
        $params['Latitude'] = '123991239';
        $query = \Bca\BcaHttp::arrayImplode('=', '&', $params);
        $equal = 'SearchBy=Distance&Latitude=123991239';
        $this->assertEquals($equal, $query);
    }

    public function testArrayImplode2()
    {
        $params = array();
        $params['SearchBy'] = array('Distance' => 'Hellooooo');
        $params['Latitude'] = '123991239';
        $query = \Bca\BcaHttp::arrayImplode('=', '&', $params);
        $equal = 'SearchBy=Hellooooo&Latitude=123991239';
        $this->assertEquals($equal, $query);
    }

    /**
     * @expectedException \Bca\BcaHttpException
     */
    public function testArrayImplode3()
    {
        $query = \Bca\BcaHttp::arrayImplode('=', '&', 'q');
        $this->assertEquals('Data harus array.', $query);
    }

    /**
     * Testing jika array kosong.
     *
     * @expectedException \Bca\BcaHttpException
     */
    public function testArrayImplode4()
    {
        $query = \Bca\BcaHttp::arrayImplode('=', '&', array());
        $this->assertEquals('parameter array tidak boleh kosong.', $query);
    }

    /**
     * Test fail jika nomor akun kosong.
     *
     * @expectedException \Bca\BcaHttpException
     */
    public function testValidateArr()
    {
        $bca = $this->getMockForAbstractClass('\Bca\BcaHttp', array('corp_id', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123'));
        $arr = array();
        $settings = self::invokeMethod($bca, 'validateArray', array($arr));
        $this->assertTrue($settings);
    }

    /**
     * Test fail nomor akun lebih dari 20.
     *
     * @expectedException \Bca\BcaHttpException
     */
    public function testValidateArr2()
    {
        $bca = $this->getMockForAbstractClass('\Bca\BcaHttp', array('corp_id', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123'));
        $arr = array('001', '002', '003', '004', '005', '006', '007', '008', '009', '010', '011', '012', '013', '014', '015', '016', '017', '018', '019', '020', '021', '022');
        $settings = self::invokeMethod($bca, 'validateArray', array($arr));
        $this->assertTrue($settings);
    }

    /**
     * Testing validasi option ARRAY.
     */
    public function testValidateArr3()
    {
        $bca = $this->getMockForAbstractClass('\Bca\BcaHttp', array('corp_id', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123'));
        $arr = array('001');
        $settings = self::invokeMethod($bca, 'validateArray', array($arr));
        $this->assertTrue($settings);
    }

    /**
     * Test fail jika array adalah string.
     *
     * @expectedException \Bca\BcaHttpException
     */
    public function testValidateArr4()
    {
        $bca = $this->getMockForAbstractClass('\Bca\BcaHttp', array('corp_id', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123'));
        $arr = '';
        $settings = self::invokeMethod($bca, 'validateArray', array($arr));
        $this->assertTrue($settings);
    }

    public function testCurlOptionsCanBeSet()
    {
        $curl_opts = array(CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4);
        $options = array(
            'curl_options' => $curl_opts,
        );
        $bca = new \Bca\BcaHttp('corp_id', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', $options);
        $settings = $bca->getSettings();
        $this->assertEquals($curl_opts, $settings['curl_options']);
    }

    /**
     * Testing constructor HOST.
     */
    public function testConstructHost1()
    {
        $equal = 'sandbox.bca.co.id';
        $bca = new \Bca\BcaHttp('corp_id', 'client_id', 'secret', 'apikey', 'secret');
        $settings = $bca->getSettings();
        $this->assertEquals($equal, $settings['host']);
    }

    /**
     * Testing constructor HOST.
     */
    public function testConstructHost2()
    {
        $options = array();
        $options['host'] = 'xxxx.com';
        $equal = 'xxxx.com';
        $bca = new \Bca\BcaHttp('corp_id', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', $options);
        $settings = $bca->getSettings();
        $this->assertEquals($equal, $settings['host']);
    }

    /**
     * Testing constructor Scheme.
     */
    public function testConstructScheme()
    {
        $options = array();
        $options['scheme'] = 'http';
        $equal = 'http';
        $bca = new \Bca\BcaHttp('corp_id', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', $options);
        $settings = $bca->getSettings();
        $this->assertEquals($equal, $settings['scheme']);
    }

    /**
     * Testing constructor corp_id.
     */
    public function testClientCorpId()
    {
        $options = array();
        $corp_id = 'BCAAPI2016';
        $equal = 'BCAAPI2016';
        $bca = new \Bca\BcaHttp($corp_id, '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', $options);
        $settings = $bca->getSettings();
        $this->assertEquals($equal, $settings['corp_id']);
    }

    /**
     * Testing constructor client_id.
     */
    public function testClientIdParameter()
    {
        $options = array();
        $client_id = '1234567-1234-1234-1345-123456789123';
        $equal = '1234567-1234-1234-1345-123456789123';

        $bca = new \Bca\BcaHttp('corpid', $client_id, '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', $options);
        $settings = $bca->getSettings();
        $this->assertEquals($equal, $settings['client_id']);
    }

    /**
     * Testing constructor client_secret.
     */
    public function testClientSecretParameter()
    {
        $options = array();
        $client_secret = '1234567-1234-1234-1345-123456789123';
        $equal = '1234567-1234-1234-1345-123456789123';

        $bca = new \Bca\BcaHttp('corpid', 'client_id', $client_secret, 'apikey', 'secret', $options);
        $settings = $bca->getSettings();
        $this->assertEquals($equal, $settings['client_secret']);
    }

    /**
     * Testing constructor api_key.
     */
    public function testApiKeyParameter()
    {
        $options = array();
        $api_key = '1234567-1234-1234-1345-123456789123';
        $equal = '1234567-1234-1234-1345-123456789123';

        $bca = new \Bca\BcaHttp('corpid', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', $api_key, '1234567-1234-1234-1345-123456789123', $options);
        $settings = $bca->getSettings();
        $this->assertEquals($equal, $settings['api_key']);
    }

    /**
     * Testing constructor secret_key.
     */
    public function testSecretParameter()
    {
        $secret = '1234567-1234-1234-1345-123456789123';
        $equal = '1234567-1234-1234-1345-123456789123';

        $bca = new \Bca\BcaHttp('corpid', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', $secret);
        $settings = $bca->getSettings();
        $this->assertEquals($equal, $settings['secret_key']);
    }

    /**
     * Testing set timezone.
     */
    public function testTimeZone()
    {
        \Bca\BcaHttp::setTimeZone('Asia/Singapore');
        $timezone = \Bca\BcaHttp::getTimeZone();

        $this->assertEquals(
            $timezone,
            'Asia/Singapore'
        );
    }

    /**
     * Testing set timezone.
     */
    public function testSetTimeout()
    {
        \Bca\BcaHttp::setTimeOut(80);
        $timeout = \Bca\BcaHttp::getTimeOut();

        $this->assertEquals(
            $timeout,
            80
        );
    }

    /**
     * Testing constructor HOST.
     */
    public function testConstructPort()
    {
        $options = array();
        $options['port'] = 443;
        $equal = 443;
        $bca = new \Bca\BcaHttp('corp_id', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', $options);
        $settings = $bca->getSettings();
        $this->assertEquals($equal, $settings['port']);
    }

    /**
     * Testing constructor HOST.
     */
    public function testConstructTimezone()
    {
        $options = array();
        $options['timezone'] = 'Asia/Jakarta';
        $equal = 'Asia/Jakarta';
        $bca = new \Bca\BcaHttp('corp_id', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', $options);
        $settings = $bca->getSettings();
        $this->assertEquals($equal, $settings['timezone']);
    }

    /**
     * Testing constructor HOST.
     */
    public function testConstructTimeout()
    {
        $options = array();
        $options['timeout'] = 60;
        $equal = 60;
        $bca = new \Bca\BcaHttp('corp_id', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', $options);
        $settings = $bca->getSettings();
        $this->assertEquals($equal, $settings['timeout']);
    }

    /**
     * Testing Authentikasi.
     */
    public function testAuth()
    {
        $options = array();
        $bca = new \Bca\BcaHttp('corpid', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', $options);
        $response = $bca->httpAuth();
        $this->assertEquals($response->code, 400);
    }

    /**
     * Testing Fund Transfer.
     */
    public function testFund()
    {
        $options = array();
        $bca = new \Bca\BcaHttp('corpid', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', $options);

        $token = "o7d8qCgfsHwRneFGTHdQsFcS5Obmd26O10iBFRi50Ve8Yb06Ju5xx";

        $response = $bca->fundTransfers(
            $token,
            '50000.00',
            '0201245680',
            '0201245681',
            '12345/PO/2017',
            'Testing Saja Ko',
            'Online Saja Ko',
            '00000001'
        );

        $this->assertEquals($response->code, 400);
    }

    /**
     * Testing Fund Transfer Menggunakan domain yang salah.
     */
    public function testFund2()
    {
        $options = array();
        $options['host'] = 'abcdefgh.com';
        $bca = new \Bca\BcaHttp('corpid', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', $options);

        $token = "o7d8qCgfsHwRneFGTHdQsFcS5Obmd26O10iBFRi50Ve8Yb06Ju5xx";

        try {
            $response = $bca->fundTransfers(
                $token,
                '50000.00',
                '0201245680',
                '0201245681',
                '12345/PO/2017',
                'Testing Saja Ko',
                'Online Saja Ko',
                '00000001'
            );
        } catch (\Unirest\Exception $ex) {
            $this->assertNotEquals($ex->getMessage(), 'Failed to connect to abcdefgh.com');
        }
    }

    /**
     * Testing Generate Signature.
     */
    public function testGenerateSign()
    {
        $token = "NopUsBuSbT3eNrQTfcEZN2aAL52JT1SlRgoL1MIslsX5gGIgv4YUf";
        $arrayAccNumber = array('0063001004');
        $arraySplit = implode(",", $arrayAccNumber);
        $uriSign = "GET:/banking/v2/corporates/corpid/accounts/$arraySplit";
        $isoTime = "2017-09-30T22:03:35.800+07:00";
        $authSignature = \Bca\BcaHttp::generateSign($uriSign, $token, "9db65b91-01ff-46ec-9274-3f234b677450", $isoTime, null);

        $output = "761eaec0e544c9cf5010b406ade39228ab182401e57f17fc54b9daa5ad99d0d6";

        $this->assertEquals($authSignature, $output);
    }

    /**
     * Testing Generate Signature Nomor 2.
     */
    public function testGenerateSign2()
    {
        $token = "NopUsBuSbT3eNrQTfcEZN2aAL52JT1SlRgoL1MIslsX5gGIgv4YUf";
        $arrayAccNumber = array('0063001004');
        $arraySplit = implode(",", $arrayAccNumber);
        $uriSign = "GET:/banking/v2/corporates/corpid/accounts/$arraySplit";
        $isoTime = "2019-02-30T22:03:35.800+07:00";

        $bodyData = array();
        $bodyData['Amount'] = "100000.00";
        $bodyData['BeneficiaryAccountNumber'] = strtolower(str_replace(' ', '', "8329389"));
        $bodyData['CorporateID'] = strtolower(str_replace(' ', '', "8293489283499"));
        $bodyData['CurrencyCode'] = "idr";
        $bodyData['ReferenceID'] = strtolower(str_replace(' ', '', ""));
        $bodyData['Remark1'] = strtolower(str_replace(' ', '', "Ini adalah remark1"));
        $bodyData['Remark2'] = strtolower(str_replace(' ', '', "Ini adalah remark2"));
        $bodyData['SourceAccountNumber'] = strtolower(str_replace(' ', '', "09202990"));
        $bodyData['TransactionDate'] = $isoTime;
        $bodyData['TransactionID'] = strtolower(str_replace(' ', '', "0020292"));

        $authSignature = \Bca\BcaHttp::generateSign($uriSign, $token, "9db65b91-01ff-46ec-9274-3f234b677450", $isoTime, $bodyData);

        $output = "1878f0eedcd93ff53054c8fc9ea271a29c99ea2f752f636c1cc765948009a90b";

        $this->assertEquals($authSignature, $output);
    }

    /**
     * Testing Mendapatkan Lokasi ATM BCA.
     */
    public function testAtmLocation()
    {
        $options = array();
        $bca = new \Bca\BcaHttp('corpid', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', $options);
        $token = "o7d8qCgfsHwRneFGTHdQsFcS5Obmd26O10iBFRi50Ve8Yb06Ju5xx";

        $response = $bca->getAtmLocation($token, "-6.1900718", "106.797190", '10', '20');

        $this->assertEquals($response->code, 400);
    }

    /**
     * Testing Mendapatkan data Forex.
     */
    public function testGetForex()
    {
        $options = array();
        $token = "NopUsBuSbT3eNrQTfcEZN2aAL52JT1SlRgoL1MIslsX5gGIgv4YUf";
        $bca = new \Bca\BcaHttp('corpid', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', $options);
        $response = $bca->getForexRate($token, 'bn', 'usd');

        $this->assertEquals($response->code, 400);
    }

    /**
     * Testing Mendapatkan data Forex.
     */
    public function testGetAccountStatement()
    {
        $options = array();
        $token = "NopUsBuSbT3eNrQTfcEZN2aAL52JT1SlRgoL1MIslsX5gGIgv4YUf";
        $bca = new \Bca\BcaHttp('corpid', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', $options);
        $response = $bca->getAccountStatement($token, '0201245680', '2016-08-29', '2016-09-01');
        $this->assertEquals($response->code, 400);
    }

    public function testGetBalanceInfos()
    {
        $options = array();
        $token = "NopUsBuSbT3eNrQTfcEZN2aAL52JT1SlRgoL1MIslsX5gGIgv4YUf";
        $bca = new \Bca\BcaHttp('corp_id', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', $options);
        $arrayAccNumber = array('0063001004');
        $response = $bca->getBalanceInfo($token, $arrayAccNumber);
        $this->assertEquals($response->code, 400);
    }

    public function testGetDepositRate()
    {
        $options = array();
        $token = "NopUsBuSbT3eNrQTfcEZN2aAL52JT1SlRgoL1MIslsX5gGIgv4YUf";
        $bca = new \Bca\BcaHttp('corp_id', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', '1234567-1234-1234-1345-123456789123', $options);
        $response = $bca->getDepositRate($token);
        $this->assertEquals($response->code, 400);
    }

    /**
     * Testing constructor HOST.
     */
    public function testClientStaticHost()
    {
        $equal = 'xxxx.com';
        $bca = \Bca\BcaHttp::setHostName('xxxx.com');
        $this->assertEquals($equal, \Bca\BcaHttp::getHostName());
    }

    /**
     * Testing constructor PORT.
     */
    public function testClientStaticPort()
    {
        $equal = 443;
        \Bca\BcaHttp::setPort(443);
        $this->assertEquals($equal, \Bca\BcaHttp::getPort());
    }

    /**
     * Testing set scheme.
     */
    public function testScheme()
    {
        \Bca\BcaHttp::setScheme('http');
        $scheme = \Bca\BcaHttp::getScheme();

        $this->assertEquals(
            $scheme,
            'http'
        );
    }

    /**
     * Testing set scheme.
     */
    public function testCurl()
    {
        $curl_opts = array(CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4);
        \Bca\BcaHttp::setCurlOptions($curl_opts);
        $scheme = \Bca\BcaHttp::getCurlOptions();
        $this->assertEquals(
            $scheme,
            array(
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSLVERSION => 6,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT => 60
            )
        );
    }
}
