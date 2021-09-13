<?php

namespace CraigPaul\Moneris\Tests\Feature;

use CraigPaul\Moneris\Interfaces\GatewayInterface;
use CraigPaul\Moneris\Tests\TestCase;
use GuzzleHttp\Client;
use CraigPaul\Moneris\Values\Crypt;
use CraigPaul\Moneris\Receipt;
use CraigPaul\Moneris\Response;
use CraigPaul\Moneris\Processor;
use CraigPaul\Moneris\Transaction;

class ResponseTest extends TestCase
{
    protected GatewayInterface $gateway;
    protected array $params;
    protected Processor $processor;
    protected Response $response;
    protected Transaction $transaction;

    public function setUp (): void
    {
        parent::setUp();

        $this->gateway = $this->gateway();

        $this->params = [
            'type' => 'purchase',
            'crypt_type' => Crypt::SSL_ENABLED_MERCHANT,
            'order_id' => uniqid('1234-56789', true),
            'amount' => '1.00',
            'credit_card' => $this->visa,
            'expdate' => '2012',
        ];

        $this->transaction = new Transaction($this->gateway, $this->params);
        $this->processor = new Processor(new Client());
    }

    /** @test */
    public function it_can_instantiate_via_the_constructor()
    {
        $response = new Response($this->transaction);

        $this->assertEquals(Response::class, get_class($response));
        $this->assertObjectHasAttribute('status', $response);
        $this->assertObjectHasAttribute('successful', $response);
        $this->assertObjectHasAttribute('transaction', $response);
    }

    /** @test */
    public function it_can_instantiate_via_a_static_create_method()
    {
        $response = Response::create($this->transaction);

        $this->assertEquals(Response::class, get_class($response));
        $this->assertObjectHasAttribute('status', $response);
        $this->assertObjectHasAttribute('successful', $response);
        $this->assertObjectHasAttribute('transaction', $response);
    }

    /** @test */
    public function it_can_validate_an_api_response_from_a_proper_transaction()
    {
        $response = $this->processor->process($this->transaction);

        $response = $response->validate();

        $this->assertTrue($response->successful);
    }

    /** @test */
    public function it_can_receive_a_receipt_from_a_properly_processed_transaction()
    {
        $response = $this->processor->process($this->transaction);

        /** @var \CraigPaul\Moneris\Response $response */
        $response = $response->validate();
        $receipt = $response->receipt();

        $this->assertNotNull($receipt);
        $this->assertEquals(Receipt::class, get_class($receipt));
        $this->assertEquals($this->params['order_id'], $receipt->read('id'));
        $this->assertObjectHasAttribute('data', $receipt);
    }

    /** @test */
    public function it_processes_expdate_error_edge_cases_from_message()
    {
        $response = $this->processTransaction([
            'expdate' => 'foo'
        ]);

        $this->assertFalse($response->successful);
        $this->assertEquals(Response::INVALID_EXPIRY_DATE, $response->status);
    }

    /** @test */
    public function it_processes_credit_card_error_edge_cases_from_message()
    {
        $response = $this->processTransaction([
            'credit_card' => '1234'
        ]);

        $this->assertFalse($response->successful);
        $this->assertEquals(Response::INVALID_CARD, $response->status);
    }

    protected function processTransaction ($params = []): Response
    {
        $this->params = array_merge($this->params, $params);
        $this->transaction = new Transaction($this->gateway, $this->params);

        $response = $this->processor->process($this->transaction);

        return $response->validate();
    }
}
