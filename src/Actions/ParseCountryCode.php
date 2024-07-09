<?php

namespace MyListerHub\Core\Actions;

use MyListerHub\Core\Concerns\Actions\AsAction;
use Rinvex\Country\Country;
use Rinvex\Country\CountryLoaderException;

/**
 * @method static string run(string $value)
 */
class ParseCountryCode
{
    use AsAction;

    public function handle(string $value): string
    {
        if (strlen($value) === 2) {
            return country($value)->getIsoAlpha2();
        }

        $countries = countries(hydrate: true);

        /** @var Country $country */
        foreach ($countries as $country) {
            if (strtolower($country->getName()) === strtolower($value)
                || strtolower($country->getOfficialName()) === strtolower($value)
                || strtolower($country->getIsoAlpha3()) === strtolower($value)) {
                return $country->getIsoAlpha2();
            }
        }

        throw CountryLoaderException::invalidCountry();
    }

    public static function try(string $value): ?string
    {
        try {
            return static::run($value);
        } catch (CountryLoaderException) {
            return null;
        }
    }
}
