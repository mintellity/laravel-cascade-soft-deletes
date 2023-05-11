<?php

namespace Mintellity\LaravelCascadeSoftDeletes\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Mintellity\LaravelCascadeSoftDeletes\LaravelCascadeSoftDeletes;

trait CascadeSoftDeletes
{
    use SoftDeletes;

    protected int $DELETE_CHUNK_SIZE = 50;

    /**
     * Register event listener for delete and restore events on the model.
     */
    protected static function bootCascadeSoftDeletes(): void
    {
        static::softDeleted(function (self $model) {
            $model->performCascadeDelete();
            Log::debug('Deleted model: '.get_class($model).' with id: '.$model->getKey());
        });
    }

    /**
     * Get the cascadeDeletes property or an empty collection.
     */
    protected function cascadeDeletes(): Collection
    {
        if (property_exists($this, 'cascadeDeletes')) {
            return collect($this->cascadeDeletes);
        } else {
            return collect();
        }
    }

    /**
     * If it's a soft delete, we need to cascade delete the related models
     */
    protected function performCascadeDelete(): void
    {
        $relations = LaravelCascadeSoftDeletes::getCascadingRelations($this, $this->cascadeDeletes());

        $relations->each(function (string $relation) {
            // We need to query each model to also dispatch trashed events for them
            $this->{$relation}()->chunk($this->DELETE_CHUNK_SIZE, function (Collection $models) {
                $models->each(function (Model $model) {
                    $model->delete();
                });
            });
        });
    }
}
