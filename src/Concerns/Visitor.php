<?php

declare(strict_types=1);

namespace LaravelInteraction\Visit\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection|\LaravelInteraction\Visit\Visit[] $visitVisitors
 * @property-read int|null $visit_visitors_count
 */
trait Visitor
{
    public function visit(Model $object): void
    {
        $this->visitedItems($object::class)
            ->attach($object->getKey());
    }

    public function hasVisited(Model $object): bool
    {
        return ($this->relationLoaded('visitVisitors') ? $this->visitVisitors : $this->visitVisitors())
            ->where('visitable_id', $object->getKey())
            ->where('visitable_type', $object->getMorphClass())
            ->count() > 0;
    }

    public function hasNotVisited(Model $object): bool
    {
        return ! $this->hasVisited($object);
    }

    public function visitVisitors(): HasMany
    {
        return $this->hasMany(config('visit.models.pivot'), config('visit.column_names.user_foreign_key'));
    }

    protected function visitedItems(string $class): MorphToMany
    {
        return $this->morphedByMany(
            $class,
            'visitable',
            config('visit.models.pivot'),
            config('visit.column_names.user_foreign_key'),
            'visitable_id'
        )
            ->withTimestamps();
    }
}
