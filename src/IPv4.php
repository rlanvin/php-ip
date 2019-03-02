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

    protected static $private_ranges = array(
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
    );

    protected static $loopback_range = '127.0.0.0/8';

    /**
     * Workaround for lack of late static binding in PHP 5.2
     * so I can use "new $this->class()"" instead of "new static()".
     */
    protected $class = __CLASS__;

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
}
