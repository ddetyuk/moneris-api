<?php

namespace CraigPaul\Moneris\Validation;

class PreauthValidator extends ValidatorAbstract
{
    protected array $mustBeSet = [
        'data_key',
        'order_id',
        'amount',
    ];

    protected array $mustBeSetWithAvs = [
        'avs_street_number',
        'avs_street_name',
        'avs_zipcode',
    ];

    protected array $mustBeSetWithCvd = [
        'cvd'
    ];

    protected array $mustBeSetWithCof = [
        'payment_indicator',
        'payment_information',
    ];

    protected function validate(): void
    {
        foreach ($this->mustBeSet as $key) {
            $this->mustBeSet($key);
        }

        if ($this->gateway->avs) {
            foreach ($this->mustBeSetWithAvs as $key) {
                $this->mustBeSet($key);
            }
        }

        if ($this->gateway->cvd) {
            foreach ($this->mustBeSetWithCvd as $key) {
                $this->mustBeSet($key);
            }
        }

        if ($this->gateway->cof) {
            foreach ($this->mustBeSetWithCof as $key) {
                $this->mustBeSet($key);
            }
        }
    }
}
