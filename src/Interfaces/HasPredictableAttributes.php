<?php

namespace DivineOmega\EloquentAttributeValuePrediction\Interfaces;

interface HasPredictableAttributes
{
    public function registerPredictableAttributes(): array;
    public function registerEstimators(): array;
}