<?php

namespace Lightrail;

class LightrailTransaction extends LightrailObject {
	private static $CREATE_ENDPOINT = "cards/%s/transactions";
	private static $DRYRUN_ENDPOINT = "cards/%s/transactions/dryRun";

	public static function simulate( $params ) {
		return self::create( $params, true );
	}

	public static function create( $params, $simulate = false ) {
		if ( $simulate ) {
			$endpoint = Lightrail::$API_BASE . self::$DRYRUN_ENDPOINT;
		} else {
			$endpoint = Lightrail::$API_BASE . self::$CREATE_ENDPOINT;
		}

		$params = self::handleContact( $params );
		$params = self::addDefaultUserSuppliedIdIfNotProvided( $params );
		$params = self::addDefaultNSFIfNotProvided( $params );

		if ( ! isset( $params['cardId'] ) ) {
			throw new BadParameterException( 'Must provide one of \'cardId\', \'contact\', or \'shopperId\'' );
		}
		$cardId = $params['cardId'];
		unset( $params['cardId'] );

		$endpoint = sprintf( $endpoint, $cardId );
		var_dump($params);
		$response = json_decode( LightrailAPICall::post( $endpoint, $params ), true );
		return new LightrailTransaction( $response, 'transaction' );
	}

	private static function handleContact( $params ) {
		$new_params = $params;

		if ( ! isset( $new_params['currency'] ) ) {
			throw new BadParameterException( 'Must provide \'currency\'' );
		}
		$currency   = $new_params['currency'];

		if ( isset( $new_params['shopperId'] ) ) {
			$shopperId = $new_params['shopperId'];
			unset( $new_params['shopperId'] );
			$card                 = LightrailContact::retrieveByShopperId( $shopperId )->retrieveContactCardForCurrency( $currency );
			$new_params['cardId'] = $card->cardId;
		} else if ( isset( $new_params['contact'] ) ) {
			$contactId = $new_params['contact'];
			unset( $new_params['contact'] );
			$card                 = LightrailContact::retrieveByContactId( $contactId )->retrieveContactCardForCurrency( $currency );
			$new_params['cardId'] = $card->cardId;
		}

		return $new_params;
	}

	private static function addDefaultUserSuppliedIdIfNotProvided( $params ) {
		$new_params = $params;
		if ( ! isset( $new_params['userSuppliedId'] ) ) {
			$new_params['userSuppliedId'] = uniqid();
		}

		return $new_params;
	}

	private static function addDefaultNSFIfNotProvided( $params ) {
		$new_params = $params;
		if ( ! isset( $new_params['nsf'] ) ) {
			$new_params['nsf'] = false;
		}

		return $new_params;
	}
}