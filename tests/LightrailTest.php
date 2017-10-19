<?php

namespace Lightrail;

use PHPUnit\Framework\TestCase;
require '../lib/Lightrail.php';


class LightrailTest extends TestCase {

	public function testPing()
	{
		Lightrail::ping();
	}
}
