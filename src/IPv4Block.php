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
    protected static $ip_class = IPv4::class;

    const netmask2prefix = array(
        "0.0.0.0" => 0,
        "128.0.0.0" => 1,
        "192.0.0.0" => 2,
        "224.0.0.0" => 3,
        "240.0.0.0" => 4,
        "248.0.0.0" => 5,
        "252.0.0.0" => 6,
        "254.0.0.0" => 7,
        "255.0.0.0" => 8,
        "255.128.0.0" => 9,
        "255.192.0.0" => 10,
        "255.224.0.0" => 11,
        "255.240.0.0" => 12,
        "255.248.0.0" => 13,
        "255.252.0.0" => 14,
        "255.254.0.0" => 15,
        "255.255.0.0" => 16,
        "255.255.128.0" => 17,
        "255.255.192.0" => 18,
        "255.255.224.0" => 19,
        "255.255.240.0" => 20,
        "255.255.248.0" => 21,
        "255.255.252.0" => 22,
        "255.255.254.0" => 23,
        "255.255.255.0" => 24,
        "255.255.255.128" => 25,
        "255.255.255.192" => 26,
        "255.255.255.224" => 27,
        "255.255.255.240" => 28,
        "255.255.255.248" => 29,
        "255.255.255.252" => 30,
        "255.255.255.254" => 31,
        "255.255.255.255" => 32
        );

    /**
     * @internal
     * Check if the prefix is valid,
     * rewrites IPv4 old-style netmask to CIDR prefix number,
     * and returns the prefix.
     *
     * @param mixed $prefix
     *
     * @throws \InvalidArgumentException
     * @return int
     */
    protected function checkPrefix($prefix)
    {
        if (isset(self::netmask2prefix[$prefix])) {
            return (int) self::netmask2prefix[$prefix];
        }

        return parent::checkPrefix($prefix);
    }
}
