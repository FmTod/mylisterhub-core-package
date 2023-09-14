<?php

namespace MyListerHub\Core\Currency\Exchangers;

use AshAllenDesign\LaravelExchangeRates\Facades\ExchangeRate as ExchangeRateProvider;
use Illuminate\Support\Carbon;
use Money\Currency;
use Money\CurrencyPair;
use Money\Exception\UnresolvableCurrencyPairException;
use Money\Exchange;

class ExchangeRatesAPIExchanger implements Exchange
{
    private ?Carbon $date;

    /**
     * Currency constructor.
     *
     * @param  Carbon|string|null  $date
     * @return void
     */
    public function __construct($date = null)
    {
        $this->date = $date ? Carbon::parse($date) : null;
    }

    /**
     * Returns a currency pair for the passed currencies with the rate coming from a third-party source.
     *
     * @param  string|Currency  $baseCurrency
     * @param  string|Currency  $counterCurrency
     *
     * @throws UnresolvableCurrencyPairException When there is no currency pair (rate) available for the given currencies
     */
    public function quote($baseCurrency, $counterCurrency): CurrencyPair
    {
        if ($baseCurrency instanceof Currency) {
            $baseCurrency = $baseCurrency->getCode();
        }
        if ($counterCurrency instanceof Currency) {
            $counterCurrency = $counterCurrency->getCode();
        }

        $supportedCurrencies = ExchangeRateProvider::currencies();
        if (in_array($baseCurrency, $supportedCurrencies, true)) {
            throw new UnresolvableCurrencyPairException("Base currency [{$baseCurrency->getCode()}] not supported.");
        }
        if (in_array($counterCurrency, $supportedCurrencies, true)) {
            throw new UnresolvableCurrencyPairException("Counter currency [{$baseCurrency->getCode()}] not supported.");
        }

        $rate = ExchangeRateProvider::exchangeRate($baseCurrency, $counterCurrency, $this->date);

        return new CurrencyPair(new Currency($baseCurrency), new Currency($counterCurrency), $rate);
    }
}
