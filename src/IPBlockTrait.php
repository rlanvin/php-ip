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
 * Static methods shared by IPv4Block and IPv6Block, but that shouldn't be
 * available in the base class
 */
trait IPBlockTrait
{
    /**
     * @var array Cache for the private blocks IPBlock instances
     */
    static public $private_blocks = null;

    /**
     * @var array Cache for the reserved blocks IPBlock instances
     */
    static public $reserved_blocks = null;

    /**
     * Returns an array with the private blocks as IPBlock objects.
     *
     * The array is then cached so the IPBlock objects don't need to be instanciated
     * anymore. This makes checking IP::isPrivate() a lot faster if more than once
     * in the same script.
     *
     * @return array An array of IPBlock
     */
    public static function getPrivateBlocks(): array
    {
        if (self::$private_blocks === null) {
            self::$private_blocks = [];
            foreach (self::PRIVATE_BLOCKS as $block) {
                self::$private_blocks[] = new self($block);
            }
        }

        return self::$private_blocks;
    }

    /**
     * Returns an array with the IANA reserved blocks as IPBlock objects.
     *
     * The array is then cached so the IPBlock objects don't need to be instanciated
     * anymore. This makes checking IP::isReserved() a lot faster if more than once
     * in the same script.
     *
     * @return array An array of IPBlock
     */
    public static function getReservedBlocks(): array
    {
        if (self::$reserved_blocks === null) {
            self::$reserved_blocks = [];
            foreach (self::RESERVED_BLOCKS as $block) {
                self::$reserved_blocks[] = new self($block);
            }
        }

        return self::$reserved_blocks;
    }

    /**
     * @return IPBlock
     */
    public static function getLoopbackBlock(): IPBlock
    {
        return new self(self::LOOPBACK_BLOCK);
    }

    /**
     * @return IPBlock
     */
    public static function getLinkLocalBlock(): IPBlock
    {
        return new self(self::LINK_LOCAL_BLOCK);
    }
}
