<?php

declare(strict_types=1);

namespace App\Relations\MorphToMany;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Laratrust requires users' roles() to be a MorphToMany, but the user_roles
 * table has no user_type column (dropped in core_v305), so the morph type
 * constraint is skipped and the relation behaves like a plain BelongsToMany.
 */
class UserRoles extends MorphToMany
{
    protected function addWhereConstraints()
    {
        BelongsToMany::addWhereConstraints();

        return $this;
    }

    public function addEagerConstraints(array $models)
    {
        BelongsToMany::addEagerConstraints($models);
    }

    protected function baseAttachRecord($id, $timed)
    {
        return BelongsToMany::baseAttachRecord($id, $timed);
    }

    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        return BelongsToMany::getRelationExistenceQuery($query, $parentQuery, $columns);
    }

    protected function getCurrentlyAttachedPivotsForIds($ids = null)
    {
        return BelongsToMany::getCurrentlyAttachedPivotsForIds($ids);
    }

    public function newPivotQuery()
    {
        return BelongsToMany::newPivotQuery();
    }

    public function newPivot(array $attributes = [], $exists = false)
    {
        return BelongsToMany::newPivot($attributes, $exists);
    }

    protected function aliasedPivotColumns()
    {
        return BelongsToMany::aliasedPivotColumns();
    }
}
