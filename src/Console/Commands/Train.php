<?php

namespace DivineOmega\EloquentAttributeValuePrediction\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Rubix\ML\Classifiers\MultilayerPerceptron;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\NeuralNet\Layers\Dense;
use Rubix\ML\NeuralNet\Layers\PReLU;
use Rubix\ML\Other\Tokenizers\NGram;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\Pipeline;
use Rubix\ML\Transformers\TextNormalizer;
use Rubix\ML\Transformers\TfIdfTransformer;
use Rubix\ML\Transformers\WordCountVectorizer;
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

            $modelFile = storage_path(sha1(serialize([$modelClass, $classAttribute])));

            /** @var MultilayerPerceptron $estimator */
            $estimator = $this->getEstimator($modelFile);

            $model->query()->chunk(100, function ($instances) use ($attributesToTrainFrom, $classAttribute, $estimator) {
                $samples = [];
                $classes = [];
                foreach ($instances as $instance) {
                    $sample = [];
                    foreach($attributesToTrainFrom as $attributeToTrainFrom) {
                        $samples[] = $instance->getAttributeValue($attributeToTrainFrom);
                    }
                    $samples[] = $sample;
                    $classes[] = $instance->getAttributeValue($classAttribute);
                }

                $dataset = new Labeled($samples, $classes);

                $estimator->partial($dataset);
            });
        }


    }

    private function getEstimator($modelFile)
    {
        return new PersistentModel(
            new Pipeline(
                [
                    new TextNormalizer(true),
                    new WordCountVectorizer(10000, 3, new NGram(1, 3)),
                    new TfIdfTransformer(),
                    new ZScaleStandardizer()
                ],
                new MultilayerPerceptron([
                    new Dense(100),
                    new PReLU(),
                    new Dense(100),
                    new PReLU(),
                    new Dense(100),
                    new PReLU(),
                    new Dense(50),
                    new PReLU(),
                    new Dense(50),
                    new PReLU(),
                ])
            ),
            new Filesystem($modelFile)
        );
    }
}