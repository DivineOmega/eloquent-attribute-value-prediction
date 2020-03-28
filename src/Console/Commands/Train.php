<?php

namespace DivineOmega\EloquentAttributeValuePrediction\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Rubix\ML\Classifiers\KNearestNeighbors;
use Rubix\ML\Classifiers\MultilayerPerceptron;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\Pipeline;
use Rubix\ML\Transformers\OneHotEncoder;
use Rubix\ML\Transformers\ZScaleStandardizer;

class Train extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eavp:train {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Train a model';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $modelsPath = storage_path('eavp/models');

        if (!is_dir($modelsPath)) {
            mkdir($modelsPath, 0777, true);
        }

        $modelClass = $this->argument('model');

        /** @var Model $model */
        $model = new $modelClass;

        if (!$model instanceof Model) {
            $this->error('The provided class is not an Eloquent model.');
            die;
        }

        // Get all model attributes
        $attributes = array_keys($model->first()->getAttributes());

        // Remove the primary key
        unset($attributes[array_search($model->getKeyName(), $attributes)]);

        foreach($attributes as $classAttribute) {
            $attributesToTrainFrom = $attributes;
            unset($attributesToTrainFrom[array_search($classAttribute, $attributes)]);

            $this->line('Training classification of '.$classAttribute.' attribute from '.implode(', ', $attributesToTrainFrom).' attributes...');

            $modelFile = $modelsPath.sha1(serialize([$modelClass, $classAttribute, $attributesToTrainFrom])).'.model';

            /** @var MultilayerPerceptron $estimator */
            $estimator = $this->getEstimator($modelFile);

            $model->query()->chunk(100, function ($instances) use ($attributesToTrainFrom, $classAttribute, $estimator) {
                $samples = [];
                $classes = [];
                foreach ($instances as $instance) {
                    $sample = [];
                    foreach($attributesToTrainFrom as $attributeToTrainFrom) {
                        $value = $instance->getAttributeValue($attributeToTrainFrom);
                        if ($value === null) {
                            $value = '?';
                        }
                        if (is_object($value) || is_array($value)) {
                            $value = serialize($value);
                        }
                        $sample[] = $value;
                    }
                    $samples[] = $sample;

                    $classValue = $instance->getAttributeValue($classAttribute);
                    if ($classValue === null) {
                        $classValue = '?';
                    }
                    if (is_object($classValue) || is_array($classValue)) {
                        $classValue = serialize($classValue);
                    }
                    $classes[] = $classValue;
                }

                $dataset = new Labeled($samples, $classes);

                $estimator->partial($dataset);
            });

            $estimator->save();
        }


    }

    private function getEstimator($modelFile)
    {
        return new PersistentModel(
            new Pipeline(
                [
                    new OneHotEncoder(),
                    new ZScaleStandardizer(),
                ],
                new KNearestNeighbors()
            ),
            new Filesystem($modelFile)
        );
    }
}