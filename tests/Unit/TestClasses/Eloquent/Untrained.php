<?php

namespace DivineOmega\EloquentAttributeValuePrediction\Tests\Unit\TestClasses\Eloquent;

use DivineOmega\EloquentAttributeValuePrediction\Interfaces\HasPredictableAttributes;
use DivineOmega\EloquentAttributeValuePrediction\Traits\PredictsAttributes;
use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

class Untrained extends Model implements HasPredictableAttributes
{
    use PredictsAttributes;
    use Sushi;

    protected $casts = [
        'abr' => 'string'
    ];

    protected $rows = [
        [
            'abbr' => 'NY',
            'name' => 'New York',
        ],
        [
            'abbr' => 'CA',
            'name' => 'California',
        ],
    ];

    public function registerPredictableAttributes(): array
    {
        return [
            'abr' => [
                'name'
                ]
            ];
    }

}