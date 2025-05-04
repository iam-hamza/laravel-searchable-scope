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
        
        // Use model's searchable property if no columns provided
        if (empty($columns) && property_exists($this, 'searchable')) {
            $columns = $this->searchable['columns'] ?? Config::get('searchable-scope.default_columns', []);
            $relations = $this->searchable['relations'] ?? [];
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
