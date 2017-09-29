<?php

class bcaConstructorTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
    }

    public function testClientIdParameter()
    {
        $client_id = 'JDjdkudfsidsfsddjfkdfj';
        $bca       = new BcaHttp('corpid', $client_key, 'client_secret', 'apikey', 'secret');
        $settings  = $bca->getSettings();
        $this->assertEquals(true, $settings['client_id']);
    }

    public function testClientSecretParameter()
    {
        $client_secret = '78dfs78s7df8sdjfksduJJjdsjj';
        $bca           = new BcaHttp('corpid', 'client_key', $client_secret, 'apikey', 'secret');
        $settings      = $bca->getSettings();
        $this->assertEquals(true, $settings['client_secret']);
    }
}
