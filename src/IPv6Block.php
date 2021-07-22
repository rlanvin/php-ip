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

    const IP_CLASS = IPv6::class;

    /**
     * @see https://www.iana.org/assignments/iana-ipv6-special-registry/iana-ipv6-special-registry.xhtml
     *
     * PRIVATE_BLOCKS   = not globally reachable, but routeable
     * RESERVED_BLOCKS  = all IANA reserved blocks
     * LOOPBACK_BLOCK   = Loopback Address
     * LINK_LOCAL_BLOCK = Link-Local Unicast
     */
    const PRIVATE_BLOCKS = [
        'fc00::/7', // Unique-Local
        '2001:2::/48', // Benchmarking
        '100::/64', // Discard-Only Address Block
        '64:ff9b:1::/48' // IPv4-IPv6 Translat.
    ];
    const RESERVED_BLOCKS = [
        '::/128',
        '::1/128',
        '::ffff:0:0/96',
        '64:ff9b::/96',
        '64:ff9b:1::/48',
        '100::/64',
        '2001::/23',
        '2001::/32',
        '2001:1::1/128',
        '2001:1::2/128',
        '2001:2::/48',
        '2001:3::/32',
        '2001:4:112::/48',
        '2001:10::/28',
        '2001:20::/28',
        '2001:db8::/32',
        '2002::/16',
        '2620:4f:8000::/48',
        'fc00::/7',
        'fe80::/10',
    ];
    const LOOPBACK_BLOCK = '::1/128';
    const LINK_LOCAL_BLOCK = 'fe80::/10';
}
