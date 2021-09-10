<?php

namespace CraigPaul\Moneris\Interfaces;

interface GatewayInterface
{
    /**
     * Capture a pre-authorized transaction.
     * @param \CraigPaul\Moneris\Transaction|string $transaction
     * @param string|null $order
     * @param mixed|null $amount
     * @return \CraigPaul\Moneris\Response
     */
    public function capture ($transaction, $order = null, $amount = null);

    /**
     * Create a new Vault instance.
     * @return \CraigPaul\Moneris\Vault
     */
    public function cards ();

    /**
     * Pre-authorize a purchase.
     * @param array $params
     * @return \CraigPaul\Moneris\Response
     */
    public function preauth (array $params = []);

    /**
     * Make a purchase.
     * @param array $params
     * @return \CraigPaul\Moneris\Response
     */
    public function purchase (array $params = []);

    /**
     * Refund a transaction.
     * @param \CraigPaul\Moneris\Transaction|string $transaction
     * @param string|null $order
     * @param mixed|null $amount
     * @return \CraigPaul\Moneris\Response
     */
    public function refund ($transaction, $order = null, $amount = null);

    /**
     * Validate CVD and/or AVS prior to attempting a purchase.
     * @param array $params
     * @return \CraigPaul\Moneris\Response
     */
    public function verify (array $params = []);

    /**
     * Void a transaction.
     * @param \CraigPaul\Moneris\Transaction|string $transaction
     * @param string|null $order
     * @return \CraigPaul\Moneris\Response
     */
    public function void ($transaction, $order = null);
}
