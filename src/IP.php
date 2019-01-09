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
 * Base class to manipulate an IP address.
 */
abstract class IP
{
    const IP_VERSION = null;
    const NB_BITS = null;
    const MAX_INT = null;

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
     * @var array
     */
    protected static $privateRanges = [];

    /**
     * Return human readable representation of the IP (e.g. 127.0.0.1 or ::1).
     *
     * @return string
     */
    abstract public function humanReadable(): string;

    /**
     * Constructor tries to guess what is the $ip.
     *
     * @param mixed $ip String, binary string, int or float
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($ip)
    {
        if (is_int($ip)) {
            $this->fromInt($ip);

            return;
        }

        if (is_float($ip)) {
            $this->fromFloat($ip);

            return;
        }

        if (is_string($ip)) {
            $this->fromString($ip);

            return;
        }

        if ($ip instanceof \GMP) {
            $this->fromGmp($ip);

            return;
        }

        throw new \InvalidArgumentException(sprintf('Unsupported argument type "%s".', gettype($ip)));
    }

    /**
     * Take an IP string/int and return an object of the correct type.
     *
     * Either IPv4 or IPv6 may be supplied, but integers less than 2^32 will
     * be considered to be IPv4 by default.
     *
     * @param mixed $ip Anything that can be converted into an IP (string, int, bin, etc.)
     *
     * @return IP
     */
    public static function create($ip): IP
    {
        try {
            return new IPv4($ip);
        } catch (\InvalidArgumentException $e) {
        }

        try {
            return new IPv6($ip);
        } catch (\InvalidArgumentException $e) {
        }

        throw new \InvalidArgumentException(sprintf('%s does not appear to be an IPv4 or IPv6 address', $ip));
    }

    /**
     * Return numeric representation of the IP in base $base.
     *
     * The return value is a PHP string. It can base used for comparison.
     *
     * @param int $base from 2 to 36
     *
     * @return string
     */
    public function numeric($base = 10): string
    {
        if ($base < 2 || $base > 62) {
            throw new \InvalidArgumentException('Base must be between 2 and 62 (inclusive).');
        }

        return gmp_strval($this->ip, $base);
    }

    /**
     * Return binary string representation.
     *
     * @return string Binary string
     */
    public function binary(): string
    {
        return inet_pton($this->humanReadable());
    }

    /**
     * Bitwise AND.
     *
     * @param $value mixed anything that can be converted into an IP object
     *
     * @return IP
     */
    public function bit_and($value): IP
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
    public function bit_or($value): IP
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
    public function plus($value): IP
    {
        $isAddition = true;

        if ($value < 0) {
            $isAddition = false;
            $value = abs($value);
        }

        if (0 == $value) {
            return clone $this;
        }

        if (!$value instanceof self) {
            $value = new static($value);
        }

        $result = $isAddition ? gmp_add($this->ip, $value->ip) : gmp_sub($this->ip, $value->ip);

        if (gmp_cmp($result, 0) < 0 || gmp_cmp($result, static::MAX_INT) > 0) {
            throw new \OutOfBoundsException();
        }

        return new static($result);
    }

    /**
     * Minus(-).
     *
     * @throws \OutOfBoundsException
     *
     * @param mixed $value Anything that can be converted into an IP object
     *
     * @return IP
     */
    public function minus($value): IP
    {
        if ($value < 0) {
            return $this->plus(abs($value));
        }

        if (0 == $value) {
            return clone $this;
        }

        if (!$value instanceof self) {
            $value = new static($value);
        }

        $result = gmp_sub($this->ip, $value->ip);

        if (gmp_cmp($result, 0) < 0 || gmp_cmp($result, static::MAX_INT) > 0) {
            throw new \OutOfBoundsException();
        }

        return new static($result);
    }

    /**
     * @see humanReadable()
     */
    public function __toString(): string
    {
        return $this->humanReadable();
    }

    /**
     * Check if the IP is contained in given block.
     *
     * @param $block mixed Anything that can be converted into an IPBlock
     *
     * @return bool
     */
    public function isIn($block): bool
    {
        if (!$block instanceof IPBlock) {
            $block = IPBlock::create($block);
        }

        return $block->contains($this);
    }

    /**
     * @return bool
     */
    public function isPrivate(): bool
    {
        if (null !== $this->is_private) {
            return $this->is_private;
        }

        $this->is_private = false;

        foreach (static::$privateRanges as $range) {
            $this->is_private |= $this->isIn($range);
        }

        return $this->is_private;
    }

    /**
     * Return true if the address is allocated for public networks.
     *
     * @return bool
     */
    public function isPublic(): bool
    {
        return !$this->isPrivate();
    }

    /**
     * Return the version number (4 or 6).
     *
     * @return int
     */
    public function getVersion(): int
    {
        return static::IP_VERSION;
    }

    /**
     * Construct from integer.
     *
     * @param int $ip
     */
    protected function fromInt(int $ip): void
    {
        $ip = gmp_init(sprintf('%u', $ip), 10);

        if (gmp_cmp($ip, static::MAX_INT) > 0) {
            throw new \InvalidArgumentException(sprintf('The integer %s is not a valid IPv4 address', gmp_strval($ip)));
        }

        $this->ip = $ip;
    }

    /**
     * Construct from string.
     *
     * @param string $ip
     */
    protected function fromString(string $ip): void
    {
        // binary string
        if (false !== @inet_ntop($ip)) {
            $strLen = static::NB_BITS / 8;

            if ($strLen != strlen($ip)) {
                throw new \InvalidArgumentException('The binary string is not a valid IPv6 address');
            }

            $hex = unpack('H*', $ip);
            $this->ip = gmp_init($hex[1], 16);

            return;
        }

        $filter_flag = constant('FILTER_FLAG_IPV'.static::IP_VERSION);

        if (filter_var($ip, FILTER_VALIDATE_IP, $filter_flag)) {
            $ip = inet_pton($ip);
            $hex = unpack('H*', $ip);
            $this->ip = gmp_init($hex[1], 16);

            return;
        }

        // numeric string (decimal)
        if (ctype_digit($ip)) {
            $ip = gmp_init($ip, 10);

            if (gmp_cmp($ip, static::MAX_INT) > 0) {
                throw new \InvalidArgumentException(sprintf('The decimal %s is not a valid IPv%d address.', gmp_strval($ip), static::IP_VERSION));
            }

            $this->ip = $ip;

            return;
        }

        throw new \InvalidArgumentException(sprintf('%s is not a valid IPv%d address.', $ip, static::IP_VERSION));
    }

    /**
     * Construct from float.
     *
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
            throw new \InvalidArgumentException(sprintf('The double %s is not a valid IPv%d address.', gmp_strval($ip), static::IP_VERSION));
        }

        $this->ip = $ip;
    }

    /**
     * Construct from GMP object.
     *
     * @param \GMP $ip
     */
    protected function fromGmp(\GMP $ip): void
    {
        if (gmp_cmp($ip, 0) < 0 || gmp_cmp($ip, static::MAX_INT) > 0) {
            throw new \InvalidArgumentException(sprintf('%s is not a valid decimal IPv%d address', gmp_strval($ip), static::IP_VERSION));
        }

        $this->ip = $ip;
    }
}
