<?php

namespace Mintellity\LaravelCascadeSoftDeletes\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

trait CascadeSoftDeletes
{
    use SoftDeletes;

    protected int $DELETE_CHUNK_SIZE = 50;

    protected $deletedAt;

    /**
     * Register event listener for delete and restore events on the model.
     */
    protected static function bootCascadeSoftDeletes(): void
    {
        static::softDeleted(function (self $model) {
            $model->performCascadeDelete();
            Log::debug('Deleted model: ' . get_class($model) . ' with id: ' . $model->getKey());
        });

        static::restoring(function (self $model) {
            $model->deletedAt = $model->deleted_at;
        });

        static::restored(function (self $model) {
            $model->performCascadeRestore();
            unset($model->deletedAt);
            Log::debug('Restored model: ' . get_class($model) . ' with id: ' . $model->getKey());

        });
    }

    /**
     * Get the cascadeDeletes property or an empty collection.
     */
    protected function cascadeDeletes(): Collection
    {
        if (property_exists($this, 'cascadeDeletes'))
            return collect($this->cascadeDeletes);
        else
            return collect();
    }

    /**
     * Get the relations that should be cascaded soft deleted.
     * Only relations that implement SoftDeletes trait will be returned.
     */
    protected function getCascadingRelations(): Collection
    {
        return collect($this->cascadeDeletes())
            ->filter(function (string $relation) {
                return
                    method_exists($this, $relation) &&
                    $this->{$relation}() instanceof Relation &&
                    $this->{$relation}()->getRelated() instanceof Model &&
                    in_array(CascadeSoftDeletes::class, class_uses_recursive($this->{$relation}()->getRelated()));
            });
    }

    /**
     * If it's a soft delete, we need to cascade delete the related models
     */
    protected function performCascadeDelete(): void
    {
        $relations = $this->getCascadingRelations();

        $relations->each(function (string $relation) {
            // We need to query each model to also dispatch trashed events for them
            $this->{$relation}()->chunk($this->DELETE_CHUNK_SIZE, function (Collection $models) {
                $models->each(function (Model $model) {
                    $model->delete();
                });
            });
        });
    }

    /**
     * Restore the related models
     */
    protected function performCascadeRestore(): void
    {
        $relations = $this->getCascadingRelations();

        $relations->each(function (string $relation) {
            $relatedClass = $this->{$relation}()->getRelated();
            $relatedDeletedAtColumn = defined($relatedClass . '::DELETED_AT') ? $relatedClass::DELETED_AT : 'deleted_at';

            // We need to query each model to also dispatch restoring events for them
            $this->{$relation}()->onlyTrashed()->where($relatedDeletedAtColumn, '>=', $this->deletedAt)->chunk($this->DELETE_CHUNK_SIZE, function (Collection $models) {
                $models->each(function (Model $model) {
                    $model->restore();
                });
            });
        });
    }
}
