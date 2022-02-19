<?php

declare(strict_types=1);

namespace LaravelInteraction\Visit;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LaravelInteraction\Visit\Events\Visited;

/**
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Database\Eloquent\Model $user
 * @property \Illuminate\Database\Eloquent\Model $visitor
 * @property \Illuminate\Database\Eloquent\Model $visitable
 *
 * @method static \LaravelInteraction\Visit\Visit|\Illuminate\Database\Eloquent\Builder withType(string $type)
 * @method static \LaravelInteraction\Visit\Visit|\Illuminate\Database\Eloquent\Builder query()
 */
class Visit extends MorphPivot
{
    /**
     * @var bool
     */
    public $incrementing = true;

    /**
     * @var array<string, class-string<\LaravelInteraction\Visit\Events\Visited>>
     */
    protected $dispatchesEvents = [
        'created' => Visited::class,
    ];

    public function getTable(): string
    {
        return config('visit.table_names.visits') ?: parent::getTable();
    }

    public function visitable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('visit.models.user'), config('visit.column_names.user_foreign_key'));
    }

    public function visitor(): BelongsTo
    {
        return $this->user();
    }

    public function isVisitedBy(Model $user): bool
    {
        return $user->is($this->visitor);
    }

    public function isVisitedTo(Model $object): bool
    {
        return $object->is($this->visitable);
    }

    public function scopeWithType(Builder $query, string $type): Builder
    {
        return $query->where('visitable_type', app($type)->getMorphClass());
    }
}
