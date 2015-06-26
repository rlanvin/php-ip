<?php

class IPv4Test extends PHPUnit_Framework_TestCase
{
	// see http://www.miniwebtool.com/ip-address-to-binary-converter/
	// and http://www.miniwebtool.com/ip-address-to-hex-converter
	public function validAddresses()
	{
		$values = array(
			array('127.0.0.1', '127.0.0.1', '2130706433', '01111111000000000000000000000001', '7f000001'),
			array('10.0.0.1', '10.0.0.1', '167772161','00001010000000000000000000000001', 'a000001'),
			array('0.0.0.0', '0.0.0.0', '0', '00000000000000000000000000000000', '00000000'),
			array('0.0.0.1', '0.0.0.1', '1', '00000000000000000000000000000001', '00000001'),
			array('255.255.255.254', '255.255.255.254', '4294967294', '11111111111111111111111111111110', 'fffffffe'),
			array('255.255.255.255', '255.255.255.255', '4294967295', '11111111111111111111111111111111', 'ffffffff'),
			array(ip2long('10.0.0.1'), '10.0.0.1', '167772161', '00001010000000000000000000000001', 'a000001'),
			array(ip2long('255.255.255.255'), '255.255.255.255', '4294967295', '11111111111111111111111111111111', 'ffffffff'),
			array('1', '0.0.0.1', '1', '00000000000000000000000000000001', '00000001'),
			array('4294967295', '255.255.255.255', '4294967295', '11111111111111111111111111111111', 'ffffffff'),
			array(inet_pton('10.0.0.1'), '10.0.0.1', '167772161', '00001010000000000000000000000001', 'a000001'),
			array(inet_pton('255.255.255.255'), '255.255.255.255', '4294967295', '11111111111111111111111111111111', 'ffffffff'),
		);

		// 32 bits
		if ( PHP_INT_SIZE == 4 ) {
			$values = array_merge($values,array(
				array(-1, '255.255.255.255', '4294967295', '11111111111111111111111111111111', 'ffffffff'),
			));
		}
		// 64 bits
		elseif ( PHP_INT_SIZE == 8 ) {

		}

		return $values;
	}

	public function invalidAddresses()
	{
		$values = array(
			array("\t"),
			array("abc"),
			array(12.3),
			array(-12.3),
			array('-1'),
			array('4294967296'),
			array('2a01:8200::'),
			array('::1'),
			array((double) -1)
		);

		// 32 bits
		if ( PHP_INT_SIZE == 4 ) {
			$values = array_merge($values,array(
				// 32bits
				array(-4294967295)
			));
		}
		// 64 bits
		elseif ( PHP_INT_SIZE == 8 ) {
			$values = array_merge($values, array(
				array(-1)
			));
		}

		return $values;
	}

	/**
	 * @dataProvider validAddresses
	 */
	public function testConstructValid($ip, $string)
	{
		$instance = new IPv4($ip);
		$this->assertEquals($string, (string) $instance);
	}

	/**
	 * @dataProvider invalidAddresses
	 * @expectedException InvalidArgumentException
	 */
	public function testConstructInvalid($ip)
	{
		$instance = new IPv4($ip);
	}

	/**
	 * @dataProvider validAddresses
	 */
	public function testConvertToNumeric($ip, $string, $dec, $bin, $hex)
	{
		$instance = new IPv4($ip);
		$this->assertEquals($dec, $instance->numeric(), "Base 10 conversion of $string");
		$this->assertEquals($bin, $instance->numeric(2), "Base 2 (bin) conversion of $string");
		$this->assertEquals($hex, $instance->numeric(16), "Base 16 (hex) conversion of $string");
	}

	public function privateAddresses()
	{
		return array(
			array('127.0.0.1'),
			array('192.168.0.1')
		);
	}

	/**
	 * @dataProvider privateAddresses
	 */
	public function testIsPrivate($ip)
	{
		$ip = new IPv4($ip);
		$this->assertTrue($ip->isPrivate(), "$ip is private");
		$this->assertFalse($ip->isPublic(), "$ip is not public");
	}
}