<?php

namespace Lightrail;

class LightrailAPICall {

	private static function getCurlObject( $endpoint ) {
		$curl = curl_init( $endpoint );
		curl_setopt( $curl, CURLOPT_HTTPHEADER, array(
			'Authorization: Bearer ' . Lightrail::$apiKey,
			'Content-Type: application/json'
		) );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );

		return $curl;
	}

	private static function handleError( $httpCode, $response ) {
		var_dump( $response );
		if ( isset( $response ) ) {
			$message = json_decode( $response,true );
			$message = $message['message'];
		}
		if (!isset($message)) {
			$message = $httpCode.'';
		}

		switch ( $httpCode ) {
			case 400: {
				throw new BadParameterException($message);
			}
			case 401:
			case 403: {
				throw new AuthorizationException($message);
			}
			case 404: {
				throw new ObjectNotFoundException($message);
			}
			default:
				throw new LightrailException( $message );
		}
	}

	public static function get( $endpoint ) {
		$curl     = self::getCurlObject( $endpoint );
		$response = curl_exec( $curl );
		$httpCode = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
		curl_close( $curl );
		if ( $httpCode > 204 ) {
			self::handleError( $httpCode, $response );
		}

		return $response;
	}

	public static function post( $endpoint, $body ) {
		$curl = self::getCurlObject( $endpoint );
		curl_setopt( $curl, CURLOPT_POST, true );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, json_encode($body));
		$response = curl_exec( $curl );
		$httpCode = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
		curl_close( $curl );
		if ( $httpCode > 204 ) {
			self::handleError( $httpCode, $response );
		}
		return $response;
	}

	public static function ping() {
		$endpoint = Lightrail::$API_BASE . "ping";
		$response = LightrailAPICall::get( $endpoint );

		return json_decode( $response, true );
	}
}