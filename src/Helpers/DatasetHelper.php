<?php

namespace DivineOmega\EloquentAttributeValuePrediction\Helpers;

use Rubix\ML\Datasets\Unlabeled;

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
            if (is_object($value) || is_array($value)) {
                $value = serialize($value);
            }
            $sample[] = $value;
        }

        return $sample;
    }
}