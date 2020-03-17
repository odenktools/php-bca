<?php

if (!class_exists('PHPUnit_Framework_TestCase') && class_exists('\PHPUnit\Framework\TestCase'))
    class_alias('\PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase');

class BcaHelperTests extends PHPUnit_Framework_TestCase
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

    /**
     * Test fail jika nomor akun kosong.
     *
     * @expectedException \Bca\BcaHttpException
     */
    public function testValidateArr()
    {
        $bca = $this->getMockForAbstractClass('\Bca\BcaHelper', array());
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
        $bca = $this->getMockForAbstractClass('\Bca\BcaHelper', array());
        $arr = array('001', '002', '003', '004', '005', '006', '007', '008', '009', '010', '011', '012', '013', '014', '015', '016', '017', '018', '019', '020', '021', '022');
        $settings = self::invokeMethod($bca, 'validateArray', array($arr));
        $this->assertTrue($settings);
    }

    /**
     * Testing validasi option ARRAY.
     */
    public function testValidateArr3()
    {
        $bca = $this->getMockForAbstractClass('\Bca\BcaHelper', array());
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
        $bca = $this->getMockForAbstractClass('\Bca\BcaHelper', array());
        $arr = '';
        $settings = self::invokeMethod($bca, 'validateArray', array($arr));
        $this->assertTrue($settings);
    }
}
