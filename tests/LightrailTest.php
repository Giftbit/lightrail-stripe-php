<?php

namespace Lightrail;
require_once '../test-config.php';
require_once '../init.php';

use PHPUnit\Framework\TestCase;

class LightrailTest extends TestCase {
	public function testPing()
	{
		Lightrail::$apiKey= TestConfig::$apiKey;
		$response = LightrailAPICall::ping();
		$this->assertEquals('TEST', $response['user']['mode']);
	}
}
