<?php

/** @noinspection PhpUnused */

namespace CraigPaul\Moneris;

use CraigPaul\Moneris\Traits\GettableTrait;
use CraigPaul\Moneris\Traits\SettableTrait;
use CraigPaul\Moneris\Validation\Errors\ErrorList;

/**
 * CraigPaul\Moneris\Response
 * @property array $errors
 * @property bool $failedAvs
 * @property bool $failedCvd
 * @property null|int $status
 * @property bool $successful
 * @property \CraigPaul\Moneris\Transaction $transaction
 */
class Response
{
    use GettableTrait;
    use SettableTrait;

    public const ERROR                    = -23;
    public const INVALID_TRANSACTION_DATA = 0;

    public const FAILED_ATTEMPT            = -1;
    public const CREATE_TRANSACTION_RECORD = -2;
    public const GLOBAL_ERROR_RECEIPT      = -3;

    public const SYSTEM_UNAVAILABLE    = -14;
    public const CARD_EXPIRED          = -15;
    public const INVALID_CARD          = -16;
    public const INSUFFICIENT_FUNDS    = -17;
    public const PREAUTH_FULL          = -18;
    public const DUPLICATE_TRANSACTION = -19;
    public const DECLINED              = -20;
    public const NOT_AUTHORIZED        = -21;
    public const INVALID_EXPIRY_DATE   = -22;

    public const CVD               = -4;
    public const CVD_NO_MATCH      = -5;
    public const CVD_NOT_PROCESSED = -6;
    public const CVD_MISSING       = -7;
    public const CVD_NOT_SUPPORTED = -8;

    public const AVS             = -9;
    public const AVS_POSTAL_CODE = -10;
    public const AVS_ADDRESS     = -11;
    public const AVS_NO_MATCH    = -12;
    public const AVS_TIMEOUT     = -13;

    public const POST_FRAUD = -22;

    protected ErrorList $errors;

    /**
     * Determine if we have failed Address Verification Service verification.
     */
    protected bool $failedAvs = false;

    /**
     * Determine if we have failed Card Validation Digits verification.
     */
    protected bool $failedCvd = false;

    /**
     * The status code.s
     */
    protected int|null $status = null;

    /**
     * Determines whether the response was successful.
     */
    protected bool $successful = true;

    /**
     * Create a new Response instance.
     *
     * @param \CraigPaul\Moneris\Transaction $transaction
     */
    public function __construct(protected Transaction $transaction)
    {
        $this->errors = new ErrorList();
    }

    public static function create(Transaction $transaction): self
    {
        return new self($transaction);
    }

    /**
     * Retrieve the transaction's receipt if it is available.
     */
    public function receipt(): Receipt|null
    {
        if (!is_null($response = $this->transaction->response)) {
            return new Receipt($response->receipt);
        }

        return null;
    }

    /**
     * Validate the response.
     */
    public function validate(): self
    {
        $receipt = $this->receipt();
        $gateway = $this->transaction->gateway;

        if ($receipt->read('id') === 'Global Error Receipt') {
            $this->status = self::GLOBAL_ERROR_RECEIPT;
            $this->successful = false;

            return $this;
        }

        $this->successful = $receipt->successful();

        if (!$this->successful) {
            $this->status = $this->convertReceiptCodeToStatus($receipt);
            return $this;
        }

        $code = !is_null($receipt->read('avs_result')) ? $receipt->read('avs_result') : false;

        if ($gateway->avs && $code && $code !== 'null' && !in_array($code, $gateway->avsCodes)) {
            $this->status = match ($code) {
                'B', 'C' => self::AVS_POSTAL_CODE,
                'G', 'I', 'P', 'S', 'U', 'Z' => self::AVS_ADDRESS,
                'N' => self::AVS_NO_MATCH,
                'R' => self::AVS_TIMEOUT,
                default => self::AVS,
            };

            $this->failedAvs = true;

            return $this;
        }

        $code = !is_null($receipt->read('cvd_result')) ? $receipt->read('cvd_result') : null;

        if ($gateway->cvd && !is_null($code) && $code !== 'null' && !in_array($code[1], $gateway->cvdCodes)) {
            $this->status = self::CVD;
            $this->failedCvd = true;

            return $this;
        }

        return $this;
    }

    protected function convertReceiptCodeToStatus(Receipt $receipt): int
    {
        $code = $receipt->read('code');

        if ($code === 'null' && $message_status = $this->convertReceiptMessageToStatus($receipt)) {
            $status = $message_status;
        } else {
            $status = match ($receipt->read('code')) {
                '050', '074', 'null' => self::SYSTEM_UNAVAILABLE,
                '051', '482', '484' => self::CARD_EXPIRED,
                '075' => self::INVALID_CARD,
                '208', '475' => self::INVALID_EXPIRY_DATE,
                '076', '079', '080', '081', '082', '083' => self::INSUFFICIENT_FUNDS,
                '077' => self::PREAUTH_FULL,
                '078' => self::DUPLICATE_TRANSACTION,
                '481', '483' => self::DECLINED,
                '485' => self::NOT_AUTHORIZED,
                '486', '487', '489', '490' => self::CVD,
                default => self::ERROR,
            };
        }

        return $status;
    }

    protected function convertReceiptMessageToStatus(Receipt $receipt): int|null
    {
        $message = (string) $receipt->read('message');
        $status = null;

        if (preg_match('/invalid pan/i', $message)) {
            $status = self::INVALID_CARD;
        } elseif (preg_match('/invalid expiry date/i', $message)) {
            $status = self::INVALID_EXPIRY_DATE;
        }

        return $status;
    }
}
