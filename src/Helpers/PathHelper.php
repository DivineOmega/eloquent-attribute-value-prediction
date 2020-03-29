<?php

namespace DivineOmega\EloquentAttributeValuePrediction\Helpers;

abstract class PathHelper
{
    public static function getModelPath($modelClass, $classAttribute)
    {
        $modelDirectory = storage_path('eavp/models/');

        if (!is_dir($modelDirectory)) {
            mkdir($modelDirectory, 0777, true);
        }

        return $modelDirectory.sha1(serialize([$modelClass, $classAttribute])).'.model';
    }
}