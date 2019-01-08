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
 * Class to manipulate IPv6.
 *
 * Addresses are stored internally as GMP resource (big int).
 */
class IPv6 extends IP
{
    const IP_VERSION = 6;
    const MAX_INT = '340282366920938463463374607431768211455';
    const NB_BITS = 128;

    protected static $privateRanges = [
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
        $ip = implode(':', str_split($hex, 4));

        if ($compress) {
            $ip = @inet_ntop(@inet_pton($ip));
        }

        return $ip;
    }
}
