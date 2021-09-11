<?php

use CraigPaul\Moneris\Moneris;
use CraigPaul\Moneris\Gateway;

class MonerisTest extends TestCase
{
    /** @test */
    public function instantiation (): void
    {
        $moneris = new Moneris($this->id, $this->token, $this->environment);

        $this->assertEquals(Moneris::class, get_class($moneris));
        $this->assertObjectHasAttribute('id', $moneris);
        $this->assertObjectHasAttribute('token', $moneris);
        $this->assertObjectHasAttribute('environment', $moneris);
    }

    /** @test */
    public function getting_the_gateway_via_static_method (): void
    {
        $gateway = Moneris::create($this->id, $this->token, $this->environment);

        $this->assertEquals(Gateway::class, get_class($gateway));
        $this->assertObjectHasAttribute('id', $gateway);
        $this->assertObjectHasAttribute('token', $gateway);
        $this->assertObjectHasAttribute('environment', $gateway);
    }

    /** @test */
    public function getting_class_properties (): void
    {
        $moneris = $this->moneris();

        $this->assertEquals($this->id, $moneris->id);
        $this->assertEquals($this->token, $moneris->token);
        $this->assertSame($this->environment, $moneris->environment);
    }

    /** @test */
    public function it_fails_to_retrieve_a_non_existent_property_of_the_class()
    {
        $moneris = $this->moneris();

        $this->expectException(InvalidArgumentException::class);

        $moneris->nonExistentProperty;
    }

    /** @test */
    public function getting_the_gateway ()
    {
        $moneris = $this->moneris();

        $gateway = $moneris->connect();

        $this->assertEquals(Gateway::class, get_class($gateway));
        $this->assertObjectHasAttribute('id', $gateway);
        $this->assertObjectHasAttribute('token', $gateway);
        $this->assertObjectHasAttribute('environment', $gateway);
    }
}
