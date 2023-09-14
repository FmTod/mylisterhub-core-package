<?php

namespace MyListerHub\Core\Currency\Exchangers;

use AshAllenDesign\LaravelExchangeRates\Facades\ExchangeRate as ExchangeRateProvider;
use Illuminate\Support\Carbon;
use Money\Currency;
use Money\CurrencyPair;
use Money\Exception\UnresolvableCurrencyPairException;
use Money\Exchange;
use MyListerHub\Core\Models\ExchangeRate;

class SmartRatesExchanger implements Exchange
{
    private ?Carbon $date;

    /**
     * Currency constructor.
     *
     * @return void
     */
    public function __construct(Carbon|string $date = null)
    {
        $this->date = $date ? Carbon::parse($date) : null;
    }

    /**
     * Returns a currency pair for the passed currencies with the rate coming from a third-party source.
     *
     *
     * @throws UnresolvableCurrencyPairException When there is no currency pair (rate) available for the given currencies
     */
    public function quote(Currency $baseCurrency, Currency $counterCurrency): CurrencyPair
    {
        $exchangeRate = $this->getExchangeRate($baseCurrency, $counterCurrency);

        $sourceCurrency = new Currency($baseCurrency->getCode());
        $targetCurrency = new Currency($counterCurrency->getCode());
        $rate = $exchangeRate->from === $counterCurrency->getCode() ? $exchangeRate->inverse : $exchangeRate->rate;

        return new CurrencyPair($sourceCurrency, $targetCurrency, $rate);
    }

    /**
     * Get exchange rate from the database or the API if the rate doesn't exist on the database.
     *
     * @param  string|Currency  $baseCurrency
     * @param  string|Currency  $counterCurrency
     */
    private function getExchangeRate($baseCurrency, $counterCurrency): ExchangeRate
    {
        if ($baseCurrency instanceof Currency) {
            $baseCurrency = $baseCurrency->getCode();
        }
        if ($counterCurrency instanceof Currency) {
            $counterCurrency = $counterCurrency->getCode();
        }

        $exchangeRate = ExchangeRate::where([
            'from' => $baseCurrency,
            'to' => $counterCurrency,
        ])
            ->orWhere([
                'from' => $counterCurrency,
                'to' => $baseCurrency,
            ])
            ->latest('updated_at')
            ->first();

        $date = $this->date ?? today();

        if ($exchangeRate && $exchangeRate->date->startOfDay()->equalTo($date->startOfDay())) {
            return $exchangeRate;
        }

        $rate = ExchangeRateProvider::exchangeRate($baseCurrency, $counterCurrency, $this->date);

        return ExchangeRate::create([
            'from' => $baseCurrency,
            'to' => $counterCurrency,
            'rate' => $rate,
            'date' => $this->date ?? today(),
        ]);
    }
}
