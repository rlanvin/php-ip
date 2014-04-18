<?php

class IPv6BlockTest extends PHPUnit_Framework_TestCase
{
	// see http://www.miniwebtool.com/ip-address-to-binary-converter/
	// and http://www.miniwebtool.com/ip-address-to-hex-converter
	public function validBlocks()
	{
		return array(
			//     CIDR            Mask               Delta              First IP          Last IP
			// array('0.0.0.0/0',    '0.0.0.0',         '255.255.255.255', '0.0.0.0',        '255.255.255.255'),
			array('2001:0db8::/30',                    'ffff:fffc::',                   '0:3:ffff:ffff:ffff:ffff:ffff:ffff',  '2001:db8::',          '2001:dbb:ffff:ffff:ffff:ffff:ffff:ffff'),
			array('2001:0db8::/31',                    'ffff:fffe::',                   '0:1:ffff:ffff:ffff:ffff:ffff:ffff',  '2001:db8::',          '2001:db9:ffff:ffff:ffff:ffff:ffff:ffff'),
			array('2001:0db8::/32',                    'ffff:ffff::',                   '::ffff:ffff:ffff:ffff:ffff:ffff',  '2001:db8::',            '2001:db8:ffff:ffff:ffff:ffff:ffff:ffff'),
			array('2001:0db8:85a3::8a2e:0370:7334/64', 'ffff:ffff:ffff:ffff::',         '::ffff:ffff:ffff:ffff',            '2001:db8:85a3::',       '2001:db8:85a3:0:ffff:ffff:ffff:ffff'),
		);
	}

	public function invalidBlocks()
	{
		return array(
			array('127.0.2666.1/24'),
			array('127.0.0.1/45'),
			array("\t"),
			array("abc"),
			array(12.3),
			array(-12.3),
			array('-1'),
			array('4294967296'),
			array('2a01:8200::'),
			array('2a01:8200::/'),
			array('::1')
		);
	}

	/**
	 * @dataProvider validBlocks
	 */
	public function testConstructValid($block, $mask, $delta, $first_ip, $last_ip)
	{
		$instance = new IPv6Block($block);
		$this->assertEquals($mask, (string) $instance->getMask(), "Mask of $block");
		$this->assertEquals($delta, (string) $instance->getDelta(), "Delta of $block");
		$this->assertEquals($first_ip, (string) $instance->getFirstIp(), "First IP of $block");
		$this->assertEquals($last_ip, (string) $instance->getLastIp(), "Last IP of $block");
	}

	/**
	 * @dataProvider invalidBlocks
	 * @expectedException InvalidArgumentException
	 */
	public function testConstructInvalid($block)
	{
		$instance = new IPv6Block($block);
	}

	public function blockContent()
	{
		return array(
			array('2001:0db8::/32', array('2001:db8::','2001:0db8:85a3::8a2e:0370:7334','2001:db8:ffff:ffff:ffff:ffff:ffff:ffff'), array('::1'))
		);
	}

	/**
	 * @dataProvider blockContent
	 */
	public function testContains($block, $in, $not_in)
	{
		$block = new IPv6Block($block);
		foreach ( $in as $ip ) {
			$this->assertTrue($block->contains($ip), "$ip is in $block");
		}
		foreach ( $not_in as $ip ) {
			$this->assertFalse($block->contains($ip, "$ip is not in $block"));
		}
	}
}