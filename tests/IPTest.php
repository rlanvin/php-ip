<?php

class IPTest extends PHPUnit_Framework_TestCase
{
	public function validAddresses()
	{
		return array(
			array('127.0.0.1', '127.0.0.1', 4),
			array('4294967296', '::1:0:0', 6),
			array('2a01:8200::', '2a01:8200::', 6),
			array('::1', '::1', 6),
			array(inet_pton('::1'), '::1', 6),
			array(inet_pton('127.0.0.1'), '127.0.0.1', 4)
		);
	}

	/**
	 * @dataProvider validAddresses
	 */
	public function testConstructValid($ip, $string, $version)
	{
		$instance = IP::create($ip);
		$this->assertEquals($string, (string) $instance);
		$this->assertEquals($version, $instance->getVersion());
	}


	public function invalidAddresses()
	{
		return array(
			array("\t"),
			array("abc"),
			array(12.3),
			array(-12.3),
			array('-1'),
		);
	}

	/**
	 * @dataProvider invalidAddresses
	 * @expectedException InvalidArgumentException
	 */
	public function testConstructInvalid($ip)
	{
		$instance = IP::create($ip);
	}
}