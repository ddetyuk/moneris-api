<?php

namespace CraigPaul\Moneris\Validation\Errors;

interface ErrorInterface
{
    public function code (): int;

    public function message (): string;
}
