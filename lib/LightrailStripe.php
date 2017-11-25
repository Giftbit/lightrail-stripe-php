<?php

namespace LightrailStripe;

class LightrailStripe
{

    public static function checkStripeApiKey()
    {
        if (!isset(\Stripe\Stripe::$apiKey)) {
            throw new \Lightrail\Exceptions\BadParameterException('Stripe::$apiKey not set.');
        }
    }

    public static function checkSplitTenderParams($params, $lightrailShare)
    {
        //TODO: check shared secret set
        \Lightrail\Lightrail::checkApiKey();
        self::checkStripeApiKey();

        if (!isset($params['currency'])) {
            throw new \Lightrail\Exceptions\BadParameterException('Currency not set for split tender transaction.');
        }
        if (!isset($params['amount'])) {
            throw new \Lightrail\Exceptions\BadParameterException('Amount not set for split tender transaction.');
        }
        if (!isset($lightrailShare)) {
            throw new \Lightrail\Exceptions\BadParameterException('Lightrail share not set for split tender transaction.');
        }
    }
}
