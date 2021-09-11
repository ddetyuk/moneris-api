<?php

namespace CraigPaul\Moneris\Values;

use JetBrains\PhpStorm\Pure;

class Environment
{
    const LIVE = 'live';
    const STAGING = 'staging';
    const TESTING = 'testing';

    protected function __construct (private string $environment) {}

    #[Pure]
    public static function live (): self
    {
        return new self(self::LIVE);
    }

    #[Pure]
    public static function staging (): self
    {
        return new self(self::STAGING);
    }

    #[Pure]
    public static function testing (): self
    {
        return new self(self::TESTING);
    }

    public function value (): string
    {
        return $this->environment;
    }

    #[Pure]
    public function isLive (): bool
    {
        return $this->value() === self::LIVE;
    }
}
