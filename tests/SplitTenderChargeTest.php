<?php

namespace LightrailStripe;

$dotenv = new \Dotenv\Dotenv( __DIR__ . "/.." );
$dotenv->load();
require_once __DIR__ . '/../init.php';

use PHPUnit\Framework\TestCase;

class SplitTenderChargeTest extends TestCase {
	public static function setUpBeforeClass() {
		\Lightrail\Lightrail::$apiKey = getEnv( "LIGHTRAIL_API_KEY" );
		\Stripe\Stripe::setApiKey( getenv( "STRIPE_API_KEY" ) );
	}

	public function getBasicParams() {
		return array(
			'amount'    => 100,
			'currency'  => 'USD',
			'source'    => getenv( "STRIPE_DEMO_TOKEN" ),
			'shopperId' => getenv( "SHOPPER_ID" ),
		);
	}

	public function testSplitTender() {
		$splitTender = SplitTenderCharge::create( $this->getBasicParams(), 1 );
		$this->assertNotNull( $splitTender->lightrailTransaction );
		$this->assertNotNull( $splitTender->stripeCharge );
		$this->assertEquals( 99, $splitTender->getStripeShare() );
		$this->assertEquals( 1, $splitTender->getLightrailShare() );
	}

	public function testSplitTenderLightrailOnly() {
		$splitTender = SplitTenderCharge::create( $this->getBasicParams(), 100 );
		$this->assertNotNull( $splitTender->lightrailTransaction );
		$this->assertNull( $splitTender->stripeCharge );
		$this->assertEquals( 100, $splitTender->getLightrailShare() );
	}

	public function testSplitTenderStripeOnly() {
		$splitTender = SplitTenderCharge::create( $this->getBasicParams(), 0 );
		$this->assertNull( $splitTender->lightrailTransaction );
		$this->assertNotNull( $splitTender->stripeCharge );
		$this->assertEquals( 100, $splitTender->getStripeShare() );
	}

	public function testSplitTenderWithLowercaseCurrency() {
		$params             = $this->getBasicParams();
		$params['currency'] = 'usd';
		$splitTender        = SplitTenderCharge::create( $params, 1 );

		$this->assertNotNull( $splitTender->lightrailTransaction );
		$this->assertNotNull( $splitTender->stripeCharge );
		$this->assertEquals( 99, $splitTender->getStripeShare() );
		$this->assertEquals( 1, $splitTender->getLightrailShare() );
	}

	public function testSplitTenderWithUserSuppliedId() {
		$params = $this->getBasicParams();

		$userSuppliedId           = uniqid();
		$params['userSuppliedId'] = $userSuppliedId;

		$splitTender = SplitTenderCharge::create( $params, 1 );
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

		$splitTender = SplitTenderCharge::create( $params, 1 );
		$this->assertNotNull( $splitTender->lightrailTransaction );
		$this->assertEquals( $userSuppliedId . '-CAPTURE', $splitTender->lightrailTransaction->userSuppliedId );
		$this->assertNotNull( $splitTender->stripeCharge );
		$this->assertEquals( 99, $splitTender->getStripeShare() );
		$this->assertEquals( 1, $splitTender->getLightrailShare() );
	}

	public function testSplitTenderWithMetadata() {
		$params             = $this->getBasicParams();
		$params['metadata'] = array( 'test' => 'test' );

		$splitTender = SplitTenderCharge::create( $params, 1 );

		$this->assertEquals( $splitTender->lightrailTransaction->metadata['_split_tender_total'], 100 );
		$this->assertEquals( $splitTender->stripeCharge->metadata['_split_tender_total'], 100 );

		$this->assertEquals( $splitTender->lightrailTransaction->metadata['_split_tender_partner'], 'STRIPE' );
		$this->assertEquals( $splitTender->stripeCharge->metadata['_split_tender_partner'], 'LIGHTRAIL' );

		$this->assertEquals( $splitTender->lightrailTransaction->metadata['_split_tender_partner_transaction_id'], $splitTender->stripeCharge->id );
		$this->assertEquals( $splitTender->stripeCharge->metadata['_split_tender_partner_transaction_id'], $splitTender->lightrailTransaction->metadata['giftbit_initial_transaction_id'] );
	}

}
