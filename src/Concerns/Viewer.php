<?php

declare(strict_types=1);

namespace Zing\LaravelEloquentView\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection|\Zing\LaravelEloquentView\View[] $views
 * @property-read int|null $views_count
 */
trait Viewer
{
    /**
     * @param \Illuminate\Database\Eloquent\Model $object
     */
    public function view(Model $object): void
    {
        $this->viewedItems(get_class($object))->attach($object->getKey());
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $object
     *
     * @return bool
     */
    public function hasViewed(Model $object): bool
    {
        return tap($this->relationLoaded('views') ? $this->views : $this->views())
            ->where('viewable_id', $object->getKey())
            ->where('viewable_type', $object->getMorphClass())
            ->count() > 0;
    }

    public function hasNotViewed(Model $object): bool
    {
        return ! $this->hasViewed($object);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function views(): HasMany
    {
        return $this->hasMany(config('eloquent-view.models.view'), config('eloquent-view.column_names.user_foreign_key'), $this->getKeyName());
    }

    /**
     * @param string $class
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    protected function viewedItems(string $class): MorphToMany
    {
        return $this->morphedByMany($class, 'viewable', config('eloquent-view.models.view'), config('eloquent-view.column_names.user_foreign_key'), 'viewable_id')->withTimestamps();
    }
}
