<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class IsExistsInMarcheScope implements Scope
{

    private $columnName = 'in_marche';
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where($this->columnName, true);
    }

    public function extend(Builder $builder)
    {
        $model = $builder->getModel();

        $builder->macro('withNonExists', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });

        $builder->macro('onlyNonExists', function (Builder $builder) {
            return $builder->withoutGlobalScope($this)
                           ->where($this->columnName, false);
        });
    }

}
