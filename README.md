# Eloquent Attribute Value Prediction

*Work in progress!*

Predict attribute values for your Laravel Eloquent models using machine learning!

Aiming for the following simple syntax:

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
