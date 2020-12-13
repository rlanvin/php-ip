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

namespace PhpIP\Tests;

use PhpIP\IPv6Block;
use PHPUnit\Framework\TestCase;

class IPv6BlockTest extends TestCase
{
    public function testIterator()
    {
        $expectation = [
            '2001:db8:85a3:a:0:8a2e:370:f0',
            '2001:db8:85a3:a:0:8a2e:370:f1',
            '2001:db8:85a3:a:0:8a2e:370:f2',
            '2001:db8:85a3:a:0:8a2e:370:f3',
            '2001:db8:85a3:a:0:8a2e:370:f4',
            '2001:db8:85a3:a:0:8a2e:370:f5',
            '2001:db8:85a3:a:0:8a2e:370:f6',
            '2001:db8:85a3:a:0:8a2e:370:f7',
            '2001:db8:85a3:a:0:8a2e:370:f8',
            '2001:db8:85a3:a:0:8a2e:370:f9',
            '2001:db8:85a3:a:0:8a2e:370:fa',
            '2001:db8:85a3:a:0:8a2e:370:fb',
            '2001:db8:85a3:a:0:8a2e:370:fc',
            '2001:db8:85a3:a:0:8a2e:370:fd',
            '2001:db8:85a3:a:0:8a2e:370:fe',
            '2001:db8:85a3:a:0:8a2e:370:ff',
        ];

        $subnet = new IPv6Block('2001:0db8:85a3:a:0:8a2e:0370:f0/124');

        $this->assertEquals($expectation, iterator_to_array($subnet->getIterator()));
    }
}
