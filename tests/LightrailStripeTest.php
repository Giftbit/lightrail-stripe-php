<?php

namespace LightrailStripe;

$dotenv = new \Dotenv\Dotenv( __DIR__ . "/.." );
$dotenv->load();
require_once __DIR__ . '/../init.php';

use PHPUnit\Framework\TestCase;

class LightrailStripeTest extends TestCase {
	public function testEnvVarsSet() {
		$this->assertNotEmpty( getEnv( "LIGHTRAIL_API_KEY" ) );
		$this->assertNotEmpty( getEnv( "LIGHTRAIL_SHARED_SECRET" ) );
		$this->assertNotEmpty( getEnv( "CONTACT_ID" ) );
		$this->assertNotEmpty( getEnv( "SHOPPER_ID" ) );
		$this->assertNotEmpty( getEnv( "STRIPE_API_KEY" ) );
		$this->assertNotEmpty( getEnv( "STRIPE_DEMO_TOKEN" ) );
	}

}
