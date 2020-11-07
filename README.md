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

You also need to add the attributes you are using to the `$casts` array. 
It is important that the machine learning algorithm knows the type of data
stored in each attribute, and that it is consistent.

For our `IrisFlower` example, the following format is appropriate.

```php
protected $casts = [
        'sepal_length' => 'float',
        'sepal_width' => 'float',
        'petal_length' => 'float',
        'petal_width' => 'float',
        'species' => 'string',
    ];
``` 

## Training

Before you can make attribute value predictions, you must train a machine 
learning model on your data. As a general rule, the more data you provide
your model, the better it will perform, and the more accurate it will be.

You can train your model(s) using the `eavp:train` Artisan command, as shown
in the example below.

```bash
php artisan eavp:train \App\Models\IrisFlower
```

One model will be trained for each of the attributes you wish to predict. When
they are trained, they will be saved into the `storage/eavp/model/` directory
for future use.

Be aware that the training process can take some time to complete depending 
on the amount of data you are using, and the complexity of your machine 
learning model. Training progress will be output to the console where possible.

You can re-run this command (manually, or on a schedule) to re-train your 
machine learning model(s). Previously trained models will be replaced 
automatically. 

## Prediction

Once you have set up your Eloquent model, and trained your machine learning 
model(s), you can begin predicting attributes.

For example, to predict the species of an IrisFlower, you can create a new
`IrisFlower` object and populate a few of its known attributes, then call the
`predict` method.

```php
$flower = new \App\Models\IrisFlower();
$flower->sepal_length = 5.1;
$flower->sepal_width = 3.5;
$flower->petal_length = 1.4;
$flower->petal_width = 0.2;

$species = $flower->predict('species');  
```

The `predict` method should be passed the attribute name you wish to predict.
It will then returns the prediction as a string or numeric type. 

In our example, this should be the 'setosa'
species, based on [Iris flower data set](https://en.wikipedia.org/wiki/Iris_flower_data_set).

## Advanced

### Prediction probabilities

TODO

### Changing machine learning model(s)

TODO