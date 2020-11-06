# Eloquent Attribute Value Prediction

*Work in progress*

Predict attribute values for your Laravel Eloquent models using machine learning!

You can use a very simple syntax, to predict both categorical and continuous (numeric) attributes.
Take a look at the example below.

```php
$animal = new \App\Models\Animal();
$animal->size = 'small';
$animal->has_wings = false;
$animal->domesticated = true;

$animal->name = $animal->predict('name');

// 'cat'

$predictions = $animal->getPredictions('name');

// [
//   'cat' => 43,
//   'dog' => 40,
//   'bird' => 10,
//   'elephant => 9,
// ]



$house = new \App\Models\House();
$house->num_bedrooms = 3;
$house->num_bathrooms = 1;

$house->value = $house->predict('value');

// 180000
```

## Installation

To install just run the following Composer command.

```bash
coomposer require divineomega/eloquent-attribute-value-prediction
```

After installation, you need to set up your model for attribute prediction.

## Setup

Let's say you have an `IrisFlowers` table that contains data about each of 
three species of the Iris flower. In this example, we want to be able to 
predict the flower's species given the sepal length and width, and 
petal length and width.

First, we need to set up out `IrisFlower` model for attribut prediction.

This is done by adding the `HasPredictableAttributes` interface and 
`PredictsAttributes` trait to our model as shown below.

```php
<?php

namespace App\Models;

use DivineOmega\EloquentAttributeValuePrediction\Interfaces\HasPredictableAttributes;
use DivineOmega\EloquentAttributeValuePrediction\Traits\PredictsAttributes;
use Illuminate\Database\Eloquent\Model;

class IrisFlower extends Model implements HasPredictableAttributes
{
    use PredictsAttributes;

}
``` 

We then need to tell our model which attributes we wish to predict. We do this
by adding the `registerPredictableAttributes()` function. 

In this example, we want to use predict the `species` attribute based on the 
`sepal_length`, `sepal_width`, `petal_length`, and `petal_width` attributes.
This can be done by returning an array in the following format.

```php
public function registerPredictableAttributes(): array
    {
        return [
            'species' => [
                'sepal_length',
                'sepal_width',
                'petal_length',
                'petal_width',
            ],
        ];
    }
```

TODO: Mention requirement to cast
TODO: Mention training artisan command
