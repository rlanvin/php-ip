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
     *
     * @var \GMP
     */
    protected $ip;

    /**
     * @var bool
     */
    protected $is_private;

    /**
     * @var string Either "IPv4" or "IPv6"
     */
    protected $class;

    /**
     * Constructor tries to guess what is the $ip.
     *
     * @param mixed $ip string, binary string, int, float or \GMP instance
     */
    public function __construct($ip)
    {
        if (is_int($ip)) {
            $this->ip = self::fromInt($ip);

            return;
        }

        // float (or double) with an integer value
        if (is_float($ip) && $ip == floor($ip)) {
            $this->ip = self::fromFloat($ip);

            return;
        }

        if (is_string($ip)) {
            $this->ip = self::fromString($ip);

            return;
        }

        if ((is_resource($ip) && get_resource_type($ip) === 'GMP integer') || $ip instanceof \GMP) {
            $this->ip = self::fromGMP($ip);

            return;
        }

        throw new \InvalidArgumentException(sprintf('Unsupported argument type: "%s".', gettype($ip)));
    }

    /**
     * @param $ip
     *
     * @return \GMP
     */
    private static function fromInt(int $ip): \GMP
    {
        $ip = gmp_init(sprintf('%u', $ip), 10);
        if (gmp_cmp($ip, static::MAX_INT) > 0) {
            throw new \InvalidArgumentException(sprintf('The integer "%s" is not a valid IPv%d address.', gmp_strval($ip), static::IP_VERSION));
        }

        return $ip;
    }

    /**
     * @param float $ip
     *
     * @return \GMP
     */
    private static function fromFloat(float $ip): \GMP
    {
        $ip = gmp_init(sprintf('%s', $ip), 10);
        if (gmp_cmp($ip, 0) < 0 || gmp_cmp($ip, static::MAX_INT) > 0) {
            throw new \InvalidArgumentException(sprintf('The double "%s" is not a valid IPv%d address.', gmp_strval($ip), static::IP_VERSION));
        }

        return $ip;
    }

    /**
     * @param string $ip
     *
     * @return \GMP
     */
    private static function fromString(string $ip): \GMP
    {
        // binary, packed string
        if (@inet_ntop($ip) !== false) {
            if (static::NB_BYTES != strlen($ip)) {
                throw new \InvalidArgumentException(sprintf('The binary string "%s" is not a valid IPv%d address.', $ip, static::IP_VERSION));
            }
            $hex = unpack('H*', $ip);

            return gmp_init($hex[1], 16);
        }

        // valid IP string
        $filterFlag = constant('FILTER_FLAG_IPV'.static::IP_VERSION);
        if (filter_var($ip, FILTER_VALIDATE_IP, $filterFlag)) {
            $ip = inet_pton($ip);
            $hex = unpack('H*', $ip);

            return gmp_init($hex[1], 16);
        }

        // numeric string (decimal)
        if (ctype_digit($ip)) {
            $ip = gmp_init($ip, 10);
            if (gmp_cmp($ip, static::MAX_INT) > 0) {
                throw new \InvalidArgumentException(sprintf('"%s" is not a valid decimal IPv%d address.', gmp_strval($ip), static::IP_VERSION));
            }

            return $ip;
        }

        throw new \InvalidArgumentException(sprintf('The string "%s" is not a valid IPv%d address.', $ip, static::IP_VERSION));
    }

    /**
     * @param \GMP $ip
     *
     * @return \GMP
     */
    private static function fromGMP(\GMP $ip): \GMP
    {
        if (gmp_cmp($ip, 0) < 0 || gmp_cmp($ip, static::MAX_INT) > 0) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid decimal IPv%d address.', gmp_strval($ip), static::IP_VERSION));
        }

        return $ip;
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
            $n = constant("$this->class::NB_BITS"); // ugly, but necessary because of PHP 5.2
            $value = str_pad($value, $n, '0', STR_PAD_LEFT);
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
        $hex = str_pad($this->numeric(16), static::NB_BITS / 4, '0', STR_PAD_LEFT);

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
            $value = new $this->class($value);
        }

        return new $this->class(gmp_and($this->ip, $value->ip));
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
            $value = new $this->class($value);
        }

        return new $this->class(gmp_or($this->ip, $value->ip));
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
            $value = new $this->class($value);
        }

        $result = gmp_add($this->ip, $value->ip);

        if (gmp_cmp($result, 0) < 0 || gmp_cmp($result, constant("$this->class::MAX_INT")) > 0) {
            throw new \OutOfBoundsException();
        }

        return new $this->class($result);
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
            $value = new $this->class($value);
        }

        $result = gmp_sub($this->ip, $value->ip);

        if (gmp_cmp($result, 0) < 0 || gmp_cmp($result, constant("$this->class::MAX_INT")) > 0) {
            throw new \OutOfBoundsException();
        }

        return new $this->class($result);
    }

    /**
     * @see humanReadable()
     */
    public function __toString()
    {
        return $this->humanReadable();
    }

    /**
     * Return the version number (4 or 6).
     *
     * Note: this is left abstract because there is not late static binding
     * in PHP 5.2 (which I need to support).
     *
     * @return int
     */
    abstract public function getVersion();

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

    abstract public function isPrivate();

    /**
     * Return true if the address is allocated for public networks.
     *
     * @return bool
     */
    public function isPublic()
    {
        return !$this->isPrivate();
    }
}
