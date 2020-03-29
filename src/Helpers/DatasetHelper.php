<?php

namespace DivineOmega\EloquentAttributeValuePrediction\Helpers;

use Rubix\ML\Datasets\Unlabeled;

abstract class DatasetHelper
{
    public static function buildUnlabeledDataset($model, string $attributeToPredict): Unlabeled
    {
        $otherAttributes = $model->getPredictionAttributes();
        unset($otherAttributes[array_search($attributeToPredict, $otherAttributes)]);

        $sample = [];
        foreach($otherAttributes as $otherAttribute) {
            $value = $model->getAttributeValue($otherAttribute);
            if ($value === null) {
                $value = '?';
            }
            if (is_object($value) || is_array($value)) {
                $value = serialize($value);
            }
            $sample[] = $value;
        }

        $dataset = new Unlabeled([$sample]);
    }
}