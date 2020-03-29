<?php

namespace DivineOmega\EloquentAttributeValuePrediction\Traits;

use DivineOmega\EloquentAttributeValuePrediction\Helpers\PathHelper;
use Rubix\ML\Classifiers\KNearestNeighbors;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;

trait HasAttributeValuePrediction
{
    public function predict($attribute)
    {
        $otherAttributes = array_keys($this->getAttributes());
        unset($otherAttributes[array_search($this->getKeyName(), $otherAttributes)]);
        unset($otherAttributes[array_search($attribute, $otherAttributes)]);

        $sample = [];
        foreach($otherAttributes as $otherAttribute) {
            $value = $instance->getAttributeValue($otherAttribute);
            if ($value === null) {
                $value = '?';
            }
            if (is_object($value) || is_array($value)) {
                $value = serialize($value);
            }
            $sample[] = $value;
        }

        $modelPath = PathHelper::getModelPath(get_class($this), $attribute);

        /** @var KNearestNeighbors $estimator */
        $estimator = PersistentModel::load(new Filesystem($modelPath));

        return $estimator->predictSample($sample);
    }

    public function getPredictions($attribute): array
    {
        $otherAttributes = array_keys($this->getAttributes());
        unset($otherAttributes[array_search($this->getKeyName(), $otherAttributes)]);
        unset($otherAttributes[array_search($attribute, $otherAttributes)]);

        $sample = [];
        foreach($otherAttributes as $otherAttribute) {
            $value = $instance->getAttributeValue($otherAttribute);
            if ($value === null) {
                $value = '?';
            }
            if (is_object($value) || is_array($value)) {
                $value = serialize($value);
            }
            $sample[] = $value;
        }

        $modelPath = PathHelper::getModelPath(get_class($this), $attribute);

        /** @var KNearestNeighbors $estimator */
        $estimator = PersistentModel::load(new Filesystem($modelPath));

        return $estimator->probaSample($sample);
    }
}