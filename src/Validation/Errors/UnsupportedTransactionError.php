<?php

namespace CraigPaul\Moneris\Validation\Errors;

class UnsupportedTransactionError implements ErrorInterface
{
    public function code (): int
    {
        return 3;
    }

    public function message (): string
    {
        return 'Unsupported transaction type.';
    }
}
