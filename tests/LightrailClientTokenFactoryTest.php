<?php

namespace Lightrail;

require_once '../test-config.php';
require_once '../init.php';
require_once '../vendor/autoload.php';

use PHPUnit\Framework\TestCase;

class LightrailClientTokenFactoryTest extends TestCase
{

    public function testJWT()
    {
        Lightrail::$apiKey = TestConfig::$apiKey;
        Lightrail::$sharedSecret = TestConfig::$lightrailSharedSecret;

        $token = LightrailClientTokenFactory::generate(TestConfig::$shopperId, 10000);
        $decoded = \Firebase\JWT\JWT::decode($token, TestConfig::$lightrailSharedSecret, array('HS256'));
        $this->assertEquals(TestConfig::$shopperId, $decoded->g->shi);
    }

}
