<?php

namespace YourName\SearchableScope\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Searchable
{
    public function scopeSearch(Builder $query, ?string $searchTerm, array $columns = [], array $relations = []): Builder
    {
        if (!$searchTerm) {
            return $query;
        }

        $query->where(function ($query) use ($columns, $relations, $searchTerm) {
            foreach ($columns as $column) {
                $query->orWhere($column, 'LIKE', "%{$searchTerm}%");
            }

            foreach ($relations as $relation => $relationColumns) {
                $query->orWhereHas($relation, function ($query) use ($relationColumns, $searchTerm) {
                    foreach ($relationColumns as $column) {
                        $query->where($column, 'LIKE', "%{$searchTerm}%");
                    }
                });
            }
        });

        return $query;
    }
}
