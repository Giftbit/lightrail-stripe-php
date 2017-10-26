<?php

namespace Lightrail;

require_once '../test-config.php';
require_once '../init.php';

use PHPUnit\Framework\TestCase;

class LightrailTransactionTest extends TestCase {
	public function getBasicParams() {
		return array(
			'value'     => -1,
			'currency'  => "USD",
			'shopperId' => TestConfig::$shopperId,
		);
	}

	public function testSimulateByShopperId() {
		Lightrail::$apiKey = TestConfig::$apiKey;
		$params            = $this->getBasicParams();
		$transaction       = LightrailTransaction::simulate( $params );
		$this->assertEquals( 'DRAWDOWN', $transaction->transactionType );
		$this->assertEquals( null, $transaction->transactionId );
	}

	public function testTransactionByShopperId() {
		Lightrail::$apiKey = TestConfig::$apiKey;
		$params            = $this->getBasicParams();

		$transaction = LightrailTransaction::create( $params );
		$this->assertNotNull( $transaction->transactionId );
		$this->assertEquals( - 1, $transaction->value );
		$this->assertEquals( 'DRAWDOWN', $transaction->transactionType );

		$params['value'] = 1;
		$transaction     = LightrailTransaction::create( $params );
		$this->assertNotNull( $transaction->transactionId );
		$this->assertEquals( 1, $transaction->value );
		$this->assertEquals( 'FUND', $transaction->transactionType );
	}

	public function testTransactionByShopperIdWithUserSuppliedId() {
		Lightrail::$apiKey = TestConfig::$apiKey;

		$params      = $this->getBasicParams();
		$params['userSuppliedId']=uniqid();

		$transaction = LightrailTransaction::create( $params );
		$this->assertNotNull( $transaction->transactionId );
		$this->assertEquals( - 1, $transaction->value );
		$this->assertEquals( 'DRAWDOWN', $transaction->transactionType );
		$this->assertEquals( $params['userSuppliedId'], $transaction->userSuppliedId );

		$params['value'] = 1;
		unset( $params['userSuppliedId'] );
		$transaction = LightrailTransaction::create( $params );
		$this->assertNotNull( $transaction->transactionId );
		$this->assertEquals( 1, $transaction->value );
		$this->assertEquals( 'FUND', $transaction->transactionType );
	}

	public function testPendingCaptureRefund() {
		Lightrail::$apiKey = TestConfig::$apiKey;
		$params            = $this->getBasicParams();

		$transaction = LightrailTransaction::createPending( $params );
		$this->assertNotNull( $transaction->transactionId );
		$this->assertEquals( - 1, $transaction->value );
		$this->assertEquals( 'PENDING_CREATE', $transaction->transactionType );

		$voidedTransaction = $transaction->void();
		$this->assertEquals( 'PENDING_VOID', $voidedTransaction->transactionType );

		$transaction        = LightrailTransaction::createPending( $params );
		$captureTransaction = $transaction->capture();
		$this->assertEquals( 'DRAWDOWN', $captureTransaction->transactionType );

		$refundTransaction = $captureTransaction->refund();
		$this->assertEquals( 'DRAWDOWN_REFUND', $refundTransaction->transactionType );
	}
}
