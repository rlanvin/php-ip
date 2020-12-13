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
    use IPTrait;

    const IP_VERSION = 4;
    const MAX_INT = '4294967295';
    const NB_BITS = 32;
    const NB_BYTES = 4;
    const BLOCK_CLASS = IPv4Block::class;

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
