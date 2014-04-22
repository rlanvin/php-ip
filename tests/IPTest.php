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

	/**
	 * @dataProvider validAddresses
	 */
	public function testBinary($ip, $string)
	{
		$instance = IP::create($ip);
		$this->assertEquals(inet_pton($string), $instance->binary());
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


	public function validOperations()
	{
		return array(
			//     IP                plus              minus             result
			array('255.255.255.255', null,             1,                '255.255.255.254'),
			array('255.255.255.255', -1,               null,             '255.255.255.254'),
			array('0.0.0.0',        '255.255.255.255', null,             '255.255.255.255'),
			array('255.255.255.255', null,            '255.255.255.255', '0.0.0.0'),
			array('0.0.0.0',         1,                null,             '0.0.0.1'),
			array('0.0.0.0',         null,              -1,              '0.0.0.1'),
			array('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', null, 1, 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:fffe'),
			array('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', -1, null, 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:fffe'),
			array('::', 1, null, '::1'),
			array('::', null, -1, '::1')
		);
	}

	/**
	 * @dataProvider validOperations
	 */
	public function testPlusMinus($ip, $plus, $minus, $result)
	{
		$ip = IP::create($ip);
		if ( $plus !== null ) {
			$this->assertEquals($result, (string) $ip->plus($plus), "$ip + $plus = $result");
			$this->assertEquals((string) $ip, (string) IP::create($result)->minus($plus), "$result - $plus = $ip");
		}
		elseif ( $minus !== null ) {
			$this->assertEquals($result, (string) $ip->minus($minus), "$ip - $minus = $result");
			$this->assertEquals((string) $ip, (string) IP::create($result)->plus($minus), "$result + $minus = $ip");
		}
	}

	public function invalidOperations()
	{
		return array(
			// IP   plus   minus
			array('255.255.255.255', 1, null),
			array('255.255.255.254', 2, null),
			array('255.255.255.255', null, -1),
			array('255.255.255.254', null, -2),
			array('255.255.255.255', '255.255.255.255', null),
			array('255.255.255.255', IPv4::MAX_INT, null),
			array('0.0.0.0', -1, null),
			array('0.0.0.1', -2, null),
			array('0.0.0.0', null, 1),
			array('0.0.0.1', null, 2),
			// IP   plus   minus
			array('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 1, null),
			array('ffff:ffff:ffff:ffff:ffff:ffff:ffff:fffe', 2, null),
			array('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', null, -1),
			array('ffff:ffff:ffff:ffff:ffff:ffff:ffff:fffe', null, -2),
			array('::', -1, null),
			array('::1', -2, null),
			array('::', null, 1),
			array('::1', null, 2)
		);
	}

	/**
	 * @dataProvider invalidOperations
	 * @expectedException OutOfBoundsException
	 */
	public function testPlusMinusOob($ip, $plus, $minus)
	{
		$ip = IP::create($ip);
		if ( $plus !== null ) {
			$ip->plus($plus);
		}
		elseif ( $minus !== null ) {
			$ip->minus($minus);
		}
	}

}