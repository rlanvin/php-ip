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
 * Class to manipulate IPv4.
 */
class IPv4 extends IP
{
    const IP_VERSION = 4;
    const MAX_INT = '4294967295';
    const NB_BITS = 32;
    const NB_BYTES = 4;

    protected static $private_ranges = [
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

    protected static $loopback_range = '127.0.0.0/8';

    /**
     * @see https://tools.ietf.org/html/rfc3927 Dynamic Configuration of IPv4 Link-Local Addresses
     *
     * @var string
     */
    protected static $link_local_block = '169.254.0.0/16';

    /**
     * {@inheritdoc}
     */
    public function humanReadable(bool $short_form = true): string
    {
        if ($short_form) {
            return inet_ntop($this->binary());
        }

        $octets = explode('.', inet_ntop($this->binary()));

        return sprintf('%03d.%03d.%03d.%03d', ...$octets);
    }

    /**
     * {@inheritdoc}
     */
    public function reversePointer(): string
    {
        $octets = array_reverse(explode('.', $this->humanReadable()));

        return implode('.', $octets).'.in-addr.arpa.';
    }
}
