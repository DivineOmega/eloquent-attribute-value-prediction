<?php

namespace DivineOmega\EloquentAttributeValuePrediction\Exceptions;

use Exception;
use Facade\IgnitionContracts\Solution;
use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;

class ModelFileNotFound extends Exception implements ProvidesSolution
{
    public function getSolution(): Solution
    {

        return BaseSolution::create('Data Model File for '.$this->getMessage().' Not Found')
            ->setSolutionDescription('Generate your data model by running `php artisan eavp:train '.addslashes($this->getMessage()).'`')
            ->setDocumentationLinks([
                'Training Documentation' => 'https://github.com/DivineOmega/eloquent-attribute-value-prediction#training',
            ]);
    }
}