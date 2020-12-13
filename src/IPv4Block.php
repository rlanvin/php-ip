<?php

declare(strict_types=1);

/**
 * Licensed under the MIT license.
 *
 * For the full copyright and license information, please view the LICENSE file.
 *
 * @author Rémi Lanvin <remi@cloudconnected.fr>
 *
 * @see https://github.com/rlanvin/php-ip
 */

namespace PhpIP;

/**
 * An IPv4 CIDR block.
 */
class IPv4Block extends IPBlock
{
    use IPBlockTrait;

    protected static $ip_class = IPv4::class;

    /**
     * @see https://en.wikipedia.org/wiki/Reserved_IP_addresses
     */
    const PRIVATE_BLOCKS = [
        '0.0.0.0/8',
        '10.0.0.0/8',
        '127.0.0.0/8',
        '169.254.0.0/16',
        '172.16.0.0/12',
        '192.0.0.0/29',
        '192.0.0.170/31',
        '192.0.2.0/24',
        '192.168.0.0/16',
        '198.18.0.0/15',
        '198.51.100.0/24',
        '203.0.113.0/24',
        '240.0.0.0/4',
        '255.255.255.255/32',
    ];
    const LOOPBACK_BLOCK = '127.0.0.0/8';
    const LINK_LOCAL_BLOCK = '169.254.0.0/16';

    const NETMASK2PREFIX = [
        '0.0.0.0' => 0,
        '128.0.0.0' => 1,
        '192.0.0.0' => 2,
        '224.0.0.0' => 3,
        '240.0.0.0' => 4,
        '248.0.0.0' => 5,
        '252.0.0.0' => 6,
        '254.0.0.0' => 7,
        '255.0.0.0' => 8,
        '255.128.0.0' => 9,
        '255.192.0.0' => 10,
        '255.224.0.0' => 11,
        '255.240.0.0' => 12,
        '255.248.0.0' => 13,
        '255.252.0.0' => 14,
        '255.254.0.0' => 15,
        '255.255.0.0' => 16,
        '255.255.128.0' => 17,
        '255.255.192.0' => 18,
        '255.255.224.0' => 19,
        '255.255.240.0' => 20,
        '255.255.248.0' => 21,
        '255.255.252.0' => 22,
        '255.255.254.0' => 23,
        '255.255.255.0' => 24,
        '255.255.255.128' => 25,
        '255.255.255.192' => 26,
        '255.255.255.224' => 27,
        '255.255.255.240' => 28,
        '255.255.255.248' => 29,
        '255.255.255.252' => 30,
        '255.255.255.254' => 31,
        '255.255.255.255' => 32,
    ];

    /**
     * @internal
     * Check if the prefix length is valid,
     * rewrites IPv4 old-style netmask to CIDR prefix length,
     * and returns the prefix length as an int
     *
     * @param mixed $prefix
     *
     * @throws \InvalidArgumentException
     *
     * @return int
     */
    protected function checkPrefixLength($prefix)
    {
        if (is_string($prefix) && isset(self::NETMASK2PREFIX[$prefix])) {
            return (int) self::NETMASK2PREFIX[$prefix];
        }

        return parent::checkPrefixLength($prefix);
    }
}
