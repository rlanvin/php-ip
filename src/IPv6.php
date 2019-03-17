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
 * Class to manipulate IPv6.
 *
 * Addresses are stored internally as GMP resource (big int).
 *
 * @see https://tools.ietf.org/html/rfc4291 IP Version 6 Addressing Architecture
 */
class IPv6 extends IP
{
    const IP_VERSION = 6;
    const MAX_INT = '340282366920938463463374607431768211455';
    const NB_BITS = 128;
    const NB_BYTES = 16;

    protected static $private_ranges = [
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

    protected static $loopback_range = '::1/128';

    /**
     * @var string
     */
    protected static $link_local_block = 'fe80::/10';

    /**
     * {@inheritdoc}
     */
    public function humanReadable(bool $short_form = true): string
    {
        if ($short_form) {
            return inet_ntop($this->binary());
        }

        $hex = str_pad($this->numeric(16), 32, '0', STR_PAD_LEFT);

        return implode(':', str_split($hex, 4));
    }

    /**
     * {@inheritdoc}
     */
    public function reversePointer(): string
    {
        $ip = str_replace(':', '', $this->humanReadable(false));
        $ip = strrev($ip);
        $ip = implode('.', str_split($ip));
        $ip .= '.ip6.arpa.';

        return $ip;
    }
}
