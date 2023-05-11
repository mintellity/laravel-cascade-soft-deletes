<?php

namespace Mintellity\LaravelCascadeSoftDeletes\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Mintellity\LaravelCascadeSoftDeletes\LaravelCascadeSoftDeletes;

trait CascadeRestores
{
    use CascadeSoftDeletes;

    protected int $DELETE_CHUNK_SIZE = 50;

    protected $deletedAt;

    /**
     * Register event listener for delete and restore events on the model.
     */
    protected static function bootCascadeRestores(): void
    {
        static::restoring(function (self $model) {
            $model->deletedAt = $model->deleted_at;
        });

        static::restored(function (self $model) {
            $model->performCascadeRestore();
            unset($model->deletedAt);
            Log::debug('Restored model: '.get_class($model).' with id: '.$model->getKey());

        });
    }

    /**
     * Get the cascadeDeletes property or an empty collection.
     */
    protected function cascadeRestores(): Collection
    {
        if (property_exists($this, 'cascadeRestores')) {
            return collect($this->cascadeRestores);
        } elseif (property_exists($this, 'cascadeDeletes')) {
            return collect($this->cascadeDeletes);
        } else {
            return collect();
        }
    }

    /**
     * Restore the related models
     */
    protected function performCascadeRestore(): void
    {
        $relations = LaravelCascadeSoftDeletes::getCascadingRelations($this, $this->cascadeRestores());

        $relations->each(function (string $relation) {
            $relatedClass = $this->{$relation}()->getRelated();
            $relatedDeletedAtColumn = defined($relatedClass.'::DELETED_AT') ? $relatedClass::DELETED_AT : 'deleted_at';

            // We need to query each model to also dispatch restoring events for them
            $this->{$relation}()->onlyTrashed()->where($relatedDeletedAtColumn, '>=', $this->deletedAt)->chunk($this->DELETE_CHUNK_SIZE, function (Collection $models) {
                $models->each(function (Model $model) {
                    $model->restore();
                });
            });
        });
    }
}
