<?php

namespace DivineOmega\EloquentAttributeValuePrediction\Interfaces;

interface AttributeValuePredictionModelInterface
{
    public function getPredictionAttributes(): array;
    public function getEstimators(): array;
}