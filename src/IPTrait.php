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
 * Static methods shared by IPv4 and IPv6, but that shouldn't be
 * available in the base class
 */
trait IPTrait
{
    /**
     * @param int $ip
     *
     * @return IP
     * @throws \InvalidArgumentException when passed $ip is not valid
     */
    public static function createFromInt(int $ip): self
    {
        return new self(self::initGmpFromInt($ip));
    }

    /**
     * @param float $ip
     *
     * @return IP
     * @throws \InvalidArgumentException when passed $ip is not valid
     */
    public static function createFromFloat(float $ip): self
    {
        return new self(self::initGmpFromFloat($ip));
    }

    /**
     * @param string $ip
     *
     * @return IP
     * @throws \InvalidArgumentException when passed $ip is not valid
     */
    public static function createFromString(string $ip): self
    {
        return new self(self::initGmpfromString($ip));
    }

    /**
     * @param string $ip
     *
     * @return IP
     * @throws \InvalidArgumentException when passed $ip is not valid
     */
    public static function createFromBinaryString(string $ip): self
    {
        if (self::NB_BYTES != strlen($ip)) {
            throw new \InvalidArgumentException(sprintf('The binary string "%s" is not a valid IPv%d address.', $ip, self::IP_VERSION));
        }

        return new self(self::initGmpFromBinaryString($ip));
    }

    /**
     * @param string $ip
     *
     * @return IP
     * @throws \InvalidArgumentException when passed $ip is not valid
     */
    public static function createFromNumericString(string $ip): self
    {
        if (!ctype_digit($ip)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid numeric string.', $ip));
        }

        return new self(self::initGmpFromNumericString($ip));
    }

    /**
     * @param \GMP $ip
     *
     * @return IP
     */
    public static function createFromGmp(\GMP $ip): self
    {
        return new self($ip);
    }

}
