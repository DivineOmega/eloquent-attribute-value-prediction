<?php

namespace DivineOmega\EloquentAttributeValuePrediction\Console\Commands;

use DivineOmega\EloquentAttributeValuePrediction\Helpers\PathHelper;
use DivineOmega\EloquentAttributeValuePrediction\Interfaces\AttributeValuePredictionModelInterface;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Rubix\ML\Classifiers\KNearestNeighbors;
use Rubix\ML\Classifiers\MultilayerPerceptron;
use Rubix\ML\CrossValidation\Metrics\Accuracy;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\NeuralNet\Layers\Dense;
use Rubix\ML\NeuralNet\Layers\PReLU;
use Rubix\ML\Other\Tokenizers\NGram;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\Pipeline;
use Rubix\ML\Transformers\OneHotEncoder;
use Rubix\ML\Transformers\TextNormalizer;
use Rubix\ML\Transformers\TfIdfTransformer;
use Rubix\ML\Transformers\WordCountVectorizer;
use Rubix\ML\Transformers\ZScaleStandardizer;
use Rubix\ML\Transformers\KNNImputer;
use Rubix\ML\Other\Loggers\Screen;

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

            /** @var KNearestNeighbors $estimator */
            $estimator = $this->getEstimator($modelPath);

            $needsInitialTraining = true;

            $model->query()->chunk(100999, function ($instances) use ($attributesToTrainFrom, $classAttribute, $estimator, $needsInitialTraining) {
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

                if ($needsInitialTraining) {
                    $estimator->train($dataset);
                    $needsInitialTraining = false;
                } else {
                    $estimator->partial($dataset);
                }
            });

            $estimator->save();
        }


    }

    private function getEstimator($modelPath)
    {
        $estimator = new PersistentModel(
            new Pipeline(
                [
                    new OneHotEncoder(),
                    new ZScaleStandardizer(),
                ],
                new KNearestNeighbors()
            ),
            new Filesystem($modelPath)
        );

        $estimator->setLogger(new Screen('example'));

        return $estimator;
    }
}