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

namespace PHPIP;

/**
 * Class to manipulate IPv4.
 */
class IPv4 extends IP
{
    const IP_VERSION = 4;
    const MAX_INT = '4294967295';
    const NB_BITS = 32;

    protected $privateRanges = [
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
        '255.255.255.255/32'
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


    public function getPrivateRanges(): array
    {
        return $this->privateRanges;
    }

    protected function fromInt(int $ip): void
    {
        // if an integer is provided, we have to be careful of the architecture
        // on 32 bits plateform, it's always a valid IP
        // on 64 bits plateform, we have to test the value
        $ip = gmp_init(sprintf('%u', $ip), 10);
        if (gmp_cmp($ip, self::MAX_INT) > 0) {
            throw new \InvalidArgumentException(sprintf('The integer %s is not a valid IPv4 address', gmp_strval($ip)));
        }
        $this->ip = $ip;
    }

    protected function fromFloat(float $ip): void
    {
        if (floor($ip) != $ip) {
            throw new \InvalidArgumentException();
        }

        // float (or double) with an integer value
        $ip = gmp_init(sprintf('%s', $ip), 10);
        if (gmp_cmp($ip, 0) < 0 || gmp_cmp($ip, self::MAX_INT) > 0) {
            throw new \InvalidArgumentException(sprintf('The double %s is not a valid IPv4 address', gmp_strval($ip)));
        }
        $this->ip = $ip;
    }

    protected function fromString(string $ip): void
    {
        // binary string
        if (!ctype_print($ip)) {
            if (4 != strlen($ip)) {
                throw new \InvalidArgumentException('The binary string is not a valid IPv4 address');
            }
            $hex = unpack('H*', $ip);
            $this->ip = gmp_init($hex[1], 16);

            return;
        }

        // human readable IPv4
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $this->ip = gmp_init(sprintf('%u', ip2long($ip)));

            return;
        }

        // numeric string (decimal)
        if (ctype_digit($ip)) {
            $ip = gmp_init($ip);

            if (gmp_cmp($ip, self::MAX_INT) > 0) {
                throw new \InvalidArgumentException(sprintf('%s is not a valid decimal IPv4 address', gmp_strval($ip)));
            }

            $this->ip = $ip;

            return;
        }

        if (false !== @inet_ntop($ip)) {
            $this->fromString(inet_ntop($ip));

            return;
        }

        throw new \InvalidArgumentException("$ip is not a valid IPv4 address");
    }

    protected function fromGmp(\GMP $ip): void
    {
        if (gmp_cmp($ip, 0) < 0 || gmp_cmp($ip, self::MAX_INT) > 0) {
            throw new \InvalidArgumentException(sprintf('%s is not a valid decimal IPv4 address', gmp_strval($ip)));
        }
        $this->ip = $ip;
    }
}
