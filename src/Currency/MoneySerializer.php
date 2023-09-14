<?php

namespace MyListerHub\Core\Currency;

use FmTod\Money\Money;
use FmTod\Money\Serializers\DecimalSerializer;

class MoneySerializer extends DecimalSerializer
{
    public function __invoke(Money $money): array
    {
        return array_merge(parent::__invoke($money), [
            'amount' => $money->getAmount(),
        ]);
    }
}
