<?php

namespace LightrailStripe;

class LightrailStripe {

	public static function checkStripeApiKey() {
		if ( ! isset( \Stripe\Stripe::$apiKey ) ) {
			throw new BadParameterException( 'Stripe::$apiKey not set.' );
		}
	}

	public static function checkSplitTenderParams( $params, $lightrailShare ) {
		//TODO: check shared secret set
		\Lightrail\Lightrail::checkApiKey();
		self::checkStripeApiKey();

		if ( ! isset( $params['currency'] ) ) {
			throw new BadParameterException( 'Currency not set for split tender transaction.' );
		}
		if ( ! isset( $params['amount'] ) ) {
			throw new BadParameterException( 'Amount not set for split tender transaction.' );
		}
		if ( ! isset( $lightrailShare ) ) {
			throw new BadParameterException( 'Lightrail share not set for split tender transaction.' );
		}
	}
}