<?php

declare(strict_types=1);

namespace Zing\LaravelEloquentView\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\Request;
use LaravelInteraction\Support\Interaction;
use function is_a;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection|\Zing\LaravelEloquentView\View[] $views
 * @property-read \Illuminate\Database\Eloquent\Collection|\Zing\LaravelEloquentView\Concerns\Viewer[] $viewers
 * @property-read int|null $views_count
 * @property-read int|null $viewers_count
 *
 * @method static static|\Illuminate\Database\Eloquent\Builder whereViewedBy(\Illuminate\Database\Eloquent\Model $user)
 * @method static static|\Illuminate\Database\Eloquent\Builder whereNotViewedBy(\Illuminate\Database\Eloquent\Model $user)
 */
trait Viewable
{
    /**
     * @param \Illuminate\Database\Eloquent\Model $user
     *
     * @return bool
     */
    public function isViewedBy(Model $user): bool
    {
        if (! is_a($user, config('eloquent-view.models.user'))) {
            return false;
        }

        if ($this->relationLoaded('viewers')) {
            return $this->viewers->contains($user);
        }

        return ($this->relationLoaded('views') ? $this->views : $this->views())
            ->where(config('eloquent-view.column_names.user_foreign_key'), $user->getKey())->count() > 0;
    }

    public function isNotViewedBy(Model $user): bool
    {
        return ! $this->isViewedBy($user);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function views(): MorphMany
    {
        return $this->morphMany(config('eloquent-view.models.view'), 'viewable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function viewers(): BelongsToMany
    {
        return $this->morphToMany(
            config('eloquent-view.models.user'),
            'viewable',
            config('eloquent-view.models.view'),
            null,
            config('eloquent-view.column_names.user_foreign_key')
        )->withTimestamps();
    }

    public function viewsCount(): int
    {
        if ($this->views_count !== null) {
            return (int) $this->views_count;
        }

        $this->loadCount('views');

        return (int) $this->views_count;
    }

    public function viewsCountForHumans($precision = 1, $mode = PHP_ROUND_HALF_UP, $divisors = null): string
    {
        return Interaction::numberForHumans($this->viewsCount(), $precision, $mode, $divisors ?? config('eloquent-view.divisors'));
    }

    public function loadViewersCount()
    {
        $view = app(config('eloquent-view.models.view'));
        $column = $view->qualifyColumn(config('eloquent-view.column_names.user_foreign_key'));
        if (method_exists($this, 'loadAggregate')) {
            $this->loadAggregate('views as viewers_count', "COUNT(DISTINCT('{$column}'))");
        } else {
            $this->viewers_count = $this->views()->selectRaw("COUNT(DISTINCT('{$column}')) as viewers_count")->value('viewers_count');
        }

        return $this;
    }

    public function viewersCount(): int
    {
        if ($this->viewers_count !== null) {
            return (int) $this->viewers_count;
        }

        $this->loadViewersCount();

        return (int) $this->viewers_count;
    }

    public function viewersCountForHumans($precision = 1, $mode = PHP_ROUND_HALF_UP, $divisors = null): string
    {
        return Interaction::numberForHumans($this->viewersCount(), $precision, $mode, $divisors ?? config('eloquent-view.divisors'));
    }

    public function scopeWhereViewedBy(Builder $query, Model $user): Builder
    {
        return $query->whereHas(
            'viewers',
            function (Builder $query) use ($user) {
                return $query->whereKey($user->getKey());
            }
        );
    }

    public function scopeWhereNotViewedBy(Builder $query, Model $user): Builder
    {
        return $query->whereDoesntHave(
            'viewers',
            function (Builder $query) use ($user) {
                return $query->whereKey($user->getKey());
            }
        );
    }

    public function record(Request $request): void
    {
        $view = $this->views()->make();
        $view->{config('eloquent-view.column_names.user_foreign_key')} = optional($request->user())->getKey();
        $view->save();
    }
}
