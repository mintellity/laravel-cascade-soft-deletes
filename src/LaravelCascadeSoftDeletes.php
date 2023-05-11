<?php

namespace Mintellity\LaravelCascadeSoftDeletes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class LaravelCascadeSoftDeletes
{
    /**
     * Get the relations that should be cascaded soft deleted.
     * Only relations that implement SoftDeletes trait will be returned.
     */
    public static function getCascadingRelations(Model $model, Collection $relations): Collection
    {
        return $relations
            ->filter(function (string $relation) use ($model) {
                return
                    method_exists($model, $relation) &&
                    $model->{$relation}() instanceof Relation &&
                    $model->{$relation}()->getRelated() instanceof Model &&
                    in_array(SoftDeletes::class, class_uses_recursive($model->{$relation}()->getRelated()));
            });
    }
}
