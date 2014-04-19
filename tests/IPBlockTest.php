<?php

class IPBlockTest extends PHPUnit_Framework_TestCase
{
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
}