<?php /** @noinspection PhpPureAttributeCanBeAddedInspection */

namespace CraigPaul\Moneris;

use CraigPaul\Moneris\Interfaces\GatewayInterface;
use CraigPaul\Moneris\Interfaces\MonerisInterface;
use CraigPaul\Moneris\Values\Environment;

/**
 * CraigPaul\Moneris\Moneris
 * @property-read string $id
 * @property-read string $token
 * @property-read string $environment
 * @property-read string $params
 */
class Moneris implements MonerisInterface
{
    use Gettable;

    public function __construct(
        protected string $id,
        protected string $token,
        protected Environment $environment,
        protected array $params = []
    ) {}

    /**
     * Create a new Moneris instance and return the Gateway.
     */
    public static function create (
        string $id,
        string $token,
        Environment $environment,
        array $params = []
    ): GatewayInterface
    {
        $moneris = new static($id, $token, $environment, $params);

        return $moneris->connect();
    }

    /**
     * Create and return a new Gateway instance.
     */
    public function connect (): GatewayInterface
    {
        $gateway = new Gateway($this->id, $this->token, $this->environment);

        if (isset($this->params['avs'])) {
            $gateway->avs = (bool) $this->params['avs'];
        }

        if (isset($this->params['cvd'])) {
            $gateway->cvd = (bool) $this->params['cvd'];
        }

        if (isset($this->params['cof'])) {
            $gateway->cof = (bool) $this->params['cof'];
        }

        return $gateway;
    }
}
