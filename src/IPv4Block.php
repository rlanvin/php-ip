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
 * An IPv4 CIDR block.
 */
class IPv4Block extends IPBlock
{
    protected $ip_class = 'PhpIP\IPv4';
    protected $class = __CLASS__;
}
