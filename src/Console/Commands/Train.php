<?php

namespace DivineOmega\EloquentAttributeValuePrediction\Console\Commands;

use DivineOmega\EloquentAttributeValuePrediction\Helpers\DatasetHelper;
use DivineOmega\EloquentAttributeValuePrediction\Helpers\PathHelper;
use DivineOmega\EloquentAttributeValuePrediction\Interfaces\AttributeValuePredictionModelInterface;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Rubix\ML\Classifiers\KNearestNeighbors;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\Pipeline;
use Rubix\ML\Transformers\OneHotEncoder;
use Rubix\ML\Transformers\ZScaleStandardizer;
use Rubix\ML\Transformers\MissingDataImputer;
use Rubix\ML\Other\Loggers\Screen;
use Rubix\ML\Transformers\NumericStringConverter;


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
        $modelClass = $this->argument('model');

        /** @var Model $model */
        $model = new $modelClass;

        if (!$model instanceof Model) {
            $this->error('The provided class is not an Eloquent model.');
            die;
        }

        if (!$model instanceof AttributeValuePredictionModelInterface) {
            $this->error('The provided class does not implement the AttributeValuePredictionModelInterface.');
            die;
        }

        // Get all model attributes
        $attributes = $model->getPredictionAttributes();

        foreach($attributes as $classAttribute) {
            $attributesToTrainFrom = $attributes;
            unset($attributesToTrainFrom[array_search($classAttribute, $attributes)]);

            $this->line('Training classification of '.$classAttribute.' attribute from '.implode(', ', $attributesToTrainFrom).' attribute(s)...');

            $modelPath = PathHelper::getModelPath($modelClass, $classAttribute);

            $estimator = $this->getEstimator($modelPath);

            $samples = [];
            $classes = [];

            $model->query()->chunk(100, function ($instances) use ($attributesToTrainFrom, $classAttribute, &$samples, &$classes) {
                foreach ($instances as $instance) {
                    $samples[] = DatasetHelper::buildSample($instance, $attributesToTrainFrom);

                    $classValue = $instance->getAttributeValue($classAttribute);
                    if ($classValue === null) {
                        $classValue = '?';
                    }
                    if (is_object($classValue) || is_array($classValue)) {
                        $classValue = serialize($classValue);
                    }
                    $classes[] = $classValue;
                }
            });

            $dataset = new Labeled($samples, $classes);

            $estimator->train($dataset);

            $estimator->save();
        }


    }

    private function getEstimator($modelPath)
    {
        $estimator = new PersistentModel(
            new Pipeline(
                [
                    new MissingDataImputer(),
                    new NumericStringConverter(),
                    new OneHotEncoder(),
                    new ZScaleStandardizer(),
                ],
                new KNearestNeighbors()
            ),
            new Filesystem($modelPath)
        );

        $estimator->setLogger(new Screen());

        return $estimator;
    }
}