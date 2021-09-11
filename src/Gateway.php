<?php

namespace CraigPaul\Moneris;

use CraigPaul\Moneris\Interfaces\GatewayInterface;
use GuzzleHttp\Client;

/**
 * CraigPaul\Moneris\Gateway
 *
 * @property bool $avs
 * @property-read array $avsCodes
 * @property bool $cvd
 * @property-read array $cvdCodes
 * @property-read string $environment
 * @property-read string $id
 * @property-read string $token
 * @property \CraigPaul\Moneris\Transaction $transaction
 * @property bool $cof
 */
class Gateway implements GatewayInterface
{
    use Gettable, Settable;

    /**
     * Determine if we will use the Address Verification Service.
     */
    protected bool $avs;
    protected array $avsCodes = ['A', 'B', 'D', 'M', 'P', 'W', 'X', 'Y', 'Z'];

    /**
     * Determine if we will use the Card Validation Digits.
     */
    protected bool $cvd;
    protected array $cvdCodes = ['M', 'Y', 'P', 'S', 'U'];

    /**
     * Determine if we will use Credential On File.
     */
    protected bool $cof;

    /**
     * The current transaction.
     */
    protected Transaction|null $transaction;


    public function __construct (
        /**
         * The Moneris Store ID.
         */
        protected string $id,
        /**
         * The Moneris API Token.
         */
        protected string $token,
        /**
         * The environment used for connecting to the Moneris API.
         */
        protected string $environment,
    )
    {
        $this->transaction = null;
        $this->avs = false;
        $this->cvd = false;
        $this->cof = false;
    }

    /**
     * Capture a pre-authorized transaction.
     *
     * @param \CraigPaul\Moneris\Transaction|string $transaction
     * @param string|null $order
     * @param mixed|null $amount
     *
     * @return \CraigPaul\Moneris\Response
     */
    public function capture(
        Transaction|string $transaction,
        string|null $order = null,
        mixed $amount = null
    ): Response
    {
        $transactionNumber = $transaction;

        if ($transaction instanceof Transaction) {
            $order = $transaction->order();
            $amount = is_null($amount)
                ? $transaction->amount()
                : $amount;
            $transactionNumber = $transaction->number();
        }

        $params = [
            'type' => 'completion',
            'crypt_type' => Crypt::SSL_ENABLED_MERCHANT,
            'comp_amount' => $amount,
            'txn_number' => $transactionNumber,
            'order_id' => $order,
        ];

        $transaction = $this->transaction($params);

        return $this->process($transaction);
    }

    /**
     * Create a new Vault instance.
     */
    public function cards (): Vault
    {
        $vault = new Vault($this->id, $this->token, $this->environment);

        $vault->avs = $this->avs;
        $vault->cvd = $this->cvd;
        $vault->cof = $this->cof;

        return $vault;
    }

    /**
     * Pre-authorize a purchase.
     */
    public function preauth (array $params = []): Response
    {
        $params = array_merge($params, [
            'type' => 'preauth',
            'crypt_type' => Crypt::SSL_ENABLED_MERCHANT,
        ]);

        $transaction = $this->transaction($params);

        return $this->process($transaction);
    }

    /**
     * Make a purchase.
     */
    public function purchase (array $params = []): Response
    {
        $params = array_merge($params, [
            'type' => 'purchase',
            'crypt_type' => Crypt::SSL_ENABLED_MERCHANT,
        ]);

        $transaction = $this->transaction($params);

        return $this->process($transaction);
    }

    /**
     * Process a transaction through the Moneris API.
     */
    protected function process (Transaction $transaction): Response
    {
        $processor = new Processor(new Client());

        return $processor->process($transaction);
    }

    /**
     * Refund a transaction.
     */
    public function refund (
        Transaction|string $transaction,
        string|null $order = null,
        mixed $amount = null
    ): Response
    {
        if ($transaction instanceof Transaction) {
            $order = $transaction->order();
            $amount = is_null($amount)
                ? $transaction->amount()
                : $amount;
            $transaction = $transaction->number();
        }

        $params = [
            'type' => 'refund',
            'crypt_type' => Crypt::SSL_ENABLED_MERCHANT,
            'amount' => $amount,
            'txn_number' => $transaction,
            'order_id' => $order,
        ];

        $transaction = $this->transaction($params);

        return $this->process($transaction);
    }

    /**
     * Get or create a new Transaction instance.
     */
    protected function transaction (array $params = null): Transaction
    {
        if (is_null($this->transaction) || !is_null($params)) {
            return $this->transaction = new Transaction($this, $params);
        }

        return $this->transaction;
    }

    /**
     * Validate CVD and/or AVS prior to attempting a purchase.
     */
    public function verify (array $params = []): Response
    {
        $params = array_merge($params, [
            'type' => 'card_verification',
            'crypt_type' => Crypt::SSL_ENABLED_MERCHANT,
        ]);

        $transaction = $this->transaction($params);

        return $this->process($transaction);
    }

    /**
     * Void a transaction.
     */
    public function void(
        Transaction|string $transaction,
        string|null $order = null
    ): Response
    {
        if ($transaction instanceof Transaction) {
            $order = $transaction->order();
            $transaction = $transaction->number();
        }

        $params = [
            'type' => 'purchasecorrection',
            'crypt_type' => Crypt::SSL_ENABLED_MERCHANT,
            'txn_number' => $transaction,
            'order_id' => $order,
        ];

        $transaction = $this->transaction($params);

        return $this->process($transaction);
    }
}
