<?php

namespace Lightrail;

class Lightrail {
	public static $apiKey;
	public static $clientSecret;

	static $API_BASE = 'https://api.lightrail.com/v1/';

	public static function setClientSecret ($theClientSecret) {
		self::$clientSecret = $theClientSecret;
	}
	public static function setApiKey ($theApiKey) {
		self::$apiKey = $theApiKey;
	}
	public static function checkParams( $params ) {
		if (!isset(self::$apiKey))
			throw new BadParameterException('Lightrail::$apiKey not set.');
	}
}