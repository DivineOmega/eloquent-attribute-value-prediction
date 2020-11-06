<?php

namespace DivineOmega\EloquentAttributeValuePrediction\Traits;

use DivineOmega\EloquentAttributeValuePrediction\Helpers\DatasetHelper;
use DivineOmega\EloquentAttributeValuePrediction\Helpers\PathHelper;
use Exception;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;

trait PredictsAttributes
{
    public function predict(string $attribute)
    {
        $dataset = DatasetHelper::buildUnlabeledDataset($this, $attribute);

        $modelPath = PathHelper::getModelPath(get_class($this), $attribute);

        $estimator = PersistentModel::load(new Filesystem($modelPath));

        $prediction = $estimator->predict($dataset)[0];

        return $prediction;
    }

    public function getPredictions(string $attribute): array
    {
        $dataset = DatasetHelper::buildUnlabeledDataset($this, $attribute);

        $modelPath = PathHelper::getModelPath(get_class($this), $attribute);

        $estimator = PersistentModel::load(new Filesystem($modelPath));

        return $estimator->proba($dataset)[0];
    }

    public function isAttributeContinuous(string $attribute)
    {
        if (!$this->hasCast($attribute)) {
            throw new Exception('The attribute `'.$attribute.'` is missing from the model\'s `$casts` array.');
        }

        $castType = $this->getCastType($attribute);

        return in_array($castType, ['int', 'integer', 'real', 'float', 'double', 'decimal']);
    }

    public function registerEstimators(): array
    {
        return [];
    }
}