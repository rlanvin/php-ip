<?php

/**
 * Licensed under the MIT license.
 *
 * For the full copyright and license information, please view the LICENSE file.
 *
 * @author Rémi Lanvin <remi@cloudconnected.fr>
 *
 * @see https://github.com/rlanvin/php-ip
 */

namespace phpIP;

/**
 * Class to manipulate IPv4.
 */
class IPv4 extends IP
{
    const IP_VERSION = 4;
    const MAX_INT = '4294967295';
    const NB_BITS = 32;

    protected static $privateRanges = [
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

    /**
     * Returns human readable representation of the IP.
     *
     * @param bool $compress Whether to compress IPv4 or not
     *
     * @return string
     */
    public function humanReadable($compress = true): string
    {
        if ($compress) {
            return long2ip(intval(doubleval($this->numeric())));
        }

        $hex = $this->numeric(16);
        $hex = str_pad($hex, 8, '0', STR_PAD_LEFT);

        $octets = array_map(function ($hex) {
            return str_pad(hexdec($hex), 3, '0', STR_PAD_LEFT);
        }, str_split($hex, 2));

        return implode('.', $octets);
    }
}
