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

if (!function_exists('gmp_shiftl')) {
    /**
     * Shift left (<<).
     *
     * @see http://www.php.net/manual/en/ref.gmp.php#99788
     *
     * @param resource|string|\GMP $x
     * @param int                  $n
     *
     * @return resource|\GMP
     */
    function gmp_shiftl($x, $n)
    {
        return gmp_mul($x, gmp_pow('2', $n));
    }
}
if (!function_exists('gmp_shiftr')) {
    /**
     * Shift right (>>).
     *
     * @see http://www.php.net/manual/en/ref.gmp.php#99788
     *
     * @param resource|string|\GMP $x
     * @param int                  $n
     *
     * @return resource|\GMP
     */
    function gmp_shiftr($x, $n)
    {
        return gmp_div($x, gmp_pow('2', $n));
    }
}

/**
 * Base class to manipulate an IP address.
 */
abstract class IP
{
    const IP_VERSION = null;
    const MAX_INT = null;
    const NB_BITS = null;
    const NB_BYTES = null;

    /**
     * Internal representation of the IP as a numeric format.
     * For IPv4, this will be an SIGNED int (32 bits).
     * For IPv6, this will be a GMP resource (128 bits big int).
     *
     * @var mixed
     */
    protected $ip;

    /**
     * @var bool
     */
    private $is_private;

    /**
     * @var array
     */
    protected static $private_ranges;

    /**
     * Constructor tries to guess what is the $ip.
     *
     * @param $ip mixed String, binary string, int or float
     */
    public function __construct($ip)
    {
        if (is_int($ip)) {
            $this->fromInt($ip);

            return;
        }

        // float (or double) with an integer value
        if (is_float($ip) && $ip == floor($ip)) {
            $this->fromFloat($ip);

            return;
        }

        if (is_string($ip)) {
            $this->fromString($ip);

            return;
        }

        if ((is_resource($ip) && get_resource_type($ip) === 'GMP integer') || $ip instanceof \GMP) {
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

        if (!self::isValid($ip)) {
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

        if (!self::isValid($ip)) {
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
        if (@inet_ntop($ip) !== false) {
            if (strlen($ip) != static::NB_BYTES) {
                throw new \InvalidArgumentException(sprintf('The binary string "%s" is not a valid IPv%d address.', $ip, static::IP_VERSION));
            }

            $hex = unpack('H*', $ip);
            $this->ip = gmp_init($hex[1], 16);

            return;
        }

        $filterFlag = constant('FILTER_FLAG_IPV'.static::IP_VERSION);
        if (filter_var($ip, FILTER_VALIDATE_IP, $filterFlag)) {
            $ip = inet_pton($ip);
            $hex = unpack('H*', $ip);
            $this->ip = gmp_init($hex[1], 16);

            return;
        }

        // numeric string (decimal)
        if (ctype_digit($ip)) {
            $ip = gmp_init($ip, 10);
            if (!self::isValid($ip)) {
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
        if (!self::isValid($ip)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid decimal IPv%d address.', gmp_strval($ip), static::IP_VERSION));
        }
        $this->ip = $ip;
    }

    /**
     * Take an IP string/int and return an object of the correct type.
     *
     * Either IPv4 or IPv6 may be supplied, but integers less than 2^32 will
     * be considered to be IPv4 by default.
     *
     * @param  $ip      mixed Anything that can be converted into an IP (string, int, bin, etc.)
     *
     * @return IPv4|IPv6
     */
    public static function create($ip)
    {
        try {
            return new IPv4($ip);
        } catch (\InvalidArgumentException $e) {
            // do nothing
        }

        try {
            return new IPv6($ip);
        } catch (\InvalidArgumentException $e) {
            // do nothing
        }

        throw new \InvalidArgumentException("$ip does not appear to be an IPv4 or IPv6 address");
    }

    /**
     * Return human readable representation of the IP (e.g. 127.0.0.1 or ::1).
     *
     * @return string
     */
    abstract public function humanReadable();

    /**
     * Return numeric representation of the IP in base $base.
     *
     * The return value is a PHP string. It can base used for comparison.
     *
     * @param  $base  int from 2 to 36
     *
     * @return string
     */
    public function numeric($base = 10)
    {
        if ($base < 2 || $base > 36) {
            throw new \InvalidArgumentException('Base must be between 2 and 36 (included)');
        }

        $value = gmp_strval($this->ip, $base);

        // fix for newer versions of GMP (> 5.0) in PHP 5.4+ that removes
        // the leading 0 in base 2
        if ($base == 2) {
            $value = str_pad($value, static::NB_BITS, '0', STR_PAD_LEFT);
        }

        return $value;
    }

    /**
     * Return binary string representation.
     *
     * @return string Binary string
     */
    public function binary()
    {
        $hex = str_pad($this->numeric(16), static::NB_BITS/4, '0', STR_PAD_LEFT);

        return pack('H*', $hex);
    }

    /**
     * Bitwise AND.
     *
     * @param $value mixed anything that can be converted into an IP object
     *
     * @return IP
     */
    public function bit_and($value)
    {
        if (!$value instanceof self) {
            $value = new static($value);
        }

        return new static(gmp_and($this->ip, $value->ip));
    }

    /**
     * Bitwise OR.
     *
     * @param $value mixed anything that can be converted into an IP object
     *
     * @return IP
     */
    public function bit_or($value)
    {
        if (!$value instanceof self) {
            $value = new static($value);
        }

        return new static(gmp_or($this->ip, $value->ip));
    }

    /**
     * Plus (+).
     *
     * @throws \OutOfBoundsException
     *
     * @param $value mixed anything that can be converted into an IP object
     *
     * @return IP
     */
    public function plus($value)
    {
        if ($value < 0) {
            return $this->minus(-1 * $value);
        }

        if ($value == 0) {
            return clone $this;
        }

        if (!$value instanceof self) {
            $value = new static($value);
        }

        $result = gmp_add($this->ip, $value->ip);

        if (!self::isValid($result)) {
            throw new \OutOfBoundsException();
        }

        return new static($result);
    }

    /**
     * Minus(-).
     *
     * @throws \OutOfBoundsException
     *
     * @param $value mixed anything that can be converted into an IP object
     *
     * @return IP
     */
    public function minus($value)
    {
        if ($value < 0) {
            return $this->plus(-1 * $value);
        }

        if ($value == 0) {
            return clone $this;
        }

        if (!$value instanceof self) {
            $value = new static($value);
        }

        $result = gmp_sub($this->ip, $value->ip);

        if (!self::isValid($result)) {
            throw new \OutOfBoundsException();
        }

        return new static($result);
    }

    /**
     * @see humanReadable()
     */
    public function __toString()
    {
        return $this->humanReadable();
    }

    /**
     * Return the IP version number (4 or 6).
     *
     * @return int
     */
    public function getVersion()
    {
        return static::IP_VERSION;
    }

    /**
     * Check if the IP is contained in given block.
     *
     * @param $block mixed Anything that can be converted into an IPBlock
     *
     * @return bool
     */
    public function isIn($block)
    {
        if (!$block instanceof IPBlock) {
            $block = IPBlock::create($block);
        }

        return $block->contains($this);
    }

    /**
     * Return true if the address is reserved per IANA IPv4/6 Special Registry.
     *
     * @return bool
     */
    public function isPrivate()
    {
        if ($this->is_private !== null) {
            return $this->is_private;
        }

        $this->is_private = false;
        foreach (static::$private_ranges as $range) {
            if ($this->isIn($range)) {
                $this->is_private = true;
                break;
            }
        }

        return $this->is_private;
    }

    /**
     * Return true if the address is allocated for public networks.
     *
     * @return bool
     */
    public function isPublic()
    {
        return !$this->isPrivate();
    }

    /**
     * Ensures that a given $ip is within the range of a valid IPvX address.
     *
     * @param \GMP|resource $ip a GMP object or resource
     *
     * @return bool
     */
    private static function isValid($ip)
    {
        return (gmp_cmp($ip, 0) >= 0) && (gmp_cmp($ip, static::MAX_INT) <= 0);
    }
}
