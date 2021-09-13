<?php

namespace CraigPaul\Moneris\Tests\Support\Stubs;

use CraigPaul\Moneris\Validation\Errors\ErrorInterface;

class ErrorStub implements ErrorInterface
{
    public function code (): int
    {
        return 4;
    }

    public function message (): string
    {
        return 'an error message?';
    }
}
