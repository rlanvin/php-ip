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

namespace PhpIP;

/**
 * Class to manipulate IPv6.
 *
 * Addresses are stored internally as GMP ressource (big int).
 */
class IPv6 extends IP
{
    const IP_VERSION = 6;
    const MAX_INT = '340282366920938463463374607431768211455';
    const NB_BITS = 128;

    protected static $private_ranges = array(
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
    );

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
     * Constuctor tries to guess what is $ip.
     *
     * @param mixed $ip
     */
    public function __construct($ip)
    {
        if (is_int($ip)) {
            // always a valid IP, since even in 64bits plateform, it's less than max value
            $this->ip = gmp_init(sprintf('%u', $ip), 10);
        } elseif (is_float($ip) && $ip == floor($ip)) {
            // float (or double) with an integer value
            $ip = gmp_init(sprintf('%s', $ip), 10);
            if (gmp_cmp($ip, 0) < 0 || gmp_cmp($ip, self::MAX_INT) > 0) {
                throw new \InvalidArgumentException(sprintf('The double %s is not a valid IPv6 address', gmp_strval($ip)));
            }
            $this->ip = $ip;
        } elseif (is_string($ip)) {
            // binary string
            if (!ctype_print($ip)) {
                // probably the result of inet_pton
                // must be 16 bytes exactly to be valid
                if (strlen($ip) != 16) {
                    throw new \InvalidArgumentException('The binary string is not a valid IPv6 address');
                }
                $hex = unpack('H*', $ip);
                $this->ip = gmp_init($hex[1], 16);
            }
            // valid human readable representation
            elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                $ip = inet_pton($ip);
                $hex = unpack('H*', $ip);
                $this->ip = gmp_init($hex[1], 16);
            }
            // numeric string (decimal)
            elseif (ctype_digit($ip)) {
                $ip = gmp_init($ip, 10);
                if (gmp_cmp($ip, '340282366920938463463374607431768211455') > 0) {
                    throw new \InvalidArgumentException(sprintf('%s is not a valid decimal IPv6 address', gmp_strval($ip)));
                }
                $this->ip = $ip;
            } else {
                throw new \InvalidArgumentException("$ip is not a valid IPv6 address");
            }
        } elseif ((is_resource($ip) && get_resource_type($ip) == 'GMP integer') || $ip instanceof \GMP) {
            if (gmp_cmp($ip, 0) < 0 || gmp_cmp($ip, self::MAX_INT) > 0) {
                throw new \InvalidArgumentException(sprintf('%s is not a valid decimal IPv6 address', gmp_strval($ip)));
            }
            $this->ip = $ip;
        } else {
            throw new \InvalidArgumentException('Unsupported argument type: '.gettype($ip));
        }
    }

    /**
     * Returns human readable representation of the IP.
     *
     * @param $compress bool Wether to compress IPv6 or not
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
}
