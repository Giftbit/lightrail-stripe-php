<?php

namespace Lightrail;

require_once '../test-config.php';
require_once '../init.php';

use PHPUnit\Framework\TestCase;

class LightrailTransactionTest extends TestCase {

	public function testSimulate() {
		Lightrail::$apiKey= TestConfig::$apiKey;
		$params = array();
		$params['value'] = -1;
		$params['currency'] = 'USD';
//		$params['shopperId'] = TestConfig::$shopperId;
		$transaction = LightrailTransaction::simulate($params);
		$this->assertEquals('DRAWDOWN', $transaction->transactionType);
		$this->assertEquals(-1, $transaction->value);
	}
}
