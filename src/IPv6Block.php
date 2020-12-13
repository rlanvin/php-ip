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
 * An IPv6 CIDR block.
 */
class IPv6Block extends IPBlock
{
    use IPBlockTrait;

    protected static $ip_class = IPv6::class;

    /**
     * @see https://en.wikipedia.org/wiki/Reserved_IP_addresses
     */
    const PRIVATE_BLOCKS = [
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
    const LOOPBACK_BLOCK = '::1/128';
    const LINK_LOCAL_BLOCK = 'fe80::/10';
}
