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
}
