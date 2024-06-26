<?php

namespace Mintellity\LaravelCascadeSoftDeletes\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
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
            $model->performCascadeSoftDelete();
        });
    }

    /**
     * Get the cascadeDeletes property or an empty collection.
     */
    protected function cascadeSoftDeletes(): Collection
    {
        if (property_exists($this, 'cascadeDeletes')) {
            return collect($this->cascadeDeletes);
        } else {
            return collect();
        }
    }

    /**
     * Cascade delete the related models
     */
    protected function performCascadeSoftDelete(): void
    {
        $relations = LaravelCascadeSoftDeletes::getCascadingRelations($this, $this->cascadeSoftDeletes());

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
