<?php

namespace Lightrail;

require_once '../test-config.php';
require_once '../init.php';
require_once '../vendor/autoload.php';


use PHPUnit\Framework\TestCase;

class StripeLightrailSplitTenderChargeTest extends TestCase {

	public function getBasicParams() {
		return array(
			'amount'    => 100,
			'currency'  => 'USD',
			'source'    => TestConfig::$stripeDemoToken,
			'shopperId' => TestConfig::$shopperId
		);
	}

	public function testSplitTender() {
		Lightrail::$apiKey = TestConfig::$apiKey;
		\Stripe\Stripe::setApiKey( TestConfig::$stripeTestApiKey );

		$splitTender = StripeLightrailSplitTenderCharge::create( $this->getBasicParams(), 99, 1 );
		$this->assertNotNull( $splitTender->lightrailTransaction );
		$this->assertNotNull( $splitTender->stripeCharge );
		$this->assertEquals( 99, $splitTender->getStripeShare() );
		$this->assertEquals( 1, $splitTender->getLightrailShare() );
	}

	public function testSplitTenderWithLowercaseCurrency() {
		Lightrail::$apiKey = TestConfig::$apiKey;
		\Stripe\Stripe::setApiKey( TestConfig::$stripeTestApiKey );

		$params             = $this->getBasicParams();
		$params['currency'] = 'usd';
		$splitTender        = StripeLightrailSplitTenderCharge::create( $params, 99, 1 );
		$this->assertNotNull( $splitTender->lightrailTransaction );
		$this->assertNotNull( $splitTender->stripeCharge );
		$this->assertEquals( 99, $splitTender->getStripeShare() );
		$this->assertEquals( 1, $splitTender->getLightrailShare() );
	}

	public function testSplitTenderWithUserSuppliedId() {
		Lightrail::$apiKey = TestConfig::$apiKey;
		\Stripe\Stripe::setApiKey( TestConfig::$stripeTestApiKey );

		$params = $this->getBasicParams();

		$userSuppliedId           = uniqid();
		$params['userSuppliedId'] = $userSuppliedId;

		$splitTender = StripeLightrailSplitTenderCharge::create( $params, 99, 1 );
		$this->assertNotNull( $splitTender->lightrailTransaction );
		$this->assertEquals( $userSuppliedId . '-CAPTURE', $splitTender->lightrailTransaction->userSuppliedId );
		$this->assertNotNull( $splitTender->stripeCharge );
		$this->assertEquals( 99, $splitTender->getStripeShare() );
		$this->assertEquals( 1, $splitTender->getLightrailShare() );
	}

	public function testSplitTenderWithIdempotencyKey() {
		Lightrail::$apiKey = TestConfig::$apiKey;
		\Stripe\Stripe::setApiKey( TestConfig::$stripeTestApiKey );

		$params = $this->getBasicParams();

		$userSuppliedId            = uniqid();
		$params['idempotency-key'] = $userSuppliedId;

		$splitTender = StripeLightrailSplitTenderCharge::create( $params, 99, 1 );
		$this->assertNotNull( $splitTender->lightrailTransaction );
		$this->assertEquals( $userSuppliedId . '-CAPTURE', $splitTender->lightrailTransaction->userSuppliedId );
		$this->assertNotNull( $splitTender->stripeCharge );
		$this->assertEquals( 99, $splitTender->getStripeShare() );
		$this->assertEquals( 1, $splitTender->getLightrailShare() );
	}

}
