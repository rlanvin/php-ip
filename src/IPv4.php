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
     * Constructor tries to guess what is the $ip.
     *
     * @param $ip mixed String, binary string, int or float
     */
    public function __construct($ip)
    {
        // if an integer is provided, we have to be careful of the architecture
        // on 32-bit platform, it is always a valid IP
        // on 64-bit platform, we have to test the value
        if (is_int($ip)) {
            $this->fromInt($ip);

            return;
        }

        // float (or double) with an integer value
        if (is_float($ip) && floor($ip) == $ip) {
            $this->fromFloat($ip);

            return;
        }

        if (is_string($ip)) {
            $this->fromString($ip);

            return;
        }

        if ((is_resource($ip) && 'GMP integer' === get_resource_type($ip)) || $ip instanceof \GMP) {
            $this->fromGMP($ip);

            return;
        }

        throw new \InvalidArgumentException(sprintf('Unsupported argument type: "%s".', gettype($ip)));
    }

    /**
     * @param int $ip
     */
    private function fromInt($ip)
    {
        $ip = gmp_init(sprintf('%u', $ip), 10);

        if (gmp_cmp($ip, static::MAX_INT) > 0) {
            throw new \InvalidArgumentException(sprintf('The integer "%s" is not a valid IPv%d address.', gmp_strval($ip), static::IP_VERSION));
        }

        $this->ip = $ip;
    }

    /**
     * @param float $ip
     */
    private function fromFloat($ip)
    {
        $ip = gmp_init(sprintf('%s', $ip), 10);

        if (gmp_cmp($ip, 0) < 0 || gmp_cmp($ip, static::MAX_INT) > 0) {
            throw new \InvalidArgumentException(sprintf('The double "%s" is not a valid IPv%d address.', gmp_strval($ip), static::IP_VERSION));
        }

        $this->ip = $ip;
    }

    /**
     * @param string $ip
     */
    private function fromString($ip)
    {
        // binary, packed string
        if (false !== @inet_ntop($ip)) {
            $strLen = static::NB_BITS/8;

            if ($strLen != strlen($ip)) {
                throw new \InvalidArgumentException(sprintf('The binary string "%s" is not a valid IPv%d address.', $ip, static::IP_VERSION));
            }

            $hex = unpack('H*', $ip);
            $this->ip = gmp_init($hex[1], 16);

            return;
        }

        $filterFlag = constant('FILTER_FLAG_IPV' . static::IP_VERSION);
        if (filter_var($ip, FILTER_VALIDATE_IP, $filterFlag)) {
            $ip = inet_pton($ip);
            $hex = unpack('H*', $ip);
            $this->ip = gmp_init($hex[1], 16);

            return;
        }

        // numeric string (decimal)
        if (ctype_digit($ip)) {
            $ip = gmp_init($ip, 10);
            if (gmp_cmp($ip, static::MAX_INT) > 0) {
                throw new \InvalidArgumentException(sprintf('"%s" is not a valid decimal IPv%d address.', gmp_strval($ip), static::IP_VERSION));
            }

            $this->ip = $ip;

            return;
        }

        throw new \InvalidArgumentException(sprintf('The string "%s" is not a valid IPv%d address.', $ip, static::IP_VERSION));
    }

    /**
     * @param \GMP|resource $ip
     */
    private function fromGMP($ip)
    {
        if (gmp_cmp($ip, 0) < 0 || gmp_cmp($ip, static::MAX_INT) > 0) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid decimal IPv%d address.', gmp_strval($ip), static::IP_VERSION));
        }
        $this->ip = $ip;
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
