<?php

namespace LightrailStripe;

$dotenv = new \Dotenv\Dotenv( __DIR__ . "/.." );
$dotenv->load();
require_once __DIR__ . '/../init.php';

use PHPUnit\Framework\TestCase;

class StripeLightrailSplitTenderChargeTest extends TestCase {
	public static function setUpBeforeClass() {
		\Lightrail\Lightrail::$apiKey = getEnv( "LIGHTRAIL_API_KEY" );
		\Stripe\Stripe::setApiKey( getenv( "STRIPE_API_KEY" ) );
	}

	public function getBasicParams() {
		return array(
			'amount'    => 100,
			'currency'  => 'USD',
			'source'    => getenv("STRIPE_DEMO_TOKEN"),
			'shopperId' => getenv("SHOPPER_ID")
		);
	}

	public function testSplitTender() {
		$splitTender = StripeLightrailSplitTenderCharge::create( $this->getBasicParams(), 99, 1 );
		$this->assertNotNull( $splitTender->lightrailTransaction );
		$this->assertNotNull( $splitTender->stripeCharge );
		$this->assertEquals( 99, $splitTender->getStripeShare() );
		$this->assertEquals( 1, $splitTender->getLightrailShare() );
	}

	public function testSplitTenderWithLowercaseCurrency() {
		$params             = $this->getBasicParams();
		$params['currency'] = 'usd';
		$splitTender        = StripeLightrailSplitTenderCharge::create( $params, 99, 1 );
		$this->assertNotNull( $splitTender->lightrailTransaction );
		$this->assertNotNull( $splitTender->stripeCharge );
		$this->assertEquals( 99, $splitTender->getStripeShare() );
		$this->assertEquals( 1, $splitTender->getLightrailShare() );
	}

	public function testSplitTenderWithUserSuppliedId() {
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
