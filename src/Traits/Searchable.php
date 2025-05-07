<?php

namespace HamzaEjaz\SearchableScope\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

trait Searchable
{
    /**
     * Search scope for models
     *
     * @param Builder $query
     * @param string|null $searchTerm
     * @param array $columns
     * @param array $relations
     * @return Builder
     */
    public function scopeSearch(Builder $query, ?string $searchTerm, array $columns = [], array $relations = []): Builder
    {
        if (!$searchTerm || strlen($searchTerm) < Config::get('searchable-scope.min_term_length', 2)) {
            return $query;
        }

        $operator = Config::get('searchable-scope.default_operator', 'LIKE');
        $caseSensitive = Config::get('searchable-scope.case_sensitive', false);
        
        /**
         * Priority order:
         * 1. Directly passed columns from controller
         * 2. Model's searchable property
         * 3. Config default columns
         */
        if (empty($columns)) {
            if (property_exists($this, 'searchable')) {
                $columns = $this->searchable['columns'] ?? [];
                $relations = $this->searchable['relations'] ?? [];
            }
            
            // If still empty, use config defaults
            if (empty($columns)) {
                $columns = Config::get('searchable-scope.default_columns', []);
            }
        }

        $query->where(function ($query) use ($columns, $relations, $searchTerm, $operator, $caseSensitive) {
            foreach ($columns as $column) {
                $value = $caseSensitive ? $searchTerm : strtolower($searchTerm);
                if (!$caseSensitive) {
                    $query->orWhereRaw('LOWER(' . $column . ') ' . $operator . ' ?', 
                        [$operator === 'LIKE' ? "%{$value}%" : $value]);
                } else {
                    $query->orWhere($column, $operator, $operator === 'LIKE' ? "%{$value}%" : $value);
                }
            }

            foreach ($relations as $relation => $relationColumns) {
                $query->orWhereHas($relation, function ($query) use ($relationColumns, $searchTerm, $operator, $caseSensitive) {
                    $query->where(function ($query) use ($relationColumns, $searchTerm, $operator, $caseSensitive) {
                        foreach ($relationColumns as $column) {
                            $value = $caseSensitive ? $searchTerm : strtolower($searchTerm);
                            if (!$caseSensitive) {
                                $query->orWhereRaw('LOWER(' . $column . ') ' . $operator . ' ?',
                                    [$operator === 'LIKE' ? "%{$value}%" : $value]);
                            } else {
                                $query->orWhere($column, $operator, $operator === 'LIKE' ? "%{$value}%" : $value);
                            }
                        }
                    });
                });
            }
        });

        return $query;
    }
}
