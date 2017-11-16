<?php

namespace Lightrail;


class LightrailClientTokenFactory
{

    public static function generate($shopperId, $validityInSeconds)
    {

        if (!isset(Lightrail::$apiKey))
            throw new BadParameterException("Lightrail.apiKey is not set.");
        if (!isset(Lightrail::$sharedSecret))
            throw new BadParameterException('Lightrail.sharedSecret is not set.');

        $payload = explode('.', Lightrail::$apiKey);
        $payload = json_decode(base64_decode($payload[1]), true);
        $uid = $payload['g']['gui'];

        $gClaim = array(
            'gui' => $uid,
            'shi' => $shopperId
        );

        $iat = time();

        $token = array(
            'iat' => $iat,
            'g' => $gClaim
        );

        if (isset($validityInSeconds)) {
            $exp = $iat + $validityInSeconds;
            $token['exp'] = $exp;
        }

        $jwt = \Firebase\JWT\JWT::encode($token, Lightrail::$sharedSecret, 'HS256');
        return $jwt;
    }

}