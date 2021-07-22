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

    const IP_CLASS = IPv4::class;

    /**
     * @see https://www.iana.org/assignments/iana-ipv4-special-registry/iana-ipv4-special-registry.xhtml
     *
     * PRIVATE_BLOCKS   = not globally reachable, but routeable
     * RESERVED_BLOCKS  = all IANA reserved blocks
     * LOOPBACK_BLOCK   = Loopback Address
     * LINK_LOCAL_BLOCK = Link-Local Unicast
     */
    const PRIVATE_BLOCKS = [
        '10.0.0.0/8', // Private-Use
        '100.64.0.0/10', // Shared Address Space (for communications between a service provider and its subscribers when using a carrier-grade NAT)
        '172.16.0.0/12', // Private-Use
        '192.0.0.0/29', // IPv4 Service Continuity Prefix (Dual-Stack Lite)
        '192.168.0.0/16', // Private-Use
        '198.18.0.0/15' // Benchmarking (testing of inter-network communications between two separate subnets)
    ];
    const RESERVED_BLOCKS = [
        '0.0.0.0/8',
        '0.0.0.0/32',
        '10.0.0.0/8',
        '100.64.0.0/10',
        '127.0.0.0/8',
        '169.254.0.0/16',
        '172.16.0.0/12',
        '192.0.0.0/24',
        '192.0.0.0/29',
        '192.0.0.8/32',
        '192.0.0.9/32',
        '192.0.0.10/32',
        '192.0.0.170/32',
        '192.0.0.171/32',
        '192.0.2.0/24',
        '192.31.196.0/24',
        '192.52.193.0/24',
        '192.88.99.0/24',
        '192.168.0.0/16',
        '192.175.48.0/24',
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
