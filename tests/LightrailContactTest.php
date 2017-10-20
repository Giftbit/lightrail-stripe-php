<?php

namespace Lightrail;
require_once '../test-config.php';
require_once '../init.php';

use PHPUnit\Framework\TestCase;

class LightrailContactTest extends TestCase {

	public function testRetrieve () {
		Lightrail::$apiKey= TestConfig::$apiKey;
		$contactById = LightrailContact::retrieveByContactId(TestConfig::$contactId);
		$contactByShopperId = LightrailContact::retrieveByShopperId(TestConfig::$shopperId);
		$this->assertEquals(TestConfig::$contactId, $contactByShopperId->contactId);
		$this->assertEquals(TestConfig::$shopperId, $contactById->userSuppliedId);

		$cardFromContactId = $contactById->retrieveContactCardForCurrency('USD');
		$cardFromShopperId = $contactByShopperId->retrieveContactCardForCurrency('USD');

		$this->assertEquals($cardFromContactId->cardId, $cardFromShopperId->cardId);
	}

}
