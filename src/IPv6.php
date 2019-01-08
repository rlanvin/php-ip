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
 * Class to manipulate IPv6.
 *
 * Addresses are stored internally as GMP resource (big int).
 */
class IPv6 extends IP
{
    const IP_VERSION = 6;
    const MAX_INT = '340282366920938463463374607431768211455';
    const NB_BITS = 128;

    protected $privateRanges = [
        '::1/128',
        '::/128',
        '::ffff:0:0/96',
        '100::/64',
        '2001::/23',
        '2001:2::/48',
        '2001:db8::/32',
        '2001:10::/28',
        'fc00::/7',
        'fe80::/10',
    ];

    /**
     * Returns human readable representation of the IP.
     *
     * @param bool $compress Whether to compress IPv6 or not
     *
     * @return string
     */
    public function humanReadable($compress = true): string
    {
        $hex = $this->numeric(16);
        $hex = str_pad($hex, 32, '0', STR_PAD_LEFT);
        $bytes = str_split($hex, 4);
        $ip = implode(':', $bytes);

        if ($compress) {
            $ip = @inet_ntop(@inet_pton($ip));
        }

        return $ip;
    }

    public function getPrivateRanges(): array
    {
        return $this->privateRanges;
    }

    /**
     * @param int $ip
     */
    protected function fromInt(int $ip): void
    {
        $this->ip = gmp_init(sprintf('%u', $ip), 10);
    }

    /**
     * @param float $ip
     *
     * @throws \InvalidArgumentException
     */
    protected function fromFloat(float $ip): void
    {
        if (floor($ip) != $ip) {
            throw new \InvalidArgumentException();
        }

        $ip = gmp_init(sprintf('%s', $ip), 10);

        if (gmp_cmp($ip, 0) < 0 || gmp_cmp($ip, self::MAX_INT) > 0) {
            throw new \InvalidArgumentException(sprintf('The double %s is not a valid IPv6 address', gmp_strval($ip)));
        }

        $this->ip = $ip;
    }

    protected function fromString(string $ip): void
    {
        // binary string
        if (!ctype_print($ip)) {
            // probably the result of inet_pton
            // must be 16 bytes exactly to be valid
            if (16 != strlen($ip)) {
                throw new \InvalidArgumentException('The binary string is not a valid IPv6 address');
            }
            $hex = unpack('H*', $ip);
            $this->ip = gmp_init($hex[1], 16);

            return;
        }

        // valid human readable representation
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $ip = inet_pton($ip);
            $hex = unpack('H*', $ip);
            $this->ip = gmp_init($hex[1], 16);

            return;
        }

        // numeric string (decimal)
        if (ctype_digit($ip)) {
            $ip = gmp_init($ip, 10);
            if (gmp_cmp($ip, self::MAX_INT) > 0) {
                throw new \InvalidArgumentException(sprintf('%s is not a valid decimal IPv6 address', gmp_strval($ip)));
            }
            $this->ip = $ip;

            return;
        }

        throw new \InvalidArgumentException("$ip is not a valid IPv6 address");
    }

    protected function fromGmp(\GMP $ip): void
    {
        if (gmp_cmp($ip, 0) < 0 || gmp_cmp($ip, self::MAX_INT) > 0) {
            throw new \InvalidArgumentException(sprintf('%s is not a valid decimal IPv6 address', gmp_strval($ip)));
        }

        $this->ip = $ip;
    }
}
