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

use PhpIP\IPv6;
use PHPUnit\Framework\TestCase;

class IPv6Test extends TestCase
{
    public function validAddresses()
    {
        $values = [
            // IP  compressed  decimal
            ['2a01:8200::', '2a01:8200::', '55835404833073476206743540170770874368'],
            ['2001:0db8:85a3:0000:0000:8a2e:0370:7334', '2001:db8:85a3::8a2e:370:7334', '42540766452641154071740215577757643572'],
            ['ffff:0db8::', 'ffff:db8::', '340277452873386678732099705461792571392'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', '340282366920938463463374607431768211455'],
            ['::1', '::1', '1', '1', '1'],

            // IPv4-mapped IPv6 addresses
            ['0000:0000:0000:0000:0000:0000:127.127.127.127', '::127.127.127.127', '2139062143'],
            ['::ffff:192.0.2.128', '::ffff:192.0.2.128', '281473902969472'],

            // init with numeric representation
            ['332314827956335977770735408709082546176', 'fa01:8200::', '332314827956335977770735408709082546176'],

            // init with GMP ressource
            [gmp_init('332314827956335977770735408709082546176'), 'fa01:8200::', '332314827956335977770735408709082546176'],
        ];

        // 32 bits
        if (PHP_INT_SIZE == 4) {
            $values = array_merge($values, [
                [-1, '::255.255.255.255', '4294967295'],
            ]);
        }
        // 64 bits
        elseif (PHP_INT_SIZE == 8) {
            $values = array_merge($values, [
                [-1, '::ffff:ffff:ffff:ffff', '18446744073709551615'],
            ]);
        }

        return $values;
    }

    public function invalidAddresses()
    {
        $values = [
            ["\t"],
            [[]],
            [new \stdClass()],
            ['-1'],
            [gmp_init('-1')],
            [gmp_init('340282366920938463463374607431768211456')],
            ['abcz'],
            [12.3],
            [-12.3],
            ['127.0.0.1'],
        ];

        // 32 bits
        if (PHP_INT_SIZE == 4) {
        }
        // 64 bits
        elseif (PHP_INT_SIZE == 8) {
        }

        return $values;
    }

    /**
     * @dataProvider validAddresses
     */
    public function testConstructValid($ip, $compressed)
    {
        $instance = new IPv6($ip);
        $this->assertEquals($compressed, (string) $instance);
    }

    /**
     * @dataProvider invalidAddresses
     */
    public function testConstructInvalid($ip)
    {
        $this->expectException(\InvalidArgumentException::class);

        $instance = new IPv6($ip);
    }

    /**
     * @dataProvider validAddresses
     */
    public function testConvertToNumeric($ip, $compressed, $dec)
    {
        $instance = new IPv6($ip);
        $array = unpack('H*', inet_pton($compressed));
        $this->assertEquals(ltrim($array[1], '0'), $instance->numeric(16), "Base 16 of $compressed");
        $this->assertEquals($dec, $instance->numeric(10), "Base 10 of $compressed");
    }

    public function testGetVersion()
    {
        $ipv6 = new IPv6('2001:acad::8888');
        $this->assertEquals(6, $ipv6->getVersion());
    }

    /**
     * @return array
     */
    public function humanReadableAddresses(): array
    {
        return [
            ['2a01:8200::', '2a01:8200:0000:0000:0000:0000:0000:0000'],
            ['2001:db8:85a3::8a2e:370:7334', '2001:0db8:85a3:0000:0000:8a2e:0370:7334'],
            ['ffff:db8::', 'ffff:0db8:0000:0000:0000:0000:0000:0000'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff'],
            ['::1', '0000:0000:0000:0000:0000:0000:0000:0001'],
            ['::', '0000:0000:0000:0000:0000:0000:0000:0000'],
        ];
    }

    /**
     * @dataProvider humanReadableAddresses
     *
     * @param $shortForm
     * @param $longForm
     */
    public function testHumanReadable($shortForm, $longForm)
    {
        $ip = new IPv6($shortForm);
        $this->assertEquals($shortForm, $ip->humanReadable(true));
        $this->assertEquals($shortForm, $ip->humanReadable());
        $this->assertEquals($longForm, $ip->humanReadable(false));
    }

    /**
     * @return array
     */
    public function getValidIpReversePointerPairs(): array
    {
        return [
            ['5b99:b88e:5764:12c8:220:f0f8:e82d:814d', 'd.4.1.8.d.2.8.e.8.f.0.f.0.2.2.0.8.c.2.1.4.6.7.5.e.8.8.b.9.9.b.5.ip6.arpa.'],
            ['55c1:4c59:7d07:9d28:2f7a:c42b:188b:cd68', '8.6.d.c.b.8.8.1.b.2.4.c.a.7.f.2.8.2.d.9.7.0.d.7.9.5.c.4.1.c.5.5.ip6.arpa.'],
            ['2f33:1fa8:ed4d:5dd:5f7d:e743:2f21:7a75', '5.7.a.7.1.2.f.2.3.4.7.e.d.7.f.5.d.d.5.0.d.4.d.e.8.a.f.1.3.3.f.2.ip6.arpa.'],
            ['9618:4fb0:cd5a:6c3e:2cc3:ceaf:d200:e17c', 'c.7.1.e.0.0.2.d.f.a.e.c.3.c.c.2.e.3.c.6.a.5.d.c.0.b.f.4.8.1.6.9.ip6.arpa.'],
            ['19ed:f7bd:bb50:d544:6b58:7eda:e107:3f97', '7.9.f.3.7.0.1.e.a.d.e.7.8.5.b.6.4.4.5.d.0.5.b.b.d.b.7.f.d.e.9.1.ip6.arpa.'],
            ['ab67:4229:5551:2e4:489c:5f54:aedc:9aab', 'b.a.a.9.c.d.e.a.4.5.f.5.c.9.8.4.4.e.2.0.1.5.5.5.9.2.2.4.7.6.b.a.ip6.arpa.'],
            ['30c0:2b73:4244:9c5e:6c32:9d5c:9e63:6b00', '0.0.b.6.3.6.e.9.c.5.d.9.2.3.c.6.e.5.c.9.4.4.2.4.3.7.b.2.0.c.0.3.ip6.arpa.'],
            ['23f0:569a:b262:3e84:e2ee:b6f5:81a2:430d', 'd.0.3.4.2.a.1.8.5.f.6.b.e.e.2.e.4.8.e.3.2.6.2.b.a.9.6.5.0.f.3.2.ip6.arpa.'],
            ['abed:7103:b723:5ad0:33c5:8f21:ecea:4251', '1.5.2.4.a.e.c.e.1.2.f.8.5.c.3.3.0.d.a.5.3.2.7.b.3.0.1.7.d.e.b.a.ip6.arpa.'],
            ['d31e:18c9:3464:6247:3bbc:156b:4aaf:ea0e', 'e.0.a.e.f.a.a.4.b.6.5.1.c.b.b.3.7.4.2.6.4.6.4.3.9.c.8.1.e.1.3.d.ip6.arpa.'],
            ['68fb:f553:f294:d80e:b53f:b285:a805:3c78', '8.7.c.3.5.0.8.a.5.8.2.b.f.3.5.b.e.0.8.d.4.9.2.f.3.5.5.f.b.f.8.6.ip6.arpa.'],
            ['8523:4983:2cce:fd85:8203:3e03:de8c:5ba8', '8.a.b.5.c.8.e.d.3.0.e.3.3.0.2.8.5.8.d.f.e.c.c.2.3.8.9.4.3.2.5.8.ip6.arpa.'],
            ['b99b:4b10:b70b:eabf:d1b2:3ba6:8696:c33d', 'd.3.3.c.6.9.6.8.6.a.b.3.2.b.1.d.f.b.a.e.b.0.7.b.0.1.b.4.b.9.9.b.ip6.arpa.'],
            ['4792:3c2d:3c11:9176:6029:7b73:78a2:8964', '4.6.9.8.2.a.8.7.3.7.b.7.9.2.0.6.6.7.1.9.1.1.c.3.d.2.c.3.2.9.7.4.ip6.arpa.'],
            ['183c:dc41:52d5:2387:f55e:d63e:55f1:f60d', 'd.0.6.f.1.f.5.5.e.3.6.d.e.5.5.f.7.8.3.2.5.d.2.5.1.4.c.d.c.3.8.1.ip6.arpa.'],
            ['5073:66be:99f5:fa1c:f971:e7dc:294:f9e', 'e.9.f.0.4.9.2.0.c.d.7.e.1.7.9.f.c.1.a.f.5.f.9.9.e.b.6.6.3.7.0.5.ip6.arpa.'],
        ];
    }

    /**
     * @return array
     */
    public function getValidLinkLocalAddresses(): array
    {
        return [
            ['fe80::'],
            ['fe80:2c27:8239:eaea:ac64:bf11:8cbf:7bc3'],
            ['fe80:4ca2:6fa5:1615:b399:3c37:b06:5f20'],
            ['fe80:6163:6af3:a9ce:bc8b:69d7:ce4:2460'],
            ['fe80:35ac:fc8f:933f:3c4c:4644:103c:5f60'],
            ['fe80:f381:1cfc:868c:284d:274d:e963:b688'],
            ['fe80:9eb3:2847:e048:9588:6020:3fbc:d373'],
            ['fe80:48ce:5e67:28ea:a869:aead:1452:a485'],
            ['fe80:2c91:feb0:17db:3127:a8a0:6ed0:62b7'],
            ['fe80:9240:9b2b:52f0:48f9:676a:3d5b:1b35'],
            ['fe80:3410:159b:3947:4d1b:e13f:33af:6e9d'],
            ['fe80:5013:20d7:2222:2b15:dea1:251d:9e09'],
            ['fe80:7099:c8e5:4ab4:cb4e:b592:aee7:782f'],
            ['fe80:7ad8:45f2:2c33:bbf0:892a:122c:e029'],
            ['fe80:fe46:bc9a:d5a1:7c0a:5baa:f087:7a2b'],
            ['febf:ffff:ffff:ffff:ffff:ffff:ffff:ffff'],
        ];
    }

    /**
     * @dataProvider getValidIpReversePointerPairs
     *
     * @param string $ip
     * @param string $reversePointer
     */
    public function testGetReversePointer(string $ip, string $reversePointer)
    {
        $ipv6 = new IPv6($ip);
        $this->assertEquals($reversePointer, $ipv6->reversePointer());
    }

    /**
     * @return array
     */
    public function getInvalidLinkLocalAddresses(): array
    {
        return [
            ['fec0::'],
            ['784:ec5b:3482:4aca:c956:5cd4:1482:79b0'],
            ['e55:3cac:7aeb:dc4c:6a9b:8f4f:b20:31be'],
            ['ecc7:8c14:720b:60f4:4f39:830f:4360:1c8c'],
            ['71e3:a3c:ef5e:520:8024:489e:3138:e14f'],
            ['2635:72dc:cefa:71a7:66bd:26a0:2fcb:d25b'],
            ['caf4:88a1:7237:9ed0:240d:613:6999:5560'],
            ['c662:35a3:7f65:9a45:e6e5:dd3d:cf79:fd9'],
            ['be83:9604:102d:64ff:5ff:4c66:2899:6d37'],
            ['3399:bae2:f27:67ed:e462:7fa2:fc0a:c99c'],
            ['21aa:47d5:f044:4c1f:4d3d:4136:4141:da85'],
            ['7af:3f67:1698:fe2e:7f67:50e7:7152:4048'],
            ['689c:5299:52b4:c61d:716f:a85a:d117:cf18'],
            ['18e0:79d2:6216:99f:9bd6:4b21:406:d468'],
            ['159b:cba7:1366:20c:67:6afd:20b8:5f43'],
            ['fdc6:714e:3c3:5a0a:6c0d:d424:e69d:3f61'],
        ];
    }

    /**
     * Data provider for testBit_xor().
     *
     * @return array
     */
    public function getXorData(): array
    {
        return [
            //IP_1                                      IP_2                                       IP_1 XOR IP_2
            ['de73:9b08:52f7:2026:b159:b25e:7ddb:a552', 'a63e:4497:ff76:460f:efca:8620:803f:4872', '784d:df9f:ad81:6629:5e93:347e:fde4:ed20'],
            ['5f4b:8229:1220:efec:eda2:2df1:ebf9:1d2e', 'df5f:b54e:463b:50c9:f1de:9801:7eaa:5667', '8014:3767:541b:bf25:1c7c:b5f0:9553:4b49'],
            ['f5e9:213a:19b0:75e8:785e:9cc9:d378:a469', '1081:0ddc:f92d:b99e:c997:505c:64a3:5c29', 'e568:2ce6:e09d:cc76:b1c9:cc95:b7db:f840'],
            ['a9b3:701e:ca1c:5eb5:95e8:de8f:98bb:c612', '6167:12ca:ac0b:a3a1:311e:49c0:9184:f783', 'c8d4:62d4:6617:fd14:a4f6:974f:093f:3191'],
            ['4588:15a5:809d:a693:08e3:b6a4:3c22:549f', '0049:db10:882a:8af9:57b6:ec2c:20f6:88a9', '45c1:ceb5:08b7:2c6a:5f55:5a88:1cd4:dc36'],
            ['d342:69a2:b73f:fd84:5b7e:44ea:054b:b788', '1d0a:e852:8586:ed03:d6c3:bc3c:9d9b:fd79', 'ce48:81f0:32b9:1087:8dbd:f8d6:98d0:4af1'],
            ['ca48:9506:4896:c62e:90e1:df74:0a17:3640', '491d:315f:c20e:fa82:bb79:55f5:6df1:8319', '8355:a459:8a98:3cac:2b98:8a81:67e6:b559'],
            ['a373:e407:5296:3f84:9985:2081:86ac:8b71', '1b42:d2eb:2039:fc20:9ff2:315a:f6d1:0349', 'b831:36ec:72af:c3a4:0677:11db:707d:8838'],
            ['083c:887f:b043:dfd3:5e49:e15b:f2c6:847b', 'd3b9:4313:56d4:b35c:3c83:f61d:0948:4371', 'db85:cb6c:e697:6c8f:62ca:1746:fb8e:c70a'],
            ['6831:fea2:f4c3:bea4:e61d:47ec:ad61:0ce7', '5fe8:c991:e17b:e136:7b76:38ef:57fd:9fe5', '37d9:3733:15b8:5f92:9d6b:7f03:fa9c:9302'],
        ];
    }

    /**
     * Data provider for testBit_negate().
     *
     * @return array
     */
    public function getNegateData(): array
    {
        return [
            ['1e11:629a:b16e:ae60:aea4:1996:a8af:3711', 'e1ee:9d65:4e91:519f:515b:e669:5750:c8ee'],
            ['b26b:5c0f:f85:e738:d73e:2371:3b29:c58e', '4d94:a3f0:f07a:18c7:28c1:dc8e:c4d6:3a71'],
            ['d3af:91af:8f9a:520d:4716:123d:bd77:c943', '2c50:6e50:7065:adf2:b8e9:edc2:4288:36bc'],
            ['bc61:39b0:5186:f704:b395:fc8a:62e:1947', '439e:c64f:ae79:8fb:4c6a:375:f9d1:e6b8'],
            ['d7fe:7772:5c74:7034:473a:cb6d:b428:714d', '2801:888d:a38b:8fcb:b8c5:3492:4bd7:8eb2'],
            ['913:50a3:5718:21b9:7db6:8db3:a186:48a8', 'f6ec:af5c:a8e7:de46:8249:724c:5e79:b757'],
            ['5d4:6e2c:e25d:e477:fda6:a923:a927:3592', 'fa2b:91d3:1da2:1b88:259:56dc:56d8:ca6d'],
            ['1abc:79b3:6800:45ae:da3c:399:e26f:f867', 'e543:864c:97ff:ba51:25c3:fc66:1d90:798'],
            ['901b:31b6:3ddb:7218:45b1:7ba0:4024:fd90', '6fe4:ce49:c224:8de7:ba4e:845f:bfdb:26f'],
            ['23fb:1c0d:db1f:14b1:9634:3ec2:8743:e7c2', 'dc04:e3f2:24e0:eb4e:69cb:c13d:78bc:183d'],
        ];
    }

    /**
     * Data provider for testMatches().
     *
     * @return array
     */
    public function getMatchesData(): array
    {
        $data = [];

        //Match all addresses whose 4th octet is "1337".
        $data[] = [
            'ip' => 'fe80:0:0:1337::',
            'mask' => 'ffff:ffff:ffff:0:ffff:ffff:ffff:ffff',
            'matches' => [
                'c30d:ec5c:ff82:1337:358d:631e:2918:e812',
                'b7bc:af65:ec86:1337:7d96:51eb:7955:4a65',
                '74b1:4339:1304:1337:481b:32f5:504d:a277',
                'd302:62e9:6f57:1337:97d9:60f3:95a1:5804',
                'f30b:f965:c810:1337:25fa:fec9:83d7:d29e',
                '2009:9b8c:a747:1337:b223:9e3a:1676:8a9a',
                '4ddf:eebd:2a16:1337:cc0e:3d4b:d752:e28e',
                '14c0:bda3:a182:1337:e357:5f1e:601c:2118',
            ],
            'non_matches' => [
                '591f:531e:8b94:ed1c:2f59:6255:b74e:c396',
                '3471:1337:d49f:de59:17b9:626b:c060:fbef',
                'e304:4137:b59e:34b7:1f70:82d9:63fe:1337',
                'd775:867c:6101:eaee:08c9:e0b2:1337:365e',
                '1fdb:192b:eb7a:b883:3c85:1337:8aab:5072',
                '1337:1337:1337:0:1337:1337:1337:1337',
                'ae07:58e8:ea9e:2fb1:bbce:468a:7e1a:72aa',
                '5779:ed72:069c:8e09:3bf1:18ec:97e0:f99b',
            ],
        ];

        //Match all addresses in a220:db8:c3b0::/48 where the last octet is an even number.
        $data[] = [
            'ip' => 'a220:db8:c3b0::',
            'mask' => '::ffff:ffff:ffff:ffff:fffe',
            'matches' => [
                'a220:db8:c3b0:09a0:ff6d:7e70:8763:9668',
                'a220:db8:c3b0:87a9:c49c:669c:1083:6ff2',
                'a220:db8:c3b0:0ca1:e7c4:1848:cc3e:b396',
                'a220:db8:c3b0:ca85:c338:071f:06fc:05a2',
                'a220:db8:c3b0:c624:24fc:1f57:cac2:b634',
                'a220:db8:c3b0:56aa:7dd2:e87c:8177:f2f2',
                'a220:db8:c3b0:b8c4:d334:29ea:9bbc:16c8',
                'a220:db8:c3b0:ee6e:aee6:c196:1acb:e43a',
            ],
            'non_matches' => [
                'a220:db8:c3b0:b8c4:d334:29ea:9bbc:16c9',
                'a220:db8:c3b0:ee6e:aee6:c196:1acb:e43b',
                'e97d:75a3:a265:adc5:8662:688e:bfcd:bcc8',
                'bd83:c5de:f93f:cb75:05b7:9f90:e4b6:53a4',
                '54fe:b07d:5b5a:66b0:83d5:f71e:5b7b:ef6c',
                '2754:511e:8ddc:0d7d:8062:ab8d:d6b8:d9e6',
                '94dc:d885:02f1:591e:0d5c:858c:8895:7f7f',
                'b388:899b:8f2d:5463:8d3e:2e01:5e2d:a698',
            ],
        ];

        return $data;
    }

    /**
     * @dataProvider getValidLinkLocalAddresses
     *
     * @param string $address
     */
    public function testIsLinkLocalReturnsTrueForValidAddresses(string $address)
    {
        $ip = new IPv6($address);
        $this->assertTrue($ip->isLinkLocal());
    }

    /**
     * @dataProvider getInvalidLinkLocalAddresses
     *
     * @param string $address
     */
    public function testIsLinkLocalReturnsFalseForInvalidAddresses(string $address)
    {
        $ip = new IPv6($address);
        $this->assertFalse($ip->isLinkLocal());
    }

    /**
     * @return array
     */
    public function getInvalidLoopbackAddresses(): array
    {
        return [
            ['2a01:8200::'],
            ['2001:db8:85a3::8a2e:370:7334'],
            ['ffff:db8::'],
            ['ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff'],
        ];
    }

    public function testIsLoopbackReturnsTrue()
    {
        $ip = new IPv6('::1');
        $this->assertTrue($ip->isLoopback());
    }

    /**
     * @dataProvider getInvalidLoopbackAddresses
     *
     * @param string $invalidLoopback
     */
    public function testIsLoopbackReturnsFalseForOtherAddresses(string $invalidLoopback)
    {
        $address = new IPv6($invalidLoopback);

        $this->assertFalse($address->isLoopback());
    }

    /**
     * @dataProvider getXorData
     *
     * @param string $ip_1
     * @param string $ip_2
     * @param string $xor
     */
    public function testBit_xor(string $ip_1, string $ip_2, string $xor)
    {
        $this->assertEquals(IPv6::create($xor), IPv6::create($ip_1)->bit_xor($ip_2));
    }

    /**
     * @dataProvider getNegateData
     *
     * @param string $ip
     * @param string $negation
     */
    public function testBit_negate(string $ip, string $negation)
    {
        $this->assertEquals(IPv6::create($negation), IPv6::create($ip));
    }

    /**
     * @dataProvider getMatchesData
     *
     * @param mixed $ip
     * @param mixed $mask
     * @param array $matches
     * @param array $non_matches
     */
    public function testMatches($ip, $mask, array $matches, array $non_matches)
    {
        $ip = IPv6::create($ip);

        foreach ($matches as $hostIP) {
            $this->assertTrue($ip->matches($hostIP, $mask), sprintf('Failed asserting host IP "%s" matches with IP: %s and mask %s.', $hostIP, $ip, $mask));
        }

        foreach ($non_matches as $hostIP) {
            $this->assertFalse($ip->matches($hostIP, $mask), sprintf('Failed asserting host IP "%s" DOES NOT match with IP: %s and mask %s.', $hostIP, $ip, $mask));
        }
    }

    /**
     * Test that the IP::matches() mask default of 0 matches the entire IP address exactly.
     */
    public function testDefaultMaskValueMatchesEntireIpAddress()
    {
        $ip = IPv6::create('14c0:bda3:a182:4a16:e357:5f1e:601c:2118');

        $this->assertTrue($ip->matches('14c0:bda3:a182:4a16:e357:5f1e:601c:2118'));
        $this->assertFalse($ip->matches('14c0:bda3:a182:4a16:e357:5f1e:601c:2119'));
    }
}
