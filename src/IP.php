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

namespace PHPIP;

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
     * For IPv4, this will be an SIGNED int (32 bits).
     * For IPv6, this will be a GMP ressource (128 bits big int).
     *
     * @var mixed
     */
    protected $ip;

    /**
     * @var bool
     */
    protected $is_private;

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
        } catch (\InvalidArgumentException $e) {}

        try {
            return new IPv6($ip);
        } catch (\InvalidArgumentException $e) {}

        throw new \InvalidArgumentException(sprintf('%s does not appear to be an IPv4 or IPv6 address', $ip));
    }

    /**
     * Return human readable representation of the IP (e.g. 127.0.0.1 or ::1).
     *
     * @return string
     */
    abstract public function humanReadable(): string;

    /**
     * Return addresses that are reserved as per IANA IPv4/6 Special Registries.
     *
     * @return array
     */
    abstract public function getPrivateRanges(): array;

    /**
     * @param int $ip
     */
    abstract protected function fromInt(int $ip): void;

    /**
     * @param float $ip
     */
    abstract protected function fromFloat(float $ip): void;

    /**
     * @param string $ip
     */
    abstract protected function fromString(string $ip): void;

    /**
     * @param \GMP $ip
     */
    abstract protected function fromGmp(\GMP $ip): void;

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

        throw new \InvalidArgumentException('Unsupported argument type: '.gettype($ip));
    }

    /**
     * Return numeric representation of the IP in base $base.
     *
     * The return value is a PHP string. It can base used for comparison.
     *
     * @param  int $base from 2 to 36
     *
     * @return string
     */
    public function numeric($base = 10): string
    {
        if ($base < 2 || $base > 62) {
            throw new \InvalidArgumentException('Base must be between 2 and 36 (included)');
        }

        $value = gmp_strval($this->ip, $base);

        if (2 == $base) {
            $value = str_pad($value, static::NB_BITS, '0', STR_PAD_LEFT);
        }

        return $value;
    }

    /**
     * Return binary string representation.
     *
     * @todo could be optimized with pack() instead?
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

        foreach ($this->getPrivateRanges() as $range) {
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
     * Note: this is left abstract because there is not late static binding
     * in PHP 5.2 (which I need to support).
     *
     * @return int
     */
    public function getVersion(): int
    {
        return static::IP_VERSION;
    }
}
