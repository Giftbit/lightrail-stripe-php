<?php

namespace LightrailStripe;

$dotenv = new \Dotenv\Dotenv(__DIR__ . "/..");
$dotenv->load();
require_once __DIR__ . '/../init.php';

use PHPUnit\Framework\TestCase;

class SplitTenderChargeTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        \Lightrail\Lightrail::$apiKey = getEnv("LIGHTRAIL_API_KEY");
        \Stripe\Stripe::setApiKey(getenv("STRIPE_API_KEY"));
    }

    public function getBasicParams()
    {
        return array(
            'amount' => 100,
            'currency' => 'USD',
            'source' => getenv("STRIPE_DEMO_TOKEN"),
            'shopperId' => getenv("SHOPPER_ID"),
        );
    }

    public function testSimulateSplitTender()
    {
        $splitTender = SplitTenderCharge::simulate($this->getBasicParams(), 1);
        $this->assertNotNull($splitTender->lightrailTransaction);
        $this->assertNull($splitTender->stripeCharge);
        $this->assertEquals(1, $splitTender->getLightrailShare());
    }

    public function testSplitTender()
    {
        $splitTender = SplitTenderCharge::create($this->getBasicParams(), 1);
        $this->assertNotNull($splitTender->lightrailTransaction);
        $this->assertNotNull($splitTender->stripeCharge);
        $this->assertEquals(99, $splitTender->getStripeShare());
        $this->assertEquals(1, $splitTender->getLightrailShare());
    }

    public function testSimulateSplitTenderLightrailOnly()
    {
        $splitTender = SplitTenderCharge::simulate($this->getBasicParams(), 100);
        $this->assertNotNull($splitTender->lightrailTransaction);
        $this->assertNull($splitTender->stripeCharge);
        $this->assertEquals(100, $splitTender->getLightrailShare());
    }

    public function testSplitTenderLightrailOnly()
    {
        $splitTender = SplitTenderCharge::create($this->getBasicParams(), 100);
        $this->assertNotNull($splitTender->lightrailTransaction);
        $this->assertNull($splitTender->stripeCharge);
        $this->assertEquals(100, $splitTender->getLightrailShare());
    }

    public function testSimulateSplitTenderStripeOnly()
    {
        $splitTender = SplitTenderCharge::simulate($this->getBasicParams(), 0);
        $this->assertNull($splitTender->lightrailTransaction);
        $this->assertNull($splitTender->stripeCharge);
    }

    public function testSplitTenderStripeOnly()
    {
        $splitTender = SplitTenderCharge::create($this->getBasicParams(), 0);
        $this->assertNull($splitTender->lightrailTransaction);
        $this->assertNotNull($splitTender->stripeCharge);
        $this->assertEquals(100, $splitTender->getStripeShare());
    }

    public function testSimulateSplitTenderAllTheMoney()
    {
        $params = $this->getBasicParams();
        $params['amount'] = 99999999;
        $params['nsf'] = false;
        $splitTender = SplitTenderCharge::simulate($params, 1);
        $this->assertNotNull($splitTender->lightrailTransaction);
        $this->assertNull($splitTender->stripeCharge);
        $this->assertGreaterThan(0, $splitTender->getLightrailShare());
    }

    public function testSplitTenderWithLowercaseCurrency()
    {
        $params = $this->getBasicParams();
        $params['currency'] = 'usd';
        $splitTender = SplitTenderCharge::create($params, 1);

        $this->assertNotNull($splitTender->lightrailTransaction);
        $this->assertNotNull($splitTender->stripeCharge);
        $this->assertEquals(99, $splitTender->getStripeShare());
        $this->assertEquals(1, $splitTender->getLightrailShare());
    }

    public function testSplitTenderWithUserSuppliedId()
    {
        $params = $this->getBasicParams();

        $userSuppliedId = uniqid();
        $params['userSuppliedId'] = $userSuppliedId;

        $splitTender = SplitTenderCharge::create($params, 1);
        $this->assertNotNull($splitTender->lightrailTransaction);
        $this->assertEquals($userSuppliedId . '-CAPTURE', $splitTender->lightrailTransaction->userSuppliedId);
        $this->assertNotNull($splitTender->stripeCharge);
        $this->assertEquals(99, $splitTender->getStripeShare());
        $this->assertEquals(1, $splitTender->getLightrailShare());
    }

    public function testSplitTenderWithIdempotencyKey()
    {
        $params = $this->getBasicParams();

        $userSuppliedId = uniqid();
        $params['idempotency-key'] = $userSuppliedId;

        $splitTender = SplitTenderCharge::create($params, 1);
        $this->assertNotNull($splitTender->lightrailTransaction);
        $this->assertEquals($userSuppliedId . '-CAPTURE', $splitTender->lightrailTransaction->userSuppliedId);
        $this->assertNotNull($splitTender->stripeCharge);
        $this->assertEquals(99, $splitTender->getStripeShare());
        $this->assertEquals(1, $splitTender->getLightrailShare());
    }

    public function testSplitTenderWithMetadata()
    {
        $params = $this->getBasicParams();
        $params['metadata'] = array('test' => 'test');

        $splitTender = SplitTenderCharge::create($params, 1);

        $this->assertEquals($splitTender->lightrailTransaction->metadata['_split_tender_total'], 100);
        $this->assertEquals($splitTender->stripeCharge->metadata['_split_tender_total'], 100);

        $this->assertEquals($splitTender->lightrailTransaction->metadata['_split_tender_partner'], 'STRIPE');
        $this->assertEquals($splitTender->stripeCharge->metadata['_split_tender_partner'], 'LIGHTRAIL');

        $this->assertEquals($splitTender->lightrailTransaction->metadata['_split_tender_partner_transaction_id'], $splitTender->stripeCharge->id);
        $this->assertEquals($splitTender->stripeCharge->metadata['_split_tender_partner_transaction_id'], $splitTender->lightrailTransaction->metadata['giftbit_initial_transaction_id']);
    }

    public function testRethrowStripeErrorAfterLightrailPending()
    {
        \Stripe\Stripe::setApiKey('abc');

        $params = $this->getBasicParams();
        $this->expectException(\Exception::class);
        SplitTenderCharge::create($params, 1);

        \Stripe\Stripe::setApiKey(getenv("STRIPE_API_KEY"));
    }

    public function testRethrowStripeErrorWithoutLightrailPending()
    {
        \Stripe\Stripe::setApiKey('abc');

        $params = $this->getBasicParams();
        $this->expectException(\Exception::class);
        SplitTenderCharge::create($params, 0);

        \Stripe\Stripe::setApiKey(getenv("STRIPE_API_KEY"));
    }

    public function testRethrowLightrailPendingError()
    {
        \Lightrail\Lightrail::$apiKey = 'abc';

        $params = $this->getBasicParams();
        $this->expectException(\Exception::class);
        SplitTenderCharge::create($params, 1);

        \Lightrail\Lightrail::$apiKey = getEnv("LIGHTRAIL_API_KEY");
    }

}
