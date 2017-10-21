<?php

namespace Lightrail;

require_once '../test-config.php';
require_once '../init.php';
require_once '../vendor/autoload.php';


use PHPUnit\Framework\TestCase;

class StripeLightrailSplitTenderChargeTest extends TestCase {

	public function testSplitTender() {
		Lightrail::$apiKey = TestConfig::$apiKey;
		\Stripe\Stripe::setApiKey( TestConfig::$stripeTestApiKey );


		$splitTender = StripeLightrailSplitTenderCharge::create( array(
			"amount"    => 100,
			"currency"  => "USD",
			"source"    => TestConfig::$stripeDemoToken,
			"shopperId" => TestConfig::$shopperId
		), 99, 1 );
		$this->assertNotNull($splitTender->lightrailTransaction);
		$this->assertNotNull($splitTender->stripeCharge);
		$this->assertEquals(99, $splitTender->getStripeShare());
		$this->assertEquals(1, $splitTender->getLightrailShare());
	}

}
