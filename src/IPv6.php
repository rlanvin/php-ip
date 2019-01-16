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
     * @param $compress bool Whether to compress IPv6 or not
     *
     * @return string
     */
    public function humanReadable($compress = true)
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

    /**
     * Return true if the address is reserved per IANA IPv6 Special Registry.
     */
    public function isPrivate()
    {
        if (null === $this->is_private) {
            $this->is_private =
                $this->isIn('::1/128') ||
                $this->isIn('::/128') ||
                $this->isIn('::ffff:0:0/96') ||
                $this->isIn('100::/64') ||
                $this->isIn('2001::/23') ||
                $this->isIn('2001:2::/48') ||
                $this->isIn('2001:db8::/32') ||
                $this->isIn('2001:10::/28') ||
                $this->isIn('fc00::/7') ||
                $this->isIn('fe80::/10');
        }

        return $this->is_private;
    }
}
