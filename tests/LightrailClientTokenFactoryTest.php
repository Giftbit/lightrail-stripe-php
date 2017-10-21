<?php

namespace Lightrail;

require_once '../test-config.php';
require_once '../init.php';
require_once '../vendor/autoload.php';

use PHPUnit\Framework\TestCase;

class LightrailClientTokenFactoryTest extends TestCase {

	public function testJWT() {
		Lightrail::$apiKey= TestConfig::$apiKey;
		Lightrail::$clientSecret= TestConfig::$lightrailClientSecret;

		$token = LightrailClientTokenFactory::generate(TestConfig::$shopperId, 10000);
		$decoded = (array)\Firebase\JWT\JWT::decode($token, TestConfig::$lightrailClientSecret, array('HS256'));
		$this->assertEquals(TestConfig::$shopperId, $decoded['shopperId']);
	}

}
