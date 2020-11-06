<?php

namespace DivineOmega\EloquentAttributeValuePrediction\Tests\Unit\TestClasses\Eloquent;

use DivineOmega\EloquentAttributeValuePrediction\Interfaces\AttributeValuePredictionModelInterface;
use DivineOmega\EloquentAttributeValuePrediction\Traits\HasAttributeValuePrediction;
use DivineOmega\uxdm\Objects\Destinations\AssociativeArrayDestination;
use DivineOmega\uxdm\Objects\Migrator;
use DivineOmega\uxdm\Objects\Sources\CSVSource;
use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

class IrisFlower extends Model implements AttributeValuePredictionModelInterface
{
    use HasAttributeValuePrediction;
    use Sushi;

    protected $casts = [
        'sepal_length' => 'float',
        'sepal_width' => 'float',
        'petal_length' => 'float',
        'petal_width' => 'float',
        'species' => 'string',
    ];

    public function getPredictionAttributes(): array
    {
        return [
            'species' => [
                'sepal_length',
                'sepal_width',
                'petal_length',
                'petal_width',
            ],
            'petal_width' => [
                'sepal_length',
                'sepal_width',
                'petal_length',
                'species',
            ]
        ];
    }

    public function getEstimators(): array
    {
        return [];
    }

    public function getRows()
    {
        $rows = [];

        (new Migrator())
            ->setSource(new CsvSource(__DIR__ . '/../../data/iris.csv'))
            ->setDestination(new AssociativeArrayDestination($rows))
            ->migrate();

        return $rows;
    }
}
