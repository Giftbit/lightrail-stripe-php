<?php

namespace Lightrail;

class Lightrail {
	public static $apiKey;
	public static $clientSecret;

	static $API_BASE= "https://api.lightrail.com/v1/";

	public static function ping () {
		$endpoint = self::$API_BASE. "ping";

	}
}