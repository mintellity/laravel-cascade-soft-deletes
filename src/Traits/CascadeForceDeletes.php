<?php

namespace Mintellity\LaravelCascadeSoftDeletes\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Mintellity\LaravelCascadeSoftDeletes\LaravelCascadeSoftDeletes;

trait CascadeForceDeletes
{
    use SoftDeletes;

    protected int $DELETE_CHUNK_SIZE = 50;

    /**
     * Register event listener for delete and restore events on the model.
     */
    protected static function bootCascadeForceDeletes(): void
    {
        static::forceDeleting(function (self $model) {
            $model->performCascadeForceDelete();
        });
    }

    /**
     * Get the cascadeDeletes property or an empty collection.
     */
    protected function cascadeForceDeletes(): Collection
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
    protected function performCascadeForceDelete(): void
    {
        $relations = LaravelCascadeSoftDeletes::getCascadingRelations($this, $this->cascadeForceDeletes());

        $relations->each(function (string $relation) {
            // We need to query each model to also dispatch trashed events for them
            $this->{$relation}()->chunk($this->DELETE_CHUNK_SIZE, function (Collection $models) {
                $models->each(function (Model $model) {
                    $model->forceDelete();
                });
            });
        });
    }
}
