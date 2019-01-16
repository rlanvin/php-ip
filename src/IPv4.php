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

    /**
     * Workaround for lack of late static binding in PHP 5.2
     * so I can use "new $this->class()"" instead of "new static()".
     */
    protected $class = __CLASS__;

    public function getVersion()
    {
        return self::IP_VERSION;
    }



    /**
     * Returns human readable representation of the IP.
     *
     * @param $compress bool Whether to compress IPv4 or not
     *
     * @return string
     */
    public function humanReadable($compress = true)
    {
        if ($compress) {
            $ip = long2ip(intval(doubleval($this->numeric())));
        } else {
            $hex = $this->numeric(16);
            $hex = str_pad($hex, 8, '0', STR_PAD_LEFT);
            $segments = str_split($hex, 2);
            foreach ($segments as &$s) {
                $s = str_pad(base_convert($s, 16, 10), 3, '0', STR_PAD_LEFT);
            }
            $ip = implode('.', $segments);
        }

        return $ip;
    }

    /**
     * Return true if the address is reserved per IANA IPv4 Special Registry.
     */
    public function isPrivate()
    {
        if (null === $this->is_private) {
            $this->is_private =
                $this->isIn('0.0.0.0/8') ||
                $this->isIn('10.0.0.0/8') ||
                $this->isIn('127.0.0.0/8') ||
                $this->isIn('169.254.0.0/16') ||
                $this->isIn('172.16.0.0/12') ||
                $this->isIn('192.0.0.0/29') ||
                $this->isIn('192.0.0.170/31') ||
                $this->isIn('192.0.2.0/24') ||
                $this->isIn('192.168.0.0/16') ||
                $this->isIn('198.18.0.0/15') ||
                $this->isIn('198.51.100.0/24') ||
                $this->isIn('203.0.113.0/24') ||
                $this->isIn('240.0.0.0/4') ||
                $this->isIn('255.255.255.255/32');
        }

        return $this->is_private;
    }
}
