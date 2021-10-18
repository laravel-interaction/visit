<?php

declare(strict_types=1);

namespace Zing\LaravelEloquentView\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
 * @method static static|\Illuminate\Database\Eloquent\Builder withViewersCount($constraints = null)
 */
trait Viewable
{
    public function isViewedBy(Model $user): bool
    {
        if (! is_a($user, config('eloquent-view.models.user'))) {
            return false;
        }

        $viewersThisRelationLoaded = $this->relationLoaded('viewers');

        if ($viewersThisRelationLoaded) {
            return $this->viewers->contains($user);
        }

        return ($this->relationLoaded('views') ? $this->views : $this->views())
            ->where(config('eloquent-view.column_names.user_foreign_key'), $user->getKey())
            ->count() > 0;
    }

    public function isNotViewedBy(Model $user): bool
    {
        return ! $this->isViewedBy($user);
    }

    public function views(): MorphMany
    {
        return $this->morphMany(config('eloquent-view.models.view'), 'viewable');
    }

    public function viewers(): MorphToMany
    {
        return tap(
            $this->morphToMany(
                config('eloquent-view.models.user'),
                'viewable',
                config('eloquent-view.models.view'),
                null,
                config('eloquent-view.column_names.user_foreign_key')
            ),
            static function (MorphToMany $relation): void {
                $relation->distinct($relation->getRelated()->qualifyColumn($relation->getRelatedKeyName()));
            }
        );
    }

    public function viewsCount(): int
    {
        if ($this->views_count !== null) {
            return (int) $this->views_count;
        }

        $this->loadCount('views');

        return (int) $this->views_count;
    }

    public function viewsCountForHumans(int $precision = 1, int $mode = PHP_ROUND_HALF_UP, $divisors = null): string
    {
        return Interaction::numberForHumans(
            $this->viewsCount(),
            $precision,
            $mode,
            $divisors ?? config('eloquent-view.divisors')
        );
    }

    public function loadViewersCount($constraints = null)
    {
        $this->loadCount(
            [
                'viewers' => function ($query) use ($constraints) {
                    return $this->selectDistinctViewerCount($query, $constraints);
                },
            ]
        );

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

    public function viewersCountForHumans(int $precision = 1, int $mode = PHP_ROUND_HALF_UP, $divisors = null): string
    {
        return Interaction::numberForHumans(
            $this->viewersCount(),
            $precision,
            $mode,
            $divisors ?? config('eloquent-view.divisors')
        );
    }

    public function scopeWhereViewedBy(Builder $query, Model $user): Builder
    {
        return $query->whereHas(
            'viewers',
            function (Builder $query) use ($user): Builder {
                return $query->whereKey($user->getKey());
            }
        );
    }

    public function scopeWhereNotViewedBy(Builder $query, Model $user): Builder
    {
        return $query->whereDoesntHave(
            'viewers',
            function (Builder $query) use ($user): Builder {
                return $query->whereKey($user->getKey());
            }
        );
    }

    public function record(Request $request): void
    {
        $view = $this->views()
            ->make();
        $view->{config('eloquent-view.column_names.user_foreign_key')} = optional($request->user())
            ->getKey();
        $view->save();
    }

    public function scopeWithViewersCount(Builder $query, $constraints = null): Builder
    {
        return $query->withCount(
            [
                'viewers' => function ($query) use ($constraints) {
                    return $this->selectDistinctViewerCount($query, $constraints);
                },
            ]
        );
    }

    protected function selectDistinctViewerCount(Builder $query, $constraints = null): Builder
    {
        if ($constraints !== null) {
            $query = $constraints($query);
        }

        $column = $query->getModel()
            ->getQualifiedKeyName();

        return $query->select(DB::raw(sprintf('COUNT(DISTINCT(%s))', $column)));
    }
}
