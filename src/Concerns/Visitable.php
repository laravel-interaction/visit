<?php

declare(strict_types=1);

namespace LaravelInteraction\Visit\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use LaravelInteraction\Support\Interaction;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection|\LaravelInteraction\Visit\Visit[] $visitableVisits
 * @property-read \Illuminate\Database\Eloquent\Collection|\LaravelInteraction\Visit\Concerns\Visitor[] $visitors
 * @property-read int|null $visitable_visits_count
 * @property-read int|null $visitors_count
 *
 * @method static static|\Illuminate\Database\Eloquent\Builder whereVisitedBy(\Illuminate\Database\Eloquent\Model $user)
 * @method static static|\Illuminate\Database\Eloquent\Builder whereNotVisitedBy(\Illuminate\Database\Eloquent\Model $user)
 * @method static static|\Illuminate\Database\Eloquent\Builder withVisitorsCount($constraints = null)
 */
trait Visitable
{
    public function isVisitedBy(Model $user): bool
    {
        if (! is_a($user, config('visit.models.user'))) {
            return false;
        }

        $visitorsThisRelationLoaded = $this->relationLoaded('visitors');

        if ($visitorsThisRelationLoaded) {
            return $this->visitors->contains($user);
        }

        return ($this->relationLoaded('visitableVisits') ? $this->visitableVisits : $this->visitableVisits())
            ->where(config('visit.column_names.user_foreign_key'), $user->getKey())
            ->count() > 0;
    }

    public function isNotVisitedBy(Model $user): bool
    {
        return ! $this->isVisitedBy($user);
    }

    public function visitableVisits(): MorphMany
    {
        return $this->morphMany(config('visit.models.pivot'), 'visitable');
    }

    public function visitors(): MorphToMany
    {
        return tap(
            $this->morphToMany(
                config('visit.models.user'),
                'visitable',
                config('visit.models.pivot'),
                null,
                config('visit.column_names.user_foreign_key')
            ),
            static function (MorphToMany $relation): void {
                $relation->distinct($relation->getRelated()->qualifyColumn($relation->getRelatedKeyName()));
            }
        );
    }

    public function visitsCount(): int
    {
        if ($this->visitable_visits_count !== null) {
            return (int) $this->visitable_visits_count;
        }

        $this->loadCount('visitableVisits');

        return (int) $this->visitable_visits_count;
    }

    /**
     * @phpstan-param 1|2|3|4 $mode
     *
     * @param array<int, string>|null $divisors
     */
    public function visitsCountForHumans(int $precision = 1, int $mode = PHP_ROUND_HALF_UP, $divisors = null): string
    {
        return Interaction::numberForHumans(
            $this->visitsCount(),
            $precision,
            $mode,
            $divisors ?? config('visit.divisors')
        );
    }

    /**
     * @param callable|null $constraints
     *
     * @return $this
     */
    public function loadVisitorsCount($constraints = null)
    {
        $this->loadCount([
            'visitors' => fn ($query) => $this->selectDistinctVisitorCount($query, $constraints),
        ]);

        return $this;
    }

    public function visitorsCount(): int
    {
        if ($this->visitors_count !== null) {
            return (int) $this->visitors_count;
        }

        $this->loadVisitorsCount();

        return (int) $this->visitors_count;
    }

    /**
     * @phpstan-param 1|2|3|4 $mode
     *
     * @param array<int, string>|null $divisors
     */
    public function visitorsCountForHumans(int $precision = 1, int $mode = PHP_ROUND_HALF_UP, $divisors = null): string
    {
        return Interaction::numberForHumans(
            $this->visitorsCount(),
            $precision,
            $mode,
            $divisors ?? config('visit.divisors')
        );
    }

    public function scopeWhereVisitedBy(Builder $query, Model $user): Builder
    {
        return $query->whereHas(
            'visitors',
            static fn (Builder $query): Builder => $query->whereKey($user->getKey())
        );
    }

    public function scopeWhereNotVisitedBy(Builder $query, Model $user): Builder
    {
        return $query->whereDoesntHave(
            'visitors',
            static fn (Builder $query): Builder => $query->whereKey($user->getKey())
        );
    }

    public function record(Request $request): void
    {
        $visit = $this->visitableVisits()
            ->make();
        $visit->{config('visit.column_names.user_foreign_key')} = optional($request->user())
            ->getKey();
        $visit->save();
    }

    /**
     * @param callable|null $constraints
     */
    public function scopeWithVisitorsCount(Builder $query, $constraints = null): Builder
    {
        return $query->withCount(
            [
                'visitors' => fn ($query) => $this->selectDistinctVisitorCount($query, $constraints),
            ]
        );
    }

    /**
     * @param callable|null $constraints
     */
    protected function selectDistinctVisitorCount(Builder $query, $constraints = null): Builder
    {
        if ($constraints !== null) {
            $query = $constraints($query);
        }

        $column = $query->getModel()
            ->getQualifiedKeyName();

        return $query->select(DB::raw(sprintf('COUNT(DISTINCT(%s))', $column)));
    }
}
