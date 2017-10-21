<?php

namespace Lightrail;


class LightrailClientTokenFactory {

	public static function generate ($shopperId, $validityInMs) {

		if (!isset(Lightrail::$apiKey))
			throw new BadParameterException("Lightrail.apiKey is not set.");
		if (!isset(Lightrail::$clientSecret ))
			throw new BadParameterException('Lightrail.clientSecret is not set.');

		$payload = explode (  '.', Lightrail::$apiKey);
		$payload = json_decode(base64_decode($payload[1]), true);
		$uid = $payload['g']['gui'];

		$gClaim = array(
			'gui' => $uid
		);

		$iat = time();
		$token = array(
			'shopperId' => $shopperId,
			'iat' => $iat,
			'g' => $gClaim
		);

		if (isset($validityInMs)) {
			$exp = $iat + $validityInMs;
			$token['exp']=$exp;
        }

		$jwt = \Firebase\JWT\JWT::encode($token, Lightrail::$clientSecret, 'HS256');
		return $jwt;
	}
}