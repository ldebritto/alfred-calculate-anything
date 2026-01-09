<?php

namespace CurrencyConverter\Provider;

use CurrencyConverter\Exception\UnsupportedCurrencyException;
use GuzzleHttp\Client;

/**
 * Get exchange rates from https://www.exchangerate-api.com/
 */
class ExchangeRateApi implements ProviderInterface
{
    /**
     * Base url of ExchangeRate-API
     *
     * @var string
     */
    const EXCHANGERATEAPI_BASEPATH = 'https://v6.exchangerate-api.com/v6';

    /**
     * @var Client
     */
    protected $httpClient;

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * ExchangeRateApi constructor.
     * @param string $apiKey
     * @param Client|null $httpClient
     */
    public function __construct($apiKey, Client $httpClient = null)
    {
        $this->apiKey = $apiKey;
        $this->httpClient = $httpClient ?: new Client();
    }

    /**
     * {@inheritdoc}
     */
    public function getRate($fromCurrency, $toCurrency)
    {
        $path = sprintf(
            '%s/%s/latest/%s',
            self::EXCHANGERATEAPI_BASEPATH,
            $this->apiKey,
            $fromCurrency
        );

        $result = json_decode($this->httpClient->get($path)->getBody(), true);

        // Check for API errors
        if (isset($result['result']) && $result['result'] === 'error') {
            $errorType = $result['error-type'] ?? 'unknown';
            throw new \Exception(sprintf('ExchangeRate-API error: %s', $errorType));
        }

        if (!isset($result['conversion_rates'][$toCurrency])) {
            throw new UnsupportedCurrencyException(sprintf('Undefined rate for "%s" currency.', $toCurrency));
        }

        return $result['conversion_rates'][$toCurrency];
    }
}
