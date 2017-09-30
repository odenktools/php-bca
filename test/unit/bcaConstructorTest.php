<?php

class bcaConstructorTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
    }

    public function testClientIdParameter()
    {
        $client_id = 'IodfipdifdsfiOAPPOOO';
        $equal     = 'IodfipdifdsfiOAPPOOO';

        $bca      = new BcaHttp('corpid', $client_id, 'client_secret', 'apikey', 'secret');
        $settings = $bca->getSettings();
        $this->assertEquals($equal, $settings['client_id']);
    }

    public function testClientSecretParameter()
    {
        $client_secret = 'YUydsfkhsdfhkuYUYh';
        $equal         = 'YUydsfkhsdfhkuYUYh';

        $bca      = new BcaHttp('corpid', 'client_id', $client_secret, 'apikey', 'secret');
        $settings = $bca->getSettings();
        $this->assertEquals($equal, $settings['client_secret']);
    }

    public function testApiKeyParameter()
    {
        $api_key = 'ofisdifoisfoioOIOIOdfsl';
        $equal   = 'ofisdifoisfoioOIOIOdfsl';

        $bca      = new BcaHttp('corpid', 'client_id', 'client_secret', $api_key, 'secret');
        $settings = $bca->getSettings();
        $this->assertEquals($equal, $settings['api_key']);
    }

    public function testSecretParameter()
    {
        $secret = 'dfisodisofisdfoMMksdflla';
        $equal  = 'dfisodisofisdfoMMksdflla';

        $bca      = new BcaHttp('corpid', 'client_id', 'client_secret', 'api_key', $secret);
        $settings = $bca->getSettings();
        $this->assertEquals($equal, $settings['secret_key']);
    }

    public function testTimeZone()
    {
        BcaHttp::setTimeZone('Asia/Singapore');
        $timezone = BcaHttp::getTimeZone();

        $this->assertEquals($timezone,
            'Asia/Singapore');
    }

    public function testAuth()
    {
        $bca      = new BcaHttp('corpid', 'client_id', 'client_secret', 'api_key', 'secret');
        $response = $bca->httpAuth();
        $this->assertEquals($response->code, 400);
    }

    public function testFund()
    {
        $bca = new BcaHttp('corpid', 'client_id', 'client_secret', 'api_key', 'secret');

        $token = "o7d8qCgfsHwRneFGTHdQsFcS5Obmd26O10iBFRi50Ve8Yb06Ju5xx";

        $response = $bca->fundTransfers($token,
            '50000.00',
            '0201245680',
            '0201245681',
            '12345/PO/2017',
            'Testing Saja Ko',
            'Online Saja Ko',
            '00000001');

        $this->assertEquals($response->code, 400);
    }
}
