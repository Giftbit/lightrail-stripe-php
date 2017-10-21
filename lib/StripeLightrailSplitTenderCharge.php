<?php


namespace Lightrail;


use PHPUnit\Runner\Exception;

class StripeLightrailSplitTenderCharge {

	public $lightrailTransaction = null;
	public $stripeCharge = null;

	public function __construct( $stripeCharge, $lightrailTransaction ) {
		$this->stripeCharge         = $stripeCharge;
		$this->lightrailTransaction = $lightrailTransaction;
	}

	public function getStripeShare() {
		if (! isset($this->stripeCharge))
			return null;
		return $this->stripeCharge->amount;
	}

	public function getLightrailShare() {
		if (! isset($this->lightrailTransaction))
			return null;
		return 0 - $this->lightrailTransaction->value;
	}

	public function getStripeTxId() {
		if (! isset($this->stripeCharge))
			return null;
		return $this->stripeCharge->id;
	}

	public function getLightrailTxId() {
		if (! isset($this->lightrailTransaction))
			return null;
		return 0 - $this->lightrailTransaction->transactionId;
	}

	public static function create( $params, $stripeShare, $lightrailShare ) {
		if ( ! isset( $params['amount'] ) ) {
			throw new BadParameterException( 'Must provide \'amount\'' );
		}
		$transactionAmount = $params['amount'];
		if ( $transactionAmount != $stripeShare + $lightrailShare ) {
			throw new BadParameterException( 'Transaction amount does not match the sum of the given Stripe and Lightrail shares.' );
		}
		unset( $params['amount'] );

		$params = LightrailTransaction::addDefaultUserSuppliedIdIfNotProvided( $params );

		if ( $lightrailShare != 0 ) {
			$lightrailParams             = self::removeStripeParams( $params );
			$lightrailParams['value']    = 0 - $lightrailShare;
			$lightrailParams['metadata'] = self::getMetadata( 'STRIPE', $transactionAmount );
			if ( $stripeShare == 0 ) { //everything on lightrail
				$lightrailTransaction = LightrailTransaction::create( $lightrailParams );

				return new StripeLightrailSplitTenderCharge( null, $lightrailTransaction );
			} else { //split between card and credit card
				$lightrailParams['pending']  = true;
				$lightrailPendingTransaction = LightrailTransaction::create( $lightrailParams );
				try {
					$stripeParams             = self::removeLightrailParams( $params );
					$stripeParams['amount']   = $stripeShare;
					$stripeParams['metadata'] = self::getMetadata( 'LIGHTRAIL', $transactionAmount, $lightrailPendingTransaction->transactionId );
					$charge                   = \Stripe\Charge::create( $stripeParams );

				} catch ( \Exception $exception ) {
					$lightrailPendingTransaction->void();
					throw $exception;
				}
				//$lightrailParams['metadata'] = self::getMetadata( 'STRIPE', $transactionAmount , $charge->getId());
				$lightrailCaptureTransaction = $lightrailPendingTransaction->capture();

				return new StripeLightrailSplitTenderCharge( $charge, $lightrailCaptureTransaction );

			}
		} else { //all on credit card
			$stripeParams           = self::removeLightrailParams( $params );
			$stripeParams['amount'] = $stripeShare;
			$charge                 = \Stripe\Charge::create( $stripeParams );

			return new StripeLightrailSplitTenderCharge( $charge, null );
		}
	}

	private static function removeStripeParams( $params ) {
		$newParams = $params;
		unset( $newParams['source'] );
		unset( $newParams['customer'] );

		return $newParams;
	}

	private static function removeLightrailParams( $params ) {
		$newParams = $params;
		unset( $newParams['cardId'] );
		unset( $newParams['contact'] );
		unset( $newParams['shopperId'] );
		unset( $newParams['userSuppliedId'] );

		return $newParams;
	}

	private static function getMetadata( $partner, $transactionAmount, $stripeTxId = null ) {
		$lightrailSplitTenderMetadata                           = array();
		$lightrailSplitTenderMetadata ['_split-tender-total']   = $transactionAmount;
		$lightrailSplitTenderMetadata ['_split-tender-partner'] = $partner;
		if ( $stripeTxId != null ) {
			$lightrailSplitTenderMetadata ['_split-tender-partner-txn-id'] = $stripeTxId;
		}

		return $lightrailSplitTenderMetadata;
	}

}