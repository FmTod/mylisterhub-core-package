<?php

namespace MyListerHub\Core\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class ExchangeRate extends Model
{
    use CentralConnection;

    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
    ];

    protected function inverse(): Attribute
    {
        return new Attribute(
            get: function ($value, $attributes) {
                if (isset($attributes['rate'])) {
                    return bcdiv('1', $this->rate, 6);
                }

                return null;
            },
        );
    }
}
