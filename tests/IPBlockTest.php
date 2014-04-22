<?php

class IPBlockTest extends PHPUnit_Framework_TestCase
{
	public function validOperations()
	{
		return array(
			// block                plus  minus  result
			array('192.168.0.0/24', 5,    null, '192.168.5.0/24'),
			array('192.168.0.0/24', 256,  null, '192.169.0.0/24'),
			array('0.0.0.0/1',      1,    null, '128.0.0.0/1'),
		);
	}

	/**
	 * @dataProvider validOperations
	 */
	public function testPlusMinus($block, $plus, $minus, $result)
	{
		$block = IPBlock::create($block);
		if ( $plus !== null ) {
			$this->assertEquals($result, (string) $block->plus($plus), "$block + $plus = $result");
			$this->assertEquals((string) $block, (string) IPBlock::create($result)->minus($plus), "$result - $plus = $block");
		}
		elseif ( $minus !== null ) {
			$this->assertEquals($result, (string) $block->minus($minus), "$block - $minus = $result");
			$this->assertEquals((string) $block, (string) IPBlock::create($result)->plus($minus), "$result + $minus = $block");

		}
	}

	public function invalidOperations()
	{
		return array(
			// IP   plus   minus
			array('255.255.255.255/32', 1, null),
			array('255.255.255.254/32', 2, null),
			// array('255.255.255.255/32', null, -1),
			// array('255.255.255.254/32', null, -2),
			// array('255.255.255.255', '255.255.255.255', null),
			// array('255.255.255.255', IPv4::MAX_INT, null),
			array('0.0.0.0/0', 1, null),
			array('0.0.0.0/0', -1, null),
			array('0.0.0.0/1', 2, null),
			array('0.0.0.0/32', -1, null),
			array('0.0.0.1/32', -2, null),
			// array('0.0.0.0', null, 1),
			// array('0.0.0.1', null, 2)
		);
	}

	/**
	 * @dataProvider invalidOperations
	 * @expectedException OutOfBoundsException
	 */
	public function testPlusMinusOob($block, $plus, $minus)
	{
		$block = IPBlock::create($block);
		if ( $plus !== null ) {
			$block->plus($plus);
		}
		elseif ( $minus !== null ) {
			$block->minus($minus);
		}
	}

	public function blockContent()
	{
		return array(
			array(
				'192.168.0.0/24',
				array('192.168.0.0','192.168.0.42','192.168.0.255'),
				array('192.168.0.128/25'),
				array('10.0.0.1','192.167.255.255','192.169.0.0'),
				array('10.0.0.1/24'),
			),
			array(
				'2001:0db8::/32',
				array('2001:db8::','2001:0db8:85a3::8a2e:0370:7334','2001:db8:ffff:ffff:ffff:ffff:ffff:ffff'),
				array('2001:db8::/64'),
				array('::1'),
				array('::1/128')
			)
		);
	}

	/**
	 * @dataProvider blockContent
	 */
	public function testContains($block, $ip_in, $block_in, $ip_not_in, $block_not_in)
	{
		$block = IPBlock::create($block);
		foreach ( $ip_in as $ip ) {
			$this->assertTrue($block->contains($ip), "$block contains $ip");
			$this->assertTrue(IP::create($ip)->isIn($block), "$ip is in $block");
		}
		foreach ( $block_in as $ip ) {
			$this->assertTrue($block->contains($ip), "$block contains $ip");
			$this->assertFalse(IPBlock::create($ip)->contains($block), "$ip does not contain $block");
		}
		foreach ( $ip_not_in as $ip ) {
			$this->assertFalse($block->contains($ip), "$block does not contain $ip");
			$this->assertFalse(IP::create($ip)->isIn($block), "$ip is not in $block");
		}
		foreach ( $block_not_in as $ip ) {
			$this->assertFalse($block->contains($ip), "$ip is not in $block");
		}
	}

	public function overlappingBlocks()
	{
		return array(
			array(
				'192.168.0.0/24',
				array('192.168.0.128/25', '192.168.0.0/23'),
				array('10.0.0.1/24'),
			),

		);
	}

	/**
	 * @dataProvider overlappingBlocks
	 */
	public function testOverlaps($block, $overlapping, $not_overlapping)
	{
		$block = IPBlock::create($block);
		foreach ( $overlapping as $block2 ) {
			$this->assertTrue($block->overlaps($block2), "$block is overlapping $block2");
			$this->assertTrue(IPBlock::create($block2)->overlaps($block), "$block2 is overlapping $block");
		}
		foreach ( $not_overlapping as $block2 ) {
			$this->assertFalse($block->overlaps($block2, "$block is not overlapping $block2"));
			$this->assertFalse(IPBlock::create($block2)->overlaps($block), "$block2 is not overlappping $block");
		}
	}

	public function testCountable()
	{
		$block = IPBlock::create('192.168.0.0/24');
		$this->assertEquals(256, sizeof($block));

		$block = IPBlock::create('::1/128');
		$this->assertEquals(1, sizeof($block));

		$block = IPBlock::create('0.0.0.0/8');
		$this->assertEquals(16777216, sizeof($block));

		try {
			$block = IPBlock::create('0.0.0.0/1');
			sizeof($block);
			$this->fail('Sizeof should fail if number of addresses is bigger than PHP_INT_MAX');
		} catch ( RuntimeException $e ) {
		}

		$block = IPBlock::create('0.0.0.0/0');
		$this->assertEquals('4294967296', $block->getNbAddresses());
	}

	public function testArrayAccess()
	{
		$block = IPBlock::create('192.168.0.0/24');
		$this->assertEquals('192.168.0.0',$block[0]);
		$this->assertEquals('192.168.0.15',$block[15]);
		$this->assertEquals('192.168.0.255',$block[255]);
		try {
			$block[256];
			$this->fail('[] shoud throw OutOfBoundException');
		} catch ( OutOfBoundsException $e ) {
		}

		try {
			$block[2] = 'X';
			$this->fail('Setting with [] shoud throw LogicException');
		} catch ( LogicException $e ) {
		}
	}

	public function testGetSubblocks()
	{
		// todo
	}

	public function testGetSuper()
	{
		$block = IPBlock::create('192.168.42.0/24');
		$this->assertEquals('192.168.0.0/16', (string) $block->getSuper('/16'));

		try {
			$block->getSuper('');
			$this->fail('Expected InvalidArgumentException has not be thrown');
		} catch ( InvalidArgumentException $e ) {
		}

		try {
			$block->getSuper('/32');
			$this->fail('Expected InvalidArgumentException has not be thrown');
		} catch ( InvalidArgumentException $e ) {
		}
	}
}