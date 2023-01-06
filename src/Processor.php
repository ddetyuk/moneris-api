<?php

namespace CraigPaul\Moneris;

use CraigPaul\Moneris\Values\Environment;
use GuzzleHttp\Client;
use SimpleXMLElement;

class Processor
{
    protected Client $client;

    /**
     * API configuration.
     */
    protected array $config = [
        'protocol' => 'https',
        'host' => 'esqa.moneris.com',
        'port' => '443',
        'url' => '/gateway2/servlet/MpgRequest',
        'api_version' => 'PHP - 2.5.6',
        'timeout' => 60,
    ];

    /**
     * Global error response to maintain consistency.
     */
    protected string $error = "<?xml version=\"1.0\"?><response><receipt><ReceiptId>Global Error Receipt</ReceiptId><ReferenceNum>null</ReferenceNum><ResponseCode>null</ResponseCode><ISO>null</ISO> <AuthCode>null</AuthCode><TransTime>null</TransTime><TransDate>null</TransDate><TransType>null</TransType><Complete>false</Complete><Message>null</Message><TransAmount>null</TransAmount><CardType>null</CardType><TransID>null</TransID><TimedOut>null</TimedOut></receipt></response>";

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Retrieve the API configuration.
     */
    public function config(Environment|null $environment = null): array
    {
        /**
         * @codeCoverageIgnore
         */
        if ($environment && $environment->isLive()) {
            $this->config['host'] = 'www3.moneris.com';
        }

        return $this->config;
    }

    /**
     * Determine if the request is valid. If so, process the transaction via
     * the Moneris API.
     */
    public function process(Transaction $transaction): Response
    {
        if ($transaction->invalid()) {
            $response = new Response($transaction);
            $response->status = Response::INVALID_TRANSACTION_DATA;
            $response->successful = false;
            $response->errors = $transaction->errors;

            return $response;
        }

        $response = $this->submit($transaction);

        return $transaction->validate($response);
    }

    /**
     * Parse the global error response stub.
     */
    protected function error(): SimpleXMLElement
    {
        return simplexml_load_string($this->error);
    }

    /**
     * Set up and send the request to the Moneris API.
     *
     * @param array $config
     * @param string $url
     * @param string $xml
     *
     * @return string
     */
    protected function send(array $config, $url = '', $xml = ''): string
    {
        $response = $this->client->post($url, [
            'body' => $xml,
            'headers' => [
                'User-Agent' => $config['api_version']
            ],
            'timeout' => $config['timeout']
        ]);

        return $response->getBody()->getContents();
    }

    /**
     * Submit the transaction to the Moneris API.
     *
     * @param \CraigPaul\Moneris\Transaction $transaction
     *
     * @return \SimpleXMLElement
     */
    protected function submit(Transaction $transaction)
    {
        $config = $this->config($transaction->gateway->environment);

        $url = $config['protocol'] . '://' . $config['host'] . ':' . $config['port'] . $config['url'];

        $xml = str_replace(' </', '</', $transaction->toXml());

        $response = $this->send($config, $url, $xml);

        if (!$response) {
            return $this->error();
        }

        $response = @simplexml_load_string($response);

        if ($response === false) {
            return $this->error();
        }

        return $response;
    }
}
