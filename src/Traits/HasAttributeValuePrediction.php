<?php

namespace DivineOmega\EloquentAttributeValuePrediction\Traits;

use DivineOmega\EloquentAttributeValuePrediction\Helpers\DatasetHelper;
use DivineOmega\EloquentAttributeValuePrediction\Helpers\PathHelper;
use Exception;
use Rubix\ML\Classifiers\KNearestNeighbors;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;

trait HasAttributeValuePrediction
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

    public function getAttributeCast(string $attribute)
    {
        if (!array_key_exists($attribute, $this->casts)) {
            throw new Exception('The attribute `'.$attribute.'` is missing from the model\'s `$casts` array.');
        }

        return $this->casts[$attribute];
    }
    
    public function isAttributeContinuous(string $attribute)
    {
        $attributeCast = $this->getAttributeCast($attribute);

        return (in_array($attributeCast, [
                'integer',
                'real',
                'float',
                'double',
            ]) || stripos($attributeCast, 'decimal') !== false);
    }
}