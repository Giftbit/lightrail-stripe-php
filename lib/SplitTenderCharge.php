<?php

namespace LightrailStripe;

class SplitTenderCharge
{

    public $lightrailTransaction = null;
    public $stripeCharge = null;

    public function __construct($stripeCharge, $lightrailTransaction)
    {
        $this->stripeCharge = $stripeCharge;
        $this->lightrailTransaction = $lightrailTransaction;
    }

    public static function create($params, $lightrailShare)
    {
        \LightrailStripe\LightrailStripe::checkSplitTenderParams($params, $lightrailShare);

        $transactionAmount = $params['amount'];
        unset($params['amount']);

        $params = self::addDefaultUserSuppliedIdIfNotProvided($params);
        $userSuppliedId = $params['userSuppliedId'];

        $stripeShare = $transactionAmount - $lightrailShare;

        if ($lightrailShare != 0) {
            $lightrailParams = self::splitTenderToLightrailParams($params, $transactionAmount, $lightrailShare);

            if ($stripeShare == 0) { //everything on lightrail
                $lightrailTransaction = \Lightrail\LightrailTransaction::create($lightrailParams);

                return new SplitTenderCharge(null, $lightrailTransaction);
            } else { //split between card and credit card
                $lightrailParams['pending'] = true;
                $lightrailPendingTransaction = \Lightrail\LightrailTransaction::create($lightrailParams);
                try {
                    $stripeParams = self::splitTenderToStripeParams($params, $transactionAmount, $stripeShare, $lightrailPendingTransaction->transactionId);
                    $charge = \Stripe\Charge::create($stripeParams, array('idempotency_key' => $userSuppliedId));
                } catch (\Exception $exception) {
                    $lightrailPendingTransaction->void();
                    throw $exception;
                }

                $metadataForLightrailCapture = self::appendMetadata($lightrailPendingTransaction->metadata, 'STRIPE', $transactionAmount, $charge->id);
                $lightrailCaptureTransaction = $lightrailPendingTransaction->capture(array('metadata' => $metadataForLightrailCapture));

                return new SplitTenderCharge($charge, $lightrailCaptureTransaction);
            }
        } else { //all on credit card
            $stripeParams = self::removeLightrailParams($params);
            $stripeParams['amount'] = $stripeShare;
            $charge = \Stripe\Charge::create($stripeParams);

            return new SplitTenderCharge($charge, null);
        }
    }

    // Helpers

    public static function addDefaultUserSuppliedIdIfNotProvided($params)
    {
        $new_params = $params;
        if (isset($new_params['idempotency-key'])) {
            $new_params['userSuppliedId'] = $new_params['idempotency-key'];
            unset($new_params['idempotency-key']);
        }

        if (!isset($new_params['userSuppliedId'])) {
            $new_params['userSuppliedId'] = uniqid();
        }

        return $new_params;
    }

    public static function splitTenderToLightrailParams($splitTenderParams, $transactionAmount, $lightrailShare, $stripeTransactionId = null)
    {
        $lightrailParams = self::removeStripeParams($splitTenderParams);
        $lightrailParams['value'] = 0 - $lightrailShare;
        $lightrailParams['metadata'] = isset($lightrailParams['metadata']) ? $lightrailParams['metadata'] : [];
        $lightrailParams['metadata'] = self::appendMetadata($lightrailParams['metadata'], 'STRIPE', $transactionAmount, $stripeTransactionId);

        return $lightrailParams;
    }

    private static function removeStripeParams($params)
    {
        $newParams = $params;
        unset($newParams['source']);
        unset($newParams['customer']);

        return $newParams;
    }

    public static function appendMetadata($originalMetadata, $partner, $transactionAmount, $partnerTransactionId = null)
    {
        $newMetadata = $originalMetadata;

        $newMetadata['_split_tender_total'] = $transactionAmount;
        $newMetadata['_split_tender_partner'] = $partner;
        if ($partnerTransactionId != null) {
            $newMetadata['_split_tender_partner_transaction_id'] = $partnerTransactionId;
        }

        return $newMetadata;
    }

    public static function splitTenderToStripeParams($splitTenderParams, $transactionAmount, $stripeShare, $lightrailTransactionId = null)
    {
        $stripeParams = self::removeLightrailParams($splitTenderParams);
        $stripeParams['amount'] = $stripeShare;
        $stripeMetadata = isset($stripeParams['metadata']) ? $stripeParams['metadata'] : [];
        $stripeParams['metadata'] = self::appendMetadata($stripeMetadata, 'LIGHTRAIL', $transactionAmount, $lightrailTransactionId);

        return $stripeParams;
    }

    private static function removeLightrailParams($params)
    {
        $newParams = $params;
        unset($newParams['cardId']);
        unset($newParams['contact']);
        unset($newParams['shopperId']);
        unset($newParams['userSuppliedId']);

        return $newParams;
    }

    // Instance methods

    public function getStripeShare()
    {
        if (!isset($this->stripeCharge)) {
            return null;
        }

        return $this->stripeCharge->amount;
    }

    public function getLightrailShare()
    {
        if (!isset($this->lightrailTransaction)) {
            return null;
        }

        return 0 - $this->lightrailTransaction->value;
    }

    public function getStripeTxId()
    {
        if (!isset($this->stripeCharge)) {
            return null;
        }

        return $this->stripeCharge->id;
    }

    public function getLightrailTxId()
    {
        if (!isset($this->lightrailTransaction)) {
            return null;
        }

        return 0 - $this->lightrailTransaction->transactionId;
    }
}
