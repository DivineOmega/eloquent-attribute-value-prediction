<?php

namespace DivineOmega\EloquentAttributeValuePrediction\Helpers;

use Carbon\Carbon;
use Rubix\ML\Datasets\Unlabeled;
use Exception;

abstract class DatasetHelper
{
    public static function buildUnlabeledDataset($model, string $attributeToPredict): Unlabeled
    {
        $otherAttributes = $model->getPredictionAttributes();
        unset($otherAttributes[array_search($attributeToPredict, $otherAttributes)]);

        $sample = self::buildSample($model, $otherAttributes);

        return new Unlabeled([$sample]);
    }

    public static function buildSample($model, $attributes)
    {
        $sample = [];

        foreach($attributes as $attribute) {
            $value = $model->getAttributeValue($attribute);
            if ($value === null) {
                $value = '?';
            }
            if ($value instanceof Carbon) {
                $value->getTimestamp();
            }
            if (!is_string($value) && !is_numeric($value)) {
                throw new Exception('The attribute `'.$attribute.'` has a data type that is not supported.');
            }
            $sample[] = $value;
        }

        return $sample;
    }
}