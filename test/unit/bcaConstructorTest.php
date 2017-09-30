<?php

class bcaConstructorTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
    }

    public function testClientGetHost()
    {
        $equal    = 'sandbox.bca.co.id';
        $bca      = new \Bca\BcaHttp('corp_id', 'client_id', 'secret', 'apikey', 'secret');
        $settings = $bca->getSettings();
        $this->assertEquals($equal, $settings['host']);
    }

    public function testClientGetOptions1()
    {
        $options         = array();
        $options['host'] = 'xxxx.com';
        $equal           = 'xxxx.com';
        $bca             = new \Bca\BcaHttp('corp_id', 'client_id', 'secret', 'apikey', 'secret', $options);
        $settings        = $bca->getSettings();
        $this->assertEquals($equal, $settings['host']);
    }

    public function testClientCorpId()
    {
        $corp_id = 'BCAAPI2016';
        $equal   = 'BCAAPI2016';

        $bca      = new \Bca\BcaHttp($corp_id, 'client_id', 'secret', 'apikey', 'secret');
        $settings = $bca->getSettings();
        $this->assertEquals($equal, $settings['corp_id']);
    }

    public function testClientIdParameter()
    {
        $client_id = 'IodfipdifdsfiOAPPOOO';
        $equal     = 'IodfipdifdsfiOAPPOOO';

        $bca      = new \Bca\BcaHttp('corpid', $client_id, 'client_secret', 'apikey', 'secret');
        $settings = $bca->getSettings();
        $this->assertEquals($equal, $settings['client_id']);
    }

    public function testClientSecretParameter()
    {
        $client_secret = 'YUydsfkhsdfhkuYUYh';
        $equal         = 'YUydsfkhsdfhkuYUYh';

        $bca      = new \Bca\BcaHttp('corpid', 'client_id', $client_secret, 'apikey', 'secret');
        $settings = $bca->getSettings();
        $this->assertEquals($equal, $settings['client_secret']);
    }

    public function testApiKeyParameter()
    {
        $api_key = 'ofisdifoisfoioOIOIOdfsl';
        $equal   = 'ofisdifoisfoioOIOIOdfsl';

        $bca      = new \Bca\BcaHttp('corpid', 'client_id', 'client_secret', $api_key, 'secret');
        $settings = $bca->getSettings();
        $this->assertEquals($equal, $settings['api_key']);
    }

    public function testSecretParameter()
    {
        $secret = 'dfisodisofisdfoMMksdflla';
        $equal  = 'dfisodisofisdfoMMksdflla';

        $bca      = new \Bca\BcaHttp('corpid', 'client_id', 'client_secret', 'api_key', $secret);
        $settings = $bca->getSettings();
        $this->assertEquals($equal, $settings['secret_key']);
    }

    public function testTimeZone()
    {
        \Bca\BcaHttp::setTimeZone('Asia/Singapore');
        $timezone = \Bca\BcaHttp::getTimeZone();

        $this->assertEquals(
            $timezone,
            'Asia/Singapore'
        );
    }

    public function testAuth()
    {
        $bca      = new \Bca\BcaHttp('corpid', 'client_id', 'client_secret', 'api_key', 'secret');
        $response = $bca->httpAuth();
        $this->assertEquals($response->code, 400);
    }

    public function testFund()
    {
        $bca = new \Bca\BcaHttp('corpid', 'client_id', 'client_secret', 'api_key', 'secret');

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

    public function testGenerateSign()
    {
        $token          = "NopUsBuSbT3eNrQTfcEZN2aAL52JT1SlRgoL1MIslsX5gGIgv4YUf";
        $arrayAccNumber = array('0063001004');
        $arraySplit     = implode(",", $arrayAccNumber);
        $uriSign        = "GET:/banking/v2/corporates/corpid/accounts/$arraySplit";
        $isoTime        = "2017-09-30T22:03:35.800+07:00";
        $authSignature  = \Bca\BcaHttp::generateSign($uriSign, $token, "9db65b91-01ff-46ec-9274-3f234b677450", $isoTime, null);

        $output = "761eaec0e544c9cf5010b406ade39228ab182401e57f17fc54b9daa5ad99d0d6";

        $this->assertEquals($authSignature, $output);
    }

    public function testAtmLocation()
    {
        $token = "NopUsBuSbT3eNrQTfcEZN2aAL52JT1SlRgoL1MIslsX5gGIgv4YUf";
        $bca   = new \Bca\BcaHttp('corpid', 'client_id', 'client_secret', 'api_key', 'secret');

        $token = "o7d8qCgfsHwRneFGTHdQsFcS5Obmd26O10iBFRi50Ve8Yb06Ju5xx";

        $response = $bca->getAtmLocation($token, "-6.1900718", "106.797190", '10', '20');

        $this->assertEquals($response->code, 400);
    }

    public function testGetForex()
    {
        $token    = "NopUsBuSbT3eNrQTfcEZN2aAL52JT1SlRgoL1MIslsX5gGIgv4YUf";
        $bca      = new \Bca\BcaHttp('corpid', 'client_id', 'client_secret', 'api_key', 'secret');
        $response = $bca->getForexRate($token, 'bn', 'usd');

        $this->assertEquals($response->code, 400);
    }

    public function testGetAccountStatement()
    {
        $token    = "NopUsBuSbT3eNrQTfcEZN2aAL52JT1SlRgoL1MIslsX5gGIgv4YUf";
        $bca      = new \Bca\BcaHttp('corpid', 'client_id', 'client_secret', 'api_key', 'secret');
        $response = $bca->getAccountStatement($token, '0201245680', '2016-08-29', '2016-09-01');
        $this->assertEquals($response->code, 400);
    }

    public function testGetBalanceInfos()
    {
        $token          = "NopUsBuSbT3eNrQTfcEZN2aAL52JT1SlRgoL1MIslsX5gGIgv4YUf";
        $bca            = new \Bca\BcaHttp('corp_id', 'client_id', 'client_secret', 'api_key', 'secret');
        $arrayAccNumber = array('0063001004');
        $response       = $bca->getBalanceInfo($token, $arrayAccNumber);
        $this->assertEquals($response->code, 400);
    }
}
