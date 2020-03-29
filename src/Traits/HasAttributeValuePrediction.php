<?php

namespace DivineOmega\EloquentAttributeValuePrediction\Traits;

use DivineOmega\EloquentAttributeValuePrediction\Helpers\DatasetHelper;
use DivineOmega\EloquentAttributeValuePrediction\Helpers\PathHelper;
use Rubix\ML\Classifiers\KNearestNeighbors;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;

trait HasAttributeValuePrediction
{
    public function predict($attribute)
    {
        $dataset = DatasetHelper::buildUnlabeledDataset($this, $attribute);

        $modelPath = PathHelper::getModelPath(get_class($this), $attribute);

        /** @var KNearestNeighbors $estimator */
        $estimator = PersistentModel::load(new Filesystem($modelPath));

        $prediction = $estimator->predict($dataset)[0];

        $unserializedPrediction = @unserialize($prediction);

        if ($prediction === 'b:0;' || $unserializedPrediction !== false) {
            return $unserializedPrediction;
        }

        return $prediction;
    }

    public function getPredictions($attribute): array
    {
        $dataset = DatasetHelper::buildUnlabeledDataset($this, $attribute);

        $modelPath = PathHelper::getModelPath(get_class($this), $attribute);

        /** @var KNearestNeighbors $estimator */
        $estimator = PersistentModel::load(new Filesystem($modelPath));

        return $estimator->proba($dataset)[0];
    }
}