<?php

use CraigPaul\Moneris\Interfaces\GatewayInterface;
use CraigPaul\Moneris\Interfaces\MonerisInterface;
use CraigPaul\Moneris\Moneris;
use CraigPaul\Moneris\Values\Environment;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected Environment $environment;
    protected string $id;
    protected string $token;
    protected string $amex;
    protected string $mastercard;
    protected string $visa;

    public function setUp (): void
    {
        parent::setUp();

        $this->amex = '373599005095005';
        $this->mastercard = '5454545454545454';
        $this->visa = '4242424242424242';

        $this->id = 'store2';
        $this->token = 'yesguy';
        $this->environment = Environment::testing();
    }

    protected function moneris (array $params): MonerisInterface
    {
        return new Moneris(
            $this->id,
            $this->token,
            $this->environment,
            $params,
        );
    }

    protected function gateway (array $params): GatewayInterface
    {
        return Moneris::create(
            $this->id,
            $this->token,
            $this->environment,
            $params
        );
    }
}
