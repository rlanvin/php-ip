<?php

class IPv6Test extends PHPUnit_Framework_TestCase
{
	public function validAddresses()
	{
		$values = array(
			// IP  compressed  decimal
			array('2a01:8200::', '2a01:8200::', '55835404833073476206743540170770874368',),
			array('2001:0db8:85a3:0000:0000:8a2e:0370:7334', '2001:db8:85a3::8a2e:370:7334', '42540766452641154071740215577757643572'),
			array('ffff:0db8::', 'ffff:db8::', '340277452873386678732099705461792571392'),
			array('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff','ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', '340282366920938463463374607431768211455'),
			array('::1', '::1', '1', '1', '1'),

			// IPv4-mapped IPv6 addresses
			array('0000:0000:0000:0000:0000:0000:127.127.127.127','::127.127.127.127', '2139062143'),
			array('::ffff:192.0.2.128','::ffff:192.0.2.128', '281473902969472'),

			// init with numeric representation
			array('332314827956335977770735408709082546176', 'fa01:8200::', '332314827956335977770735408709082546176'),

			// init with GMP ressource
			array(gmp_init('332314827956335977770735408709082546176'), 'fa01:8200::', '332314827956335977770735408709082546176'),
		);

		// 32 bits
		if ( PHP_INT_SIZE == 4 ) {
			$values = array_merge($values,array(
				array(-1, '::255.255.255.255', '4294967295')
			));
		}
		// 64 bits
		elseif ( PHP_INT_SIZE == 8 ) {
			$values = array_merge($values, array(
				array(-1, '::ffff:ffff:ffff:ffff', '18446744073709551615')
			));
		}

		return $values;
	}

	public function invalidAddresses()
	{
		$values = array(
			array("\t"),
			array(array()),
			array(new stdClass()),
			array("-1"),
			array(gmp_init('-1')),
			array(gmp_init('340282366920938463463374607431768211456')),
			array("abcz"),
			array(12.3),
			array(-12.3),
			array('127.0.0.1'),
		);

		// 32 bits
		if ( PHP_INT_SIZE == 4 ) {

		}
		// 64 bits
		elseif ( PHP_INT_SIZE == 8 ) {

		}

		return $values;
	}

	/**
	 * @dataProvider validAddresses
	 */
	public function testConstructValid($ip, $compressed)
	{
		$instance = new IPv6($ip);
		$this->assertEquals($compressed, (string) $instance);
	}

	/**
	 * @dataProvider invalidAddresses
	 * @expectedException InvalidArgumentException
	 */
	public function testConstructInvalid($ip)
	{
		$instance = new IPv6($ip);
	}

	/**
	 * @dataProvider validAddresses
	 */
	public function testConvertToNumeric($ip, $compressed, $dec)
	{
		$instance = new IPv6($ip);
		$array = unpack('H*',inet_pton($compressed));
		$this->assertEquals(ltrim($array[1],0), $instance->numeric(16), "Base 16 of $compressed");
		$this->assertEquals($dec, $instance->numeric(10), "Base 10 of $compressed");
	}
}