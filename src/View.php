<?php

declare(strict_types=1);

namespace Zing\LaravelEloquentView;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Zing\LaravelEloquentView\Events\Viewed;

/**
 * @property \Illuminate\Database\Eloquent\Model $user
 * @property \Illuminate\Database\Eloquent\Model $viewer
 * @property \Illuminate\Database\Eloquent\Model $viewable
 *
 * @method static \Zing\LaravelEloquentView\View|\Illuminate\Database\Eloquent\Builder withType(string $type)
 * @method static \Zing\LaravelEloquentView\View|\Illuminate\Database\Eloquent\Builder query()
 */
class View extends MorphPivot
{
    public $incrementing = true;

    protected $dispatchesEvents = [
        'created' => Viewed::class,
    ];

    public function getTable()
    {
        return config('eloquent-view.table_names.views') ?: parent::getTable();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function viewable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('eloquent-view.models.user'), config('eloquent-view.column_names.user_foreign_key'));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function viewer(): BelongsTo
    {
        return $this->user();
    }

    public function isViewedBy(Model $user): bool
    {
        return $user->is($this->viewer);
    }

    public function isViewedTo(Model $object): bool
    {
        return $object->is($this->viewable);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithType(Builder $query, string $type): Builder
    {
        return $query->where('viewable_type', app($type)->getMorphClass());
    }
}
